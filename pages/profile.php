<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================================================
   بررسی ورود
========================================================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: /HABIBI/pages/login.php");
    exit;

}


$userId = (int) $_SESSION["user_id"];

$error = "";
$success = "";


/* =========================================================
   دریافت اطلاعات کاربر
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        role_id,
        first_name,
        last_name,
        full_name,
        email,
        phone,
        password,
        status,
        created_at
    FROM users
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $userId
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


/* =========================================================
   اگر کاربر پیدا نشد
========================================================= */

if (!$user) {

    $_SESSION = [];

    session_destroy();

    header("Location: /HABIBI/pages/login.php");
    exit;

}


/* =========================================================
   بررسی وضعیت حساب
========================================================= */

if ($user["status"] !== "active") {

    $_SESSION = [];

    session_destroy();

    header("Location: /HABIBI/pages/login.php");
    exit;

}


/* =========================================================
   ویرایش اطلاعات
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_profile"])
) {

    $firstName = trim($_POST["first_name"] ?? "");
    $lastName  = trim($_POST["last_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");


    /* بررسی نام */

    if ($firstName === "" || $lastName === "") {

        $error = "نام و نام خانوادگی نمی‌توانند خالی باشند.";

    }


    elseif (mb_strlen($firstName) < 2) {

        $error = "نام باید حداقل ۲ کاراکتر باشد.";

    }


    elseif (mb_strlen($lastName) < 2) {

        $error = "نام خانوادگی باید حداقل ۲ کاراکتر باشد.";

    }


    elseif (
        $email !== "" &&
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error = "ایمیل واردشده معتبر نیست.";

    }


    /* =====================================================
       بررسی تکراری نبودن ایمیل
    ====================================================== */

    if ($error === "" && $email !== "") {

        $checkEmail = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
            AND id != :id
            LIMIT 1
        ");

        $checkEmail->execute([
            ":email" => $email,
            ":id" => $userId
        ]);


        if ($checkEmail->fetch()) {

            $error = "این ایمیل قبلاً توسط کاربر دیگری ثبت شده است.";

        }

    }


    /* =====================================================
       ذخیره تغییرات
    ====================================================== */

    if ($error === "") {

        try {

            $fullName =
                trim($firstName . " " . $lastName);


            $update = $pdo->prepare("
                UPDATE users
                SET
                    first_name = :first_name,
                    last_name = :last_name,
                    full_name = :full_name,
                    email = :email
                WHERE id = :id
            ");


            $update->execute([

                ":first_name" => $firstName,

                ":last_name" => $lastName,

                ":full_name" => $fullName,

                ":email" => $email !== ""
                    ? $email
                    : null,

                ":id" => $userId

            ]);


            /* به‌روزرسانی Session */

            $_SESSION["first_name"] = $firstName;

            $_SESSION["last_name"] = $lastName;

            $_SESSION["full_name"] = $fullName;

            $_SESSION["email"] = $email;


            /* به‌روزرسانی اطلاعات فعلی */

            $user["first_name"] = $firstName;

            $user["last_name"] = $lastName;

            $user["full_name"] = $fullName;

            $user["email"] = $email;


            $success =
                "اطلاعات حساب کاربری با موفقیت به‌روزرسانی شد.";


        } catch (PDOException $e) {

            $error =
                "خطایی هنگام ذخیره اطلاعات رخ داد.";

        }

    }

}


/* =========================================================
   تغییر رمز عبور
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["change_password"])
) {

    $currentPassword =
        $_POST["current_password"] ?? "";

    $newPassword =
        $_POST["new_password"] ?? "";

    $confirmPassword =
        $_POST["confirm_new_password"] ?? "";


    /* بررسی رمز فعلی */

    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {

        $error =
            "لطفاً تمام فیلدهای تغییر رمز را تکمیل کنید.";

    }


    elseif (
        !password_verify(
            $currentPassword,
            $user["password"]
        )
    ) {

        $error =
            "رمز عبور فعلی اشتباه است.";

    }


    elseif (strlen($newPassword) < 6) {

        $error =
            "رمز عبور جدید باید حداقل ۶ کاراکتر باشد.";

    }


    elseif ($newPassword !== $confirmPassword) {

        $error =
            "رمز عبور جدید و تکرار آن یکسان نیست.";

    }


    /* =====================================================
       ذخیره رمز جدید
    ====================================================== */

    if ($error === "") {

        try {

            $newHashedPassword =
                password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );


            $updatePassword = $pdo->prepare("
                UPDATE users
                SET password = :password
                WHERE id = :id
            ");


            $updatePassword->execute([

                ":password" => $newHashedPassword,

                ":id" => $userId

            ]);


            $user["password"] =
                $newHashedPassword;


            $success =
                "رمز عبور با موفقیت تغییر کرد.";


        } catch (PDOException $e) {

            $error =
                "خطایی هنگام تغییر رمز عبور رخ داد.";

        }

    }

}


require_once __DIR__ . "/../includes/header.php";

?>


<main>

    <div class="container py-5">


        <h1 class="mb-4">
            حساب کاربری
        </h1>


        <!-- =====================================================
             پیام
        ====================================================== -->

        <?php if ($error !== ""): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle"></i>

                <?php
                echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>

            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="alert alert-success">

                <i class="bi bi-check-circle"></i>

                <?php
                echo htmlspecialchars(
                    $success,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>

            </div>

        <?php endif; ?>


        <div class="row g-4">


            <!-- =================================================
                 اطلاعات حساب
            ================================================== -->

            <div class="col-lg-7">

                <div class="card">

                    <div class="card-body">

                        <h4 class="card-title mb-4">

                            <i class="bi bi-person"></i>

                            اطلاعات شخصی

                        </h4>


                        <form
                            method="POST"
                            action=""
                        >


                            <input
                                type="hidden"
                                name="update_profile"
                                value="1"
                            >


                            <!-- نام -->

                            <div class="mb-3">

                                <label
                                    for="first_name"
                                    class="form-label"
                                >
                                    نام
                                </label>

                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    class="form-control"
                                    value="<?php
                                        echo htmlspecialchars(
                                            $user["first_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>"
                                    required
                                >

                            </div>


                            <!-- نام خانوادگی -->

                            <div class="mb-3">

                                <label
                                    for="last_name"
                                    class="form-label"
                                >
                                    نام خانوادگی
                                </label>

                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    class="form-control"
                                    value="<?php
                                        echo htmlspecialchars(
                                            $user["last_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>"
                                    required
                                >

                            </div>


                            <!-- موبایل -->

                            <div class="mb-3">

                                <label
                                    for="phone"
                                    class="form-label"
                                >
                                    شماره موبایل
                                </label>

                                <input
                                    type="text"
                                    id="phone"
                                    class="form-control"
                                    value="<?php
                                        echo htmlspecialchars(
                                            $user["phone"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>"
                                    readonly
                                >

                                <small class="text-muted">
                                    شماره موبایل قابل تغییر نیست.
                                </small>

                            </div>


                            <!-- ایمیل -->

                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label"
                                >
                                    ایمیل
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    value="<?php
                                        echo htmlspecialchars(
                                            $user["email"] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>"
                                >

                            </div>


                            <button
                                type="submit"
                                class="btn btn-danger"
                            >

                                <i class="bi bi-check-lg"></i>

                                ذخیره تغییرات

                            </button>


                        </form>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 اطلاعات عضویت
            ================================================== -->

            <div class="col-lg-5">

                <div class="card mb-4">

                    <div class="card-body">

                        <h4 class="card-title mb-4">

                            <i class="bi bi-person-badge"></i>

                            حساب من

                        </h4>


                        <p>

                            <strong>
                                نام:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $user["full_name"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                        <p>

                            <strong>
                                شماره موبایل:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $user["phone"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                        <p>

                            <strong>
                                تاریخ عضویت:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $user["created_at"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                    </div>

                </div>



                <!-- =================================================
                     تغییر رمز
                ================================================== -->

                <div class="card">

                    <div class="card-body">

                        <h4 class="card-title mb-4">

                            <i class="bi bi-shield-lock"></i>

                            تغییر رمز عبور

                        </h4>


                        <form
                            method="POST"
                            action=""
                        >


                            <input
                                type="hidden"
                                name="change_password"
                                value="1"
                            >


                            <!-- رمز فعلی -->

                            <div class="mb-3">

                                <label
                                    for="current_password"
                                    class="form-label"
                                >
                                    رمز عبور فعلی
                                </label>

                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- رمز جدید -->

                            <div class="mb-3">

                                <label
                                    for="new_password"
                                    class="form-label"
                                >
                                    رمز عبور جدید
                                </label>

                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- تکرار -->

                            <div class="mb-3">

                                <label
                                    for="confirm_new_password"
                                    class="form-label"
                                >
                                    تکرار رمز جدید
                                </label>

                                <input
                                    type="password"
                                    id="confirm_new_password"
                                    name="confirm_new_password"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <button
                                type="submit"
                                class="btn btn-outline-danger"
                            >

                                <i class="bi bi-key"></i>

                                تغییر رمز

                            </button>


                        </form>

                    </div>

                </div>

            </div>


        </div>

    </div>

</main>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
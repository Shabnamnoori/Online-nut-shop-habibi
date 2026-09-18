<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


$error = "";
$success = "";


/* =========================================================
   ثبت نام
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstName = trim($_POST["first_name"] ?? "");
    $lastName  = trim($_POST["last_name"] ?? "");
    $phone     = trim($_POST["phone"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = $_POST["password"] ?? "";
    $confirm   = $_POST["confirm_password"] ?? "";


    /* نام کامل */

    $fullName = trim($firstName . " " . $lastName);


    /* =====================================================
       بررسی فیلدها
    ====================================================== */

    if (
        $firstName === "" ||
        $lastName === "" ||
        $phone === "" ||
        $password === "" ||
        $confirm === ""
    ) {

        $error = "لطفاً تمام فیلدهای ضروری را وارد کنید.";

    }


    elseif (mb_strlen($firstName) < 2) {

        $error = "نام باید حداقل ۲ کاراکتر باشد.";

    }


    elseif (mb_strlen($lastName) < 2) {

        $error = "نام خانوادگی باید حداقل ۲ کاراکتر باشد.";

    }


    elseif (!preg_match("/^[0-9۰-۹]{10,11}$/u", $phone)) {

        $error = "شماره موبایل واردشده معتبر نیست.";

    }


    elseif (
        $email !== "" &&
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error = "ایمیل واردشده معتبر نیست.";

    }


    elseif (strlen($password) < 6) {

        $error = "رمز عبور باید حداقل ۶ کاراکتر باشد.";

    }


    elseif ($password !== $confirm) {

        $error = "رمز عبور و تکرار آن یکسان نیست.";

    }


    /* =====================================================
       بررسی شماره موبایل
    ====================================================== */

    else {

        $checkPhone = $pdo->prepare("
            SELECT id
            FROM users
            WHERE phone = :phone
            LIMIT 1
        ");

        $checkPhone->execute([
            ":phone" => $phone
        ]);


        if ($checkPhone->fetch()) {

            $error = "این شماره موبایل قبلاً ثبت شده است.";

        }


        /* =================================================
           بررسی ایمیل
        ================================================== */

        elseif ($email !== "") {

            $checkEmail = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            $checkEmail->execute([
                ":email" => $email
            ]);


            if ($checkEmail->fetch()) {

                $error = "این ایمیل قبلاً ثبت شده است.";

            }

        }

    }


    /* =====================================================
       ذخیره کاربر
    ====================================================== */

    if ($error === "") {

        try {

            /* رمزنگاری رمز عبور */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            $sql = "
                INSERT INTO users
                (
                    role_id,
                    first_name,
                    last_name,
                    full_name,
                    email,
                    phone,
                    password,
                    status
                )

                VALUES
                (
                    2,
                    :first_name,
                    :last_name,
                    :full_name,
                    :email,
                    :phone,
                    :password,
                    'active'
                )
            ";


            $stmt = $pdo->prepare($sql);


            $stmt->execute([

                ":first_name" => $firstName,

                ":last_name" => $lastName,

                ":full_name" => $fullName,

                ":email" => $email !== ""
                    ? $email
                    : null,

                ":phone" => $phone,

                ":password" => $hashedPassword

            ]);


            $success =
                "ثبت‌نام با موفقیت انجام شد. اکنون می‌توانید وارد حساب خود شوید.";


            /* پاک کردن مقادیر فرم */

            $_POST = [];


        } catch (PDOException $e) {

            $error =
                "خطا در ثبت اطلاعات: " .
                $e->getMessage();

        }

    }

}


require_once __DIR__ . "/../includes/header.php";

?>


<main>

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-md-7 col-lg-6">


                <h1 class="text-center mb-4">
                    ثبت‌نام
                </h1>


                <!-- =================================================
                     خطا
                ================================================== -->

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


                <!-- =================================================
                     موفقیت
                ================================================== -->

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

                    <div class="text-center">

                        <a
                            href="/HABIBI/pages/login.php"
                            class="btn btn-danger"
                        >
                            ورود به حساب
                        </a>

                    </div>

                <?php else: ?>


                    <!-- =================================================
                         FORM
                    ================================================== -->

                    <form
                        method="POST"
                        action=""
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
                                        $_POST["first_name"] ?? "",
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
                                        $_POST["last_name"] ?? "",
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
                                name="phone"
                                class="form-control"
                                placeholder="09123456789"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["phone"] ?? "",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                ?>"
                                inputmode="numeric"
                                required
                            >

                        </div>


                        <!-- ایمیل -->

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                ایمیل
                                <small>
                                    (اختیاری)
                                </small>
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="example@email.com"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["email"] ?? "",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                ?>"
                            >

                        </div>


                        <!-- رمز -->

                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                رمز عبور
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="حداقل ۶ کاراکتر"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword(
                                        'password',
                                        'passwordIcon'
                                    )"
                                >

                                    <i
                                        class="bi bi-eye"
                                        id="passwordIcon"
                                    ></i>

                                </button>

                            </div>

                        </div>


                        <!-- تکرار رمز -->

                        <div class="mb-3">

                            <label
                                for="confirm_password"
                                class="form-label"
                            >
                                تکرار رمز عبور
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="رمز عبور را دوباره وارد کنید"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword(
                                        'confirm_password',
                                        'confirmPasswordIcon'
                                    )"
                                >

                                    <i
                                        class="bi bi-eye"
                                        id="confirmPasswordIcon"
                                    ></i>

                                </button>

                            </div>

                        </div>


                        <!-- ثبت نام -->

                        <button
                            type="submit"
                            class="btn btn-danger w-100"
                        >

                            <i class="bi bi-person-plus"></i>

                            ثبت‌نام

                        </button>


                    </form>


                    <!-- =================================================
                         ورود
                    ================================================== -->

                    <div class="text-center mt-4">

                        قبلاً حساب ساخته‌اید؟

                        <a href="/HABIBI/pages/login.php">
                            وارد شوید
                        </a>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</main>


<script>

function togglePassword(inputId, iconId) {

    const input =
        document.getElementById(inputId);

    const icon =
        document.getElementById(iconId);


    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("bi-eye");

        icon.classList.add("bi-eye-slash");

    } else {

        input.type = "password";

        icon.classList.remove("bi-eye-slash");

        icon.classList.add("bi-eye");

    }

}

</script>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
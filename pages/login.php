<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================================================
   اگر کاربر قبلاً وارد شده
========================================================= */

if (isset($_SESSION["user_id"])) {

    header("Location: /HABIBI/");
    exit;

}


$error = "";


/* =========================================================
   ورود
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $phone = trim($_POST["phone"] ?? "");

    $password = $_POST["password"] ?? "";


    /* =====================================================
       بررسی خالی نبودن
    ====================================================== */

    if ($phone === "" || $password === "") {

        $error = "لطفاً شماره موبایل و رمز عبور را وارد کنید.";

    }


    /* =====================================================
       پیدا کردن کاربر
    ====================================================== */

    else {


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
                status
            FROM users
            WHERE phone = :phone
            LIMIT 1
        ");


        $stmt->execute([
            ":phone" => $phone
        ]);


        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        /* =================================================
           بررسی کاربر
        ================================================== */

        if (!$user) {

            $error = "شماره موبایل یا رمز عبور اشتباه است.";

        }


        /* =================================================
           بررسی وضعیت حساب
        ================================================== */

        elseif ($user["status"] !== "active") {

            $error =
                "حساب کاربری شما فعال نیست.";

        }


        /* =================================================
           بررسی رمز
        ================================================== */

        elseif (
            !password_verify(
                $password,
                $user["password"]
            )
        ) {

            $error =
                "شماره موبایل یا رمز عبور اشتباه است.";

        }


        /* =================================================
           ورود موفق
        ================================================== */

        else {


            /*
             * جلوگیری از Session Fixation
             */

            session_regenerate_id(true);


            /* اطلاعات کاربر */

            $_SESSION["user_id"] =
                (int) $user["id"];


            $_SESSION["role_id"] =
                (int) $user["role_id"];


            $_SESSION["first_name"] =
                $user["first_name"];


            $_SESSION["last_name"] =
                $user["last_name"];


            $_SESSION["full_name"] =
                $user["full_name"];


            $_SESSION["email"] =
                $user["email"];


            $_SESSION["phone"] =
                $user["phone"];


            /* زمان ورود */

            $_SESSION["login_time"] =
                time();


            /* =================================================
               انتقال
            ================================================== */

            if ((int) $user["role_id"] === 1) {

                header(
                    "Location: /HABIBI/admin/index.php"
                );

                exit;

            }


            header(
                "Location: /HABIBI/"
            );

            exit;

        }

    }

}


require_once __DIR__ . "/../includes/header.php";

?>


<main>

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-md-6 col-lg-5">


                <h1 class="text-center mb-4">
                    ورود به حساب</h1>


                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">

                        <?php
                        echo htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action=""
                >


                    <!-- شماره موبایل -->

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
                            required
                        >

                    </div>



                    <!-- رمز عبور -->

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
                                placeholder="رمز عبور"
                                required
                            >


                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePassword()"
                                title="نمایش رمز عبور"
                            >

                                <i
                                    class="bi bi-eye"
                                    id="passwordIcon"
                                ></i>

                            </button>

                        </div>

                    </div>



                    <!-- ورود -->

                    <button
                        type="submit"
                        class="btn btn-danger w-100"
                    >

                        <i class="bi bi-box-arrow-in-left"></i>

                        ورود

                    </button>


                </form>



                <!-- ثبت نام -->

                <div class="text-center mt-4">

                    حساب کاربری ندارید؟

                    <a href="/HABIBI/pages/register.php">

                        ثبت‌نام کنید

                    </a>

                </div>


            </div>

        </div>

    </div>

</main>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const icon =
        document.getElementById("passwordIcon");


    if (password.type === "password") {

        password.type = "text";

        icon.classList.remove("bi-eye");

        icon.classList.add("bi-eye-slash");

    } else {

        password.type = "password";

        icon.classList.remove("bi-eye-slash");

        icon.classList.add("bi-eye");

    }

}

</script>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
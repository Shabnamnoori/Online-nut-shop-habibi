<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================
   بررسی ادمین
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    (int)($_SESSION["role_id"] ?? 0) !== 1
) {
    header("Location: /HABIBI/");
    exit;
}


$error = "";
$message = "";


/* =========================
   تغییر وضعیت کاربر
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "status"
) {

    $userId = (int)($_POST["id"] ?? 0);
    $status = $_POST["status"] ?? "";


    $allowedStatuses = [
        "active",
        "inactive",
        "blocked"
    ];


    if (
        $userId <= 0 ||
        !in_array($status, $allowedStatuses, true)
    ) {

        $error = "اطلاعات واردشده صحیح نیست.";

    } elseif (
        $userId === (int)$_SESSION["user_id"]
    ) {

        $error = "نمی‌توانید وضعیت حساب خودتان را تغییر دهید.";

    } else {

        try {

            $stmt = $pdo->prepare("
                UPDATE users
                SET status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $status,
                $userId
            ]);

            header(
                "Location: /HABIBI/admin/users.php?updated=1"
            );

            exit;

        } catch (PDOException $e) {

            $error = "خطا در تغییر وضعیت کاربر.";
        }
    }
}


/* =========================
   پیام موفقیت
========================= */

if (isset($_GET["updated"])) {
    $message = "وضعیت کاربر با موفقیت تغییر کرد.";
}


/* =========================
   دریافت کاربران
========================= */

$stmt = $pdo->query("
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.full_name,
        u.email,
        u.phone,
        u.status,
        u.created_at,
        r.name AS role_name

    FROM users u

    INNER JOIN roles r
        ON r.id = u.role_id

    ORDER BY u.id DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        مدیریت کاربران | حبیبی
    </title>


    <link
        rel="stylesheet"
        href="/HABIBI/assets/bootstrap/css/bootstrap.rtl.min.css"
    >

    <link
        rel="stylesheet"
        href="/HABIBI/assets/bootstrap-icons/bootstrap-icons.css"
    >

</head>


<body>


<div class="container py-5">


    <!-- =========================
         Header
    ========================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                مدیریت کاربران
            </h2>

            <p class="text-muted mb-0">
                مشاهده و مدیریت کاربران فروشگاه
            </p>

        </div>


        <a
            href="/HABIBI/admin/index.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-right"></i>

            داشبورد

        </a>

    </div>


    <!-- =========================
         Messages
    ========================== -->

    <?php if ($message !== ""): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle"></i>

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle"></i>

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         Users Table
    ========================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">


            <?php if (empty($users)): ?>

                <div class="text-center py-5">

                    <i
                        class="bi bi-people text-muted"
                        style="font-size:60px;"
                    ></i>

                    <p class="text-muted mt-3">
                        هنوز کاربری ثبت نشده است.
                    </p>

                </div>

            <?php else: ?>


                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">


                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    نام و نام خانوادگی
                                </th>

                                <th>
                                    ایمیل
                                </th>

                                <th>
                                    تلفن
                                </th>

                                <th>
                                    نقش
                                </th>

                                <th>
                                    وضعیت
                                </th>

                                <th>
                                    تاریخ عضویت
                                </th>

                                <th>
                                    عملیات
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($users as $user): ?>


                            <tr>


                                <td>

                                    <?php echo (int)$user["id"]; ?>

                                </td>


                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $user["full_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $user["email"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $user["phone"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php if ($user["role_name"] === "admin"): ?>

                                        <span class="badge bg-danger">
                                            مدیر
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-primary">
                                            مشتری
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($user["status"] === "active"): ?>

                                        <span class="badge bg-success">
                                            فعال
                                        </span>

                                    <?php elseif ($user["status"] === "inactive"): ?>

                                        <span class="badge bg-secondary">
                                            غیرفعال
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            مسدود
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            $user["created_at"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </small>

                                </td>


                                <td>


                                    <?php
                                    $isCurrentUser =
                                        (int)$user["id"] ===
                                        (int)$_SESSION["user_id"];
                                    ?>


                                    <?php if (!$isCurrentUser): ?>


                                        <div class="dropdown">

                                            <button
                                                class="btn btn-sm btn-outline-dark dropdown-toggle"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                            >

                                                مدیریت

                                            </button>


                                            <ul class="dropdown-menu">


                                                <li>

                                                    <form
                                                        method="POST"
                                                        class="px-2"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="status"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?php echo (int)$user["id"]; ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="status"
                                                            value="active"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item"
                                                        >

                                                            <i class="bi bi-check-circle text-success"></i>

                                                            فعال کردن

                                                        </button>

                                                    </form>

                                                </li>


                                                <li>

                                                    <form
                                                        method="POST"
                                                        class="px-2"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="status"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?php echo (int)$user["id"]; ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="status"
                                                            value="inactive"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item"
                                                        >

                                                            <i class="bi bi-pause-circle text-secondary"></i>

                                                            غیرفعال کردن

                                                        </button>

                                                    </form>

                                                </li>


                                                <li>

                                                    <form
                                                        method="POST"
                                                        class="px-2"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="status"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?php echo (int)$user["id"]; ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="status"
                                                            value="blocked"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item text-danger"
                                                        >

                                                            <i class="bi bi-slash-circle"></i>

                                                            مسدود کردن

                                                        </button>

                                                    </form>

                                                </li>


                                            </ul>

                                        </div>


                                    <?php else: ?>

                                        <span class="text-muted small">
                                            حساب شما
                                        </span>

                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


<script
    src="/HABIBI/assets/bootstrap/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================
   بررسی دسترسی ادمین
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    (int)($_SESSION["role_id"] ?? 0) !== 1
) {
    header("Location: /HABIBI/");
    exit;
}


/* =========================
   آمار کاربران
========================= */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role_id = 2
");

$totalUsers = (int)$stmt->fetchColumn();


/* =========================
   آمار محصولات
========================= */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM products
");

$totalProducts = (int)$stmt->fetchColumn();


/* =========================
   محصولات فعال
========================= */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM products
    WHERE status = 'active'
");

$activeProducts = (int)$stmt->fetchColumn();


/* =========================
   تعداد سفارش‌ها
========================= */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM orders
");

$totalOrders = (int)$stmt->fetchColumn();


/* =========================
   سفارش‌های در انتظار
========================= */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status = 'pending'
");

$pendingOrders = (int)$stmt->fetchColumn();


/* =========================
   سفارش‌های تحویل شده
========================= */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status = 'delivered'
");

$deliveredOrders = (int)$stmt->fetchColumn();

/* =========================
   فروش موفق
========================= */

$stmt = $pdo->query("
    SELECT COALESCE(SUM(final_amount), 0)
    FROM orders
    WHERE status = 'delivered'
");

$totalSales = (int)$stmt->fetchColumn();

/* =========================
   آخرین سفارش‌ها
========================= */

$stmt = $pdo->query("
    SELECT
        o.id,
        o.order_number,
        o.final_amount,
        o.status,
        o.payment_status,
        o.created_at,
        u.full_name

    FROM orders o

    INNER JOIN users u
        ON u.id = o.user_id

    ORDER BY o.id DESC

    LIMIT 5
");

$latestOrders =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   وضعیت سفارش
========================= */

$statusLabels = [

    "pending" => "در انتظار بررسی",

    "confirmed" => "تأیید شده",

    "processing" => "در حال آماده‌سازی",

    "shipped" => "ارسال شده",

    "delivered" => "تحویل شده",

    "cancelled" => "لغو شده"

];

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
        پنل مدیریت | حبیبی
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


<div class="container-fluid py-5 px-4">


    <!-- =========================
         HEADER
    ========================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                پنل مدیریت حبیبی
            </h2>

            <p class="text-muted mb-0">

                مدیریت فروشگاه اینترنتی خشکبار حبیبی

            </p>

        </div>


        <a
            href="/HABIBI/"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-house"></i>

            مشاهده سایت

        </a>

    </div>


    <!-- =========================
         STATISTICS
    ========================== -->

    <div class="row g-4 mb-4">


        <!-- کاربران -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                کاربران
                            </small>

                            <h3 class="mt-2 mb-0">

                                <?php
                                echo $totalUsers;
                                ?>

                            </h3>

                        </div>


                        <div class="fs-1 text-primary">

                            <i class="bi bi-people"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- محصولات -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                محصولات
                            </small>

                            <h3 class="mt-2 mb-0">

                                <?php
                                echo $totalProducts;
                                ?>

                            </h3>

                            <small class="text-muted">

                                <?php
                                echo $activeProducts;
                                ?>

                                محصول فعال

                            </small>

                        </div>


                        <div class="fs-1 text-success">

                            <i class="bi bi-box-seam"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- سفارش‌ها -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                سفارش‌ها
                            </small>

                            <h3 class="mt-2 mb-0">

                                <?php
                                echo $totalOrders;
                                ?>

                            </h3>

                            <small class="text-warning">

                                <?php
                                echo $pendingOrders;
                                ?>

                                در انتظار بررسی

                            </small>

                        </div>


                        <div class="fs-1 text-warning">

                            <i class="bi bi-cart-check"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- فروش -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                فروش موفق
                            </small>

                            <h3 class="mt-2 mb-0">

                                <?php
                                echo number_format($totalSales);
                                ?>

                            </h3>

                            <small class="text-muted">
                                تومان
                            </small>

                        </div>


                        <div class="fs-1 text-danger">

                            <i class="bi bi-currency-dollar"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================
         QUICK ACCESS
    ========================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="mb-4">

                دسترسی سریع

            </h5>


            <div class="row g-3">


                <div class="col-md-3">

                    <a
                        href="/HABIBI/admin/orders.php"
                        class="btn btn-outline-primary w-100 py-3"
                    >

                        <i class="bi bi-box-seam"></i>

                        مدیریت سفارش‌ها

                    </a>

                </div>


                <div class="col-md-3">

                    <a
                        href="/HABIBI/admin/products.php"
                        class="btn btn-outline-success w-100 py-3"
                    >

                        <i class="bi bi-basket"></i>

                        مدیریت محصولات

                    </a>

                </div>


                <div class="col-md-3">

                    <a
                        href="/HABIBI/admin/categories.php"
                        class="btn btn-outline-warning w-100 py-3"
                    >

                        <i class="bi bi-grid"></i>

                        دسته‌بندی‌ها

                    </a>

                </div>


                <div class="col-md-3">

                    <a
                        href="/HABIBI/admin/users.php"
                        class="btn btn-outline-dark w-100 py-3"
                    >

                        <i class="bi bi-people"></i>

                        کاربران

                    </a>

                </div>


            </div>

        </div>

    </div>


    <!-- =========================
         LATEST ORDERS
    ========================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">


            <div class="d-flex justify-content-between align-items-center p-4">

                <h5 class="mb-0">

                    آخرین سفارش‌ها

                </h5>


                <a
                    href="/HABIBI/admin/orders.php"
                    class="btn btn-sm btn-outline-secondary"
                >

                    مشاهده همه

                </a>

            </div>


            <?php if (empty($latestOrders)): ?>

                <div class="text-center text-muted py-5">

                    هنوز سفارشی ثبت نشده است.

                </div>

            <?php else: ?>


                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">


                        <thead>

                            <tr>

                                <th>
                                    شماره سفارش
                                </th>

                                <th>
                                    مشتری
                                </th>

                                <th>
                                    مبلغ
                                </th>

                                <th>
                                    وضعیت
                                </th>

                                <th>
                                    عملیات
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($latestOrders as $order): ?>


                            <tr>


                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $order["order_number"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $order["full_name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo number_format(
                                        (int)$order["final_amount"]
                                    );
                                    ?>

                                    تومان

                                </td>


                                <td>

                                    <?php

                                    $status =
                                        $order["status"];

                                    ?>

                                    <?php if ($status === "delivered"): ?>

                                        <span class="badge bg-success">

                                            <?php
                                            echo $statusLabels[$status];
                                            ?>

                                        </span>

                                    <?php elseif ($status === "cancelled"): ?>

                                        <span class="badge bg-danger">

                                            <?php
                                            echo $statusLabels[$status];
                                            ?>

                                        </span>

                                    <?php elseif ($status === "processing"): ?>

                                        <span class="badge bg-warning text-dark">

                                            <?php
                                            echo $statusLabels[$status];
                                            ?>

                                        </span>

                                    <?php elseif ($status === "shipped"): ?>

                                        <span class="badge bg-info text-dark">

                                            <?php
                                            echo $statusLabels[$status];
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">

                                            <?php
                                            echo $statusLabels[$status];
                                            ?>

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <a
                                        href="/HABIBI/admin/order.php?id=<?php echo (int)$order["id"]; ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >

                                        <i class="bi bi-eye"></i>

                                        مشاهده

                                    </a>

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
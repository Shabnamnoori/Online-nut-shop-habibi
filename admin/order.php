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


/* =========================
   بررسی ID سفارش
========================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    header("Location: /HABIBI/admin/orders.php");
    exit;
}

$orderId = (int)$_GET["id"];


/* =========================
   دریافت سفارش
========================= */

$stmt = $pdo->prepare("
    SELECT
        o.*,
        u.full_name,
        u.email,
        u.phone,

        a.title AS address_title,
        a.recipient_name,
        a.province,
        a.city,
        a.postal_code,
        a.address AS shipping_address,
        a.phone AS address_phone

    FROM orders o

    INNER JOIN users u
        ON u.id = o.user_id

    LEFT JOIN addresses a
        ON a.id = o.address_id

    WHERE o.id = ?

    LIMIT 1
");

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$order) {
    header("Location: /HABIBI/admin/orders.php");
    exit;
}


/* =========================
   دریافت اقلام سفارش
========================= */

$stmt = $pdo->prepare("
    SELECT
        oi.*,
        p.image

    FROM order_items oi

    LEFT JOIN products p
        ON p.id = oi.product_id

    WHERE oi.order_id = ?

    ORDER BY oi.id ASC
");

$stmt->execute([$orderId]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   وضعیت‌ها
========================= */

$statusLabels = [

    "pending" => "در انتظار بررسی",
    "confirmed" => "تأیید شده",
    "processing" => "در حال آماده‌سازی",
    "shipped" => "ارسال شده",
    "delivered" => "تحویل داده شده",
    "cancelled" => "لغو شده"

];


$paymentLabels = [

    "unpaid" => "پرداخت نشده",
    "paid" => "پرداخت شده",
    "failed" => "ناموفق",
    "refunded" => "برگشت داده شده"

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
        جزئیات سفارش | حبیبی
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
                جزئیات سفارش
            </h2>

            <p class="text-muted mb-0">

                سفارش شماره

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $order["order_number"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </strong>

            </p>

        </div>


        <a
            href="/HABIBI/admin/orders.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-right"></i>

            بازگشت به سفارش‌ها

        </a>

    </div>


    <div class="row g-4">


        <!-- =========================
             اطلاعات مشتری
        ========================== -->

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="mb-4">

                        <i class="bi bi-person"></i>

                        اطلاعات مشتری

                    </h5>


                    <div class="mb-3">

                        <small class="text-muted">
                            نام و نام خانوادگی
                        </small>

                        <div>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $order["full_name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </strong>

                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">
                            ایمیل
                        </small>

                        <div>

                            <?php
                            echo htmlspecialchars(
                                $order["email"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </div>

                    </div>


                    <div>

                        <small class="text-muted">
                            تلفن
                        </small>

                        <div>

                            <?php
                            echo htmlspecialchars(
                                $order["phone"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
             وضعیت سفارش
        ========================== -->

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="mb-4">

                        <i class="bi bi-box-seam"></i>

                        وضعیت سفارش

                    </h5>


                    <div class="mb-3">

                        <small class="text-muted">
                            وضعیت سفارش
                        </small>

                        <div class="mt-1">

                            <?php

                            $status =
                                $order["status"];

                            ?>

                            <?php if ($status === "delivered"): ?>

                                <span class="badge bg-success">
                                    <?php echo $statusLabels[$status]; ?>
                                </span>

                            <?php elseif ($status === "cancelled"): ?>

                                <span class="badge bg-danger">
                                    <?php echo $statusLabels[$status]; ?>
                                </span>

                            <?php elseif ($status === "shipped"): ?>

                                <span class="badge bg-info text-dark">
                                    <?php echo $statusLabels[$status]; ?>
                                </span>

                            <?php elseif ($status === "processing"): ?>

                                <span class="badge bg-warning text-dark">
                                    <?php echo $statusLabels[$status]; ?>
                                </span>

                            <?php elseif ($status === "confirmed"): ?>

                                <span class="badge bg-primary">
                                    <?php echo $statusLabels[$status]; ?>
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    <?php echo $statusLabels[$status]; ?>
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                    <div>

                        <small class="text-muted">
                            وضعیت پرداخت
                        </small>

                        <div class="mt-1">

                            <?php

                            $payment =
                                $order["payment_status"];

                            ?>

                            <?php if ($payment === "paid"): ?>

                                <span class="badge bg-success">
                                    پرداخت شده
                                </span>

                            <?php elseif ($payment === "failed"): ?>

                                <span class="badge bg-danger">
                                    پرداخت ناموفق
                                </span>

                            <?php elseif ($payment === "refunded"): ?>

                                <span class="badge bg-warning text-dark">
                                    برگشت داده شده
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    پرداخت نشده
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
             محصولات سفارش
        ========================== -->

        <div class="col-12">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-0">

                    <div class="p-4 pb-3">

                        <h5 class="mb-0">

                            <i class="bi bi-basket"></i>

                            محصولات سفارش

                        </h5>

                    </div>


                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        محصول
                                    </th>

                                    <th>
                                        وزن
                                    </th>

                                    <th>
                                        تعداد
                                    </th>

                                    <th>
                                        قیمت واحد
                                    </th>

                                    <th>
                                        قیمت کل
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php foreach ($items as $item): ?>


                                <tr>


                                    <td>

                                        <div class="d-flex align-items-center gap-3">


                                            <?php if (!empty($item["image"])): ?>

                                                <img
                                                    src="/HABIBI/assets/images/products/<?php echo htmlspecialchars($item["image"], ENT_QUOTES, "UTF-8"); ?>"
                                                    alt=""
                                                    width="60"
                                                    height="60"
                                                    style="object-fit:cover;border-radius:8px;"
                                                >

                                            <?php endif; ?>


                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $item["product_name"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                                ?>

                                            </strong>

                                        </div>

                                    </td>


                                    <td>

                                        <?php if ($item["weight"] !== null): ?>

                                            <?php
                                            echo htmlspecialchars(
                                                $item["weight"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>

                                            <?php
                                            echo htmlspecialchars(
                                                $item["unit"] ?? "گرم",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo (int)$item["quantity"];
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo number_format(
                                            (int)$item["unit_price"]
                                        );
                                        ?>

                                        تومان

                                    </td>


                                    <td>

                                        <strong>

                                            <?php
                                            echo number_format(
                                                (int)$item["total_price"]
                                            );
                                            ?>

                                            تومان

                                        </strong>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
             آدرس ارسال
        ========================== -->

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="mb-4">

                        <i class="bi bi-geo-alt"></i>

                        آدرس ارسال

                    </h5>


                    <?php if (!empty($order["shipping_address"])): ?>


                        <?php if (!empty($order["recipient_name"])): ?>

                            <p>
                                <strong>
                                    گیرنده:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $order["recipient_name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </p>

                        <?php endif; ?>


                        <p>

                            <strong>
                                استان:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $order["province"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                        <p>

                            <strong>
                                شهر:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $order["city"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                        <p>

                            <strong>
                                آدرس:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $order["shipping_address"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                        <?php if (!empty($order["postal_code"])): ?>

                            <p>

                                <strong>
                                    کد پستی:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $order["postal_code"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </p>

                        <?php endif; ?>


                        <?php if (!empty($order["address_phone"])): ?>

                            <p class="mb-0">

                                <strong>
                                    تلفن گیرنده:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $order["address_phone"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </p>

                        <?php endif; ?>


                    <?php else: ?>

                        <p class="text-muted mb-0">
                            آدرس ارسال برای این سفارش ثبت نشده است.
                        </p>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- =========================
             خلاصه مالی
        ========================== -->

        <div class="col-lg-5">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="mb-4">

                        <i class="bi bi-receipt"></i>

                        خلاصه سفارش

                    </h5>


                    <div class="d-flex justify-content-between mb-3">

                        <span>
                            مبلغ محصولات
                        </span>

                        <strong>

                            <?php
                            echo number_format(
                                (int)$order["total_amount"]
                            );
                            ?>

                            تومان

                        </strong>

                    </div>


                    <div class="d-flex justify-content-between mb-3">

                        <span>
                            هزینه ارسال
                        </span>

                        <strong>

                            <?php
                            echo number_format(
                                (int)$order["shipping_cost"]
                            );
                            ?>

                            تومان

                        </strong>

                    </div>


                    <div class="d-flex justify-content-between mb-3">

                        <span>
                            تخفیف
                        </span>

                        <strong class="text-danger">

                            -

                            <?php
                            echo number_format(
                                (int)$order["discount_amount"]
                            );
                            ?>

                            تومان

                        </strong>

                    </div>


                    <hr>


                    <div class="d-flex justify-content-between">

                        <strong>
                            مبلغ نهایی
                        </strong>

                        <strong class="fs-5">

                            <?php
                            echo number_format(
                                (int)$order["final_amount"]
                            );
                            ?>

                            تومان

                        </strong>

                    </div>


                </div>

            </div>

        </div>


        <!-- =========================
             توضیحات
        ========================== -->

        <?php if (!empty($order["notes"])): ?>

            <div class="col-12">

                <div class="alert alert-light border">

                    <strong>

                        <i class="bi bi-chat-left-text"></i>

                        توضیحات سفارش:

                    </strong>

                    <span>

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $order["notes"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                        );
                        ?>

                    </span>

                </div>

            </div>

        <?php endif; ?>


    </div>


</div>


<script
    src="/HABIBI/assets/bootstrap/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
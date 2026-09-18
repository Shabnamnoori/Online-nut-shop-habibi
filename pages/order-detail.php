<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================
   بررسی ورود
========================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: /HABIBI/pages/login.php");
    exit;

}

$userId = (int)$_SESSION["user_id"];


/* =========================
   بررسی ID سفارش
========================= */

$orderId = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($orderId <= 0) {

    header("Location: /HABIBI/pages/orders.php");
    exit;

}


/* =========================
   اطلاعات سفارش
========================= */

$stmt = $pdo->prepare("
    SELECT
        o.*,
        a.title AS address_title,
        a.recipient_name,
        a.phone AS address_phone,
        a.province,
        a.city,
        a.postal_code,
        a.address
    FROM orders o

    LEFT JOIN addresses a
        ON a.id = o.address_id

    WHERE o.id = ?
    AND o.user_id = ?

    LIMIT 1
");

$stmt->execute([
    $orderId,
    $userId
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$order) {

    header("Location: /HABIBI/pages/orders.php");
    exit;

}


/* =========================
   محصولات سفارش
========================= */

$stmt = $pdo->prepare("
    SELECT
        product_name,
        weight,
        unit,
        quantity,
        unit_price,
        total_price
    FROM order_items
    WHERE order_id = ?
    ORDER BY id ASC
");

$stmt->execute([$orderId]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   وضعیت سفارش
========================= */

$orderStatuses = [

    "pending" => "در انتظار بررسی",

    "confirmed" => "تأیید شده",

    "processing" => "در حال آماده‌سازی",

    "shipped" => "ارسال شده",

    "delivered" => "تحویل داده شده",

    "cancelled" => "لغو شده"

];

$status =
    $orderStatuses[$order["status"]]
    ?? $order["status"];


/* =========================
   وضعیت پرداخت
========================= */

$paymentStatuses = [

    "unpaid" => "پرداخت نشده",

    "paid" => "پرداخت شده",

    "failed" => "ناموفق",

    "refunded" => "برگشت داده شده"

];

$paymentStatus =
    $paymentStatuses[$order["payment_status"]]
    ?? $order["payment_status"];


require_once __DIR__ . "/../includes/header.php";

?>


<div class="container py-5">


    <!-- =========================
         عنوان
    ========================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-2">
                جزئیات سفارش
            </h2>

            <p class="text-muted mb-0">

                سفارش شماره:

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
            href="/HABIBI/pages/orders.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-right"></i>

            بازگشت

        </a>

    </div>


    <!-- =========================
         اطلاعات کلی
    ========================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-4">

            <div class="row g-4">


                <div class="col-md-3">

                    <small class="text-muted d-block mb-1">
                        شماره سفارش
                    </small>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $order["order_number"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </strong>

                </div>


                <div class="col-md-3">

                    <small class="text-muted d-block mb-1">
                        تاریخ ثبت
                    </small>

                    <strong>

                        <?php
                        echo date(
                            "Y/m/d H:i",
                            strtotime($order["created_at"])
                        );
                        ?>

                    </strong>

                </div>


                <div class="col-md-3">

                    <small class="text-muted d-block mb-1">
                        وضعیت سفارش
                    </small>

                    <span class="badge bg-warning text-dark">

                        <?php echo $status; ?>

                    </span>

                </div>


                <div class="col-md-3">

                    <small class="text-muted d-block mb-1">
                        وضعیت پرداخت
                    </small>

                    <span class="badge bg-light text-dark">

                        <?php echo $paymentStatus; ?>

                    </span>

                </div>


            </div>

        </div>

    </div>


    <div class="row g-4">


        <!-- =========================
             محصولات
        ========================== -->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h4 class="mb-4">

                        <i class="bi bi-bag"></i>

                        محصولات سفارش

                    </h4>


                    <?php foreach ($items as $item): ?>


                        <div class="border rounded p-3 mb-3">


                            <div class="row align-items-center g-3">


                                <div class="col-md-4">

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


                                <div class="col-md-2">

                                    <small class="text-muted d-block">
                                        وزن
                                    </small>

                                    <?php
                                    echo htmlspecialchars(
                                        $item["weight"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                    <?php
                                    echo htmlspecialchars(
                                        $item["unit"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </div>


                                <div class="col-md-2">

                                    <small class="text-muted d-block">
                                        تعداد
                                    </small>

                                    <?php echo (int)$item["quantity"]; ?>

                                </div>


                                <div class="col-md-2">

                                    <small class="text-muted d-block">
                                        قیمت واحد
                                    </small>

                                    <?php
                                    echo number_format(
                                        $item["unit_price"]
                                    );
                                    ?>

                                    تومان

                                </div>


                                <div class="col-md-2 text-md-end">

                                    <small class="text-muted d-block">
                                        مبلغ
                                    </small>

                                    <strong>

                                        <?php
                                        echo number_format(
                                            $item["total_price"]
                                        );
                                        ?>

                                        تومان

                                    </strong>

                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>

            </div>

        </div>


        <!-- =========================
             خلاصه سفارش
        ========================== -->

        <div class="col-lg-4">


            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <h5 class="mb-4">
                        خلاصه پرداخت
                    </h5>


                    <div class="d-flex justify-content-between mb-3">

                        <span>
                            مبلغ کالاها
                        </span>

                        <strong>

                            <?php
                            echo number_format(
                                $order["total_amount"]
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

                            echo $order["shipping_cost"] > 0
                                ? number_format(
                                    $order["shipping_cost"]
                                ) . " تومان"
                                : "رایگان";

                            ?>

                        </strong>

                    </div>


                    <?php if ($order["discount_amount"] > 0): ?>

                        <div class="d-flex justify-content-between mb-3">

                            <span>
                                تخفیف
                            </span>

                            <strong class="text-success">

                                -

                                <?php
                                echo number_format(
                                    $order["discount_amount"]
                                );
                                ?>

                                تومان

                            </strong>

                        </div>

                    <?php endif; ?>


                    <hr>


                    <div class="d-flex justify-content-between">

                        <strong>
                            مبلغ نهایی
                        </strong>

                        <strong>

                            <?php
                            echo number_format(
                                $order["final_amount"]
                            );
                            ?>

                            تومان

                        </strong>

                    </div>

                </div>

            </div>


            <!-- =========================
                 آدرس
            ========================== -->

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h5 class="mb-4">

                        <i class="bi bi-geo-alt"></i>

                        آدرس ارسال

                    </h5>


                    <?php if (!empty($order["recipient_name"])): ?>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $order["recipient_name"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </strong>


                        <p class="mt-2 mb-2">

                            <?php
                            echo htmlspecialchars(
                                $order["province"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $order["city"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                        <p class="mb-2">

                            <?php
                            echo htmlspecialchars(
                                $order["address"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                        <?php if (!empty($order["postal_code"])): ?>

                            <p class="mb-2">

                                کد پستی:

                                <?php
                                echo htmlspecialchars(
                                    $order["postal_code"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </p>

                        <?php endif; ?>


                        <p class="mb-0">

                            تلفن:

                            <?php
                            echo htmlspecialchars(
                                $order["address_phone"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </p>


                    <?php else: ?>

                        <p class="text-muted mb-0">

                            اطلاعات آدرس در دسترس نیست.

                        </p>

                    <?php endif; ?>


                </div>

            </div>


        </div>


    </div>

</div>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
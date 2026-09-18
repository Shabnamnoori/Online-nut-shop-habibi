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
   دریافت سفارش‌ها
========================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        order_number,
        total_amount,
        shipping_cost,
        discount_amount,
        final_amount,
        status,
        payment_status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->execute([$userId]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


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


/* =========================
   وضعیت پرداخت
========================= */

$paymentStatuses = [

    "unpaid" => "پرداخت نشده",

    "paid" => "پرداخت شده",

    "failed" => "ناموفق",

    "refunded" => "مبلغ برگشت داده شده"

];


require_once __DIR__ . "/../includes/header.php";

?>


<div class="container py-5">


    <!-- =========================
         عنوان
    ========================== -->

    <div class="text-center mb-5">

        <h2>
            سفارش‌های من
        </h2>

        <p class="text-muted">
            مشاهده و پیگیری سفارش‌های ثبت‌شده
        </p>

    </div>


    <?php if (empty($orders)): ?>


        <!-- =========================
             بدون سفارش
        ========================== -->

        <div class="text-center py-5">

            <i
                class="bi bi-box-seam text-muted"
                style="font-size: 70px;"
            ></i>


            <h4 class="mt-4">

                هنوز سفارشی ثبت نکرده‌اید.

            </h4>


            <p class="text-muted">

                محصولات حبیبی را ببینید و اولین سفارش خود را ثبت کنید.

            </p>


            <a
                href="/HABIBI/pages/products.php"
                class="btn btn-danger mt-3"
            >

                <i class="bi bi-shop"></i>

                مشاهده محصولات

            </a>

        </div>


    <?php else: ?>


        <!-- =========================
             لیست سفارش‌ها
        ========================== -->

        <div class="row g-4">


            <?php foreach ($orders as $order): ?>


                <?php

                $status =
                    $orderStatuses[$order["status"]]
                    ?? $order["status"];

                $paymentStatus =
                    $paymentStatuses[$order["payment_status"]]
                    ?? $order["payment_status"];


                $statusClass = "bg-warning text-dark";


                if ($order["status"] === "confirmed") {
                    $statusClass = "bg-info text-dark";
                }

                if ($order["status"] === "processing") {
                    $statusClass = "bg-primary";
                }

                if ($order["status"] === "shipped") {
                    $statusClass = "bg-info text-dark";
                }

                if ($order["status"] === "delivered") {
                    $statusClass = "bg-success";
                }

                if ($order["status"] === "cancelled") {
                    $statusClass = "bg-danger";
                }


                $paymentClass = "bg-warning text-dark";

                if ($order["payment_status"] === "paid") {
                    $paymentClass = "bg-success";
                }

                if ($order["payment_status"] === "failed") {
                    $paymentClass = "bg-danger";
                }

                ?>


                <div class="col-12">


                    <div class="card border-0 shadow-sm">


                        <div class="card-body p-4">


                            <div class="row align-items-center g-4">


                                <!-- شماره سفارش -->

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


                                <!-- تاریخ -->

                                <div class="col-md-2">

                                    <small class="text-muted d-block mb-1">

                                        تاریخ ثبت

                                    </small>

                                    <span>

                                        <?php
                                        echo date(
                                            "Y/m/d",
                                            strtotime(
                                                $order["created_at"]
                                            )
                                        );
                                        ?>

                                    </span>

                                </div>


                                <!-- مبلغ -->

                                <div class="col-md-2">

                                    <small class="text-muted d-block mb-1">

                                        مبلغ نهایی

                                    </small>

                                    <strong>

                                        <?php
                                        echo number_format(
                                            $order["final_amount"]
                                        );
                                        ?>

                                        تومان

                                    </strong>

                                </div>


                                <!-- وضعیت -->

                                <div class="col-md-2">

                                    <small class="text-muted d-block mb-1">

                                        وضعیت سفارش

                                    </small>


                                    <span
                                        class="badge <?php echo $statusClass; ?>"
                                    >

                                        <?php echo $status; ?>

                                    </span>

                                </div>


                                <!-- پرداخت -->

                                <div class="col-md-2">

                                    <small class="text-muted d-block mb-1">

                                        پرداخت

                                    </small>


                                    <span class="badge bg-light text-dark">

                                        <?php echo $paymentStatus; ?>

                                    </span>

                                </div>


                                <!-- جزئیات -->

                                <div class="col-md-1 text-md-end">

                                    <a
                                        href="/HABIBI/pages/order-detail.php?id=<?php echo (int)$order["id"]; ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        title="جزئیات سفارش"
                                    >

                                        <i class="bi bi-chevron-left"></i>

                                    </a>

                                </div>


                            </div>


                        </div>

                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
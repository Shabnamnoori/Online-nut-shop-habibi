<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================
   بررسی سفارش
========================= */

$orderId = (int)($_SESSION["last_order_id"] ?? 0);
$orderNumber = $_SESSION["last_order_number"] ?? "";


if ($orderId <= 0 || $orderNumber === "") {

    header("Location: /HABIBI/");
    exit;

}


/* =========================
   دریافت اطلاعات سفارش
========================= */

$userId = (int)($_SESSION["user_id"] ?? 0);


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
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
");

$stmt->execute([
    $orderId,
    $userId
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$order) {

    header("Location: /HABIBI/");
    exit;

}


require_once __DIR__ . "/../includes/header.php";

?>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">


            <div class="card border-0 shadow-sm text-center">

                <div class="card-body p-5">


                    <!-- =========================
                         آیکون موفقیت
                    ========================== -->

                    <div class="mb-4">

                        <i
                            class="bi bi-check-circle-fill text-success"
                            style="font-size: 70px;"
                        ></i>

                    </div>


                    <!-- =========================
                         عنوان
                    ========================== -->

                    <h2 class="mb-3">

                        سفارش شما با موفقیت ثبت شد 🎉

                    </h2>


                    <p class="text-muted mb-4">

                        از خرید شما از فروشگاه حبیبی سپاسگزاریم.

                    </p>


                    <!-- =========================
                         شماره سفارش
                    ========================== -->

                    <div class="alert alert-light border mb-4">

                        <div class="mb-2">

                            <span class="text-muted">
                                شماره سفارش:
                            </span>

                        </div>

                        <strong class="fs-5">

                            <?php
                            echo htmlspecialchars(
                                $order["order_number"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </strong>

                    </div>


                    <!-- =========================
                         مبلغ
                    ========================== -->

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
                                ? number_format($order["shipping_cost"]) . " تومان"
                                : "رایگان";
                            ?>

                        </strong>

                    </div>


                    <?php if ((float)$order["discount_amount"] > 0): ?>

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


                    <div class="d-flex justify-content-between mb-4">

                        <strong>
                            مبلغ نهایی
                        </strong>

                        <strong class="fs-5">

                            <?php
                            echo number_format(
                                $order["final_amount"]
                            );
                            ?>

                            تومان

                        </strong>

                    </div>


                    <!-- =========================
                         وضعیت سفارش
                    ========================== -->

                    <div class="alert alert-warning">

                        <i class="bi bi-clock"></i>

                        وضعیت سفارش:

                        <strong>
                            در انتظار بررسی
                        </strong>

                    </div>


                    <div class="alert alert-light border">

                        <i class="bi bi-cash"></i>

                        روش پرداخت:

                        <strong>
                            پرداخت در محل
                        </strong>

                    </div>


                    <!-- =========================
                         دکمه‌ها
                    ========================== -->

                    <div class="d-flex gap-2 justify-content-center flex-wrap mt-4">

                        <a
                            href="/HABIBI/pages/orders.php"
                            class="btn btn-danger"
                        >

                            <i class="bi bi-box-seam"></i>

                            سفارش‌های من

                        </a>


                        <a
                            href="/HABIBI/"
                            class="btn btn-outline-secondary"
                        >

                            <i class="bi bi-house"></i>

                            بازگشت به فروشگاه

                        </a>

                    </div>


                </div>

            </div>


        </div>

    </div>

</div>


<?php

/* =========================
   پاک کردن اطلاعات موقت
========================= */

unset($_SESSION["last_order_id"]);
unset($_SESSION["last_order_number"]);


require_once __DIR__ . "/../includes/footer.php";

?>
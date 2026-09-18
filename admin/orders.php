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
   تغییر وضعیت سفارش
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "status"
) {

    $orderId = (int)($_POST["id"] ?? 0);
    $status = $_POST["status"] ?? "";


    $allowedStatuses = [
        "pending",
        "confirmed",
        "processing",
        "shipped",
        "delivered",
        "cancelled"
    ];


    if (
        $orderId <= 0 ||
        !in_array($status, $allowedStatuses, true)
    ) {

        $error = "اطلاعات واردشده صحیح نیست.";

    } else {

        try {

            $stmt = $pdo->prepare("
                UPDATE orders
                SET status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $status,
                $orderId
            ]);

            header(
                "Location: /HABIBI/admin/orders.php?updated=1"
            );

            exit;

        } catch (PDOException $e) {

            $error = "خطا در تغییر وضعیت سفارش.";
        }
    }
}


/* =========================
   پیام موفقیت
========================= */

if (isset($_GET["updated"])) {

    $message =
        "وضعیت سفارش با موفقیت تغییر کرد.";
}


/* =========================
   دریافت سفارش‌ها
========================= */

$stmt = $pdo->query("
    SELECT
        o.id,
        o.order_number,
        o.total_amount,
        o.shipping_cost,
        o.discount_amount,
        o.final_amount,
        o.status,
        o.payment_status,
        o.created_at,

        u.full_name,
        u.phone

    FROM orders o

    INNER JOIN users u
        ON u.id = o.user_id

    ORDER BY o.id DESC
");

$orders =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   متن وضعیت
========================= */

$statusLabels = [

    "pending" =>
        "در انتظار بررسی",

    "confirmed" =>
        "تأیید شده",

    "processing" =>
        "در حال آماده‌سازی",

    "shipped" =>
        "ارسال شده",

    "delivered" =>
        "تحویل داده شده",

    "cancelled" =>
        "لغو شده"

];


$paymentLabels = [

    "unpaid" =>
        "پرداخت نشده",

    "paid" =>
        "پرداخت شده",

    "failed" =>
        "ناموفق",

    "refunded" =>
        "برگشت داده شده"

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
        مدیریت سفارش‌ها | حبیبی
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
         Header
    ========================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                مدیریت سفارش‌ها
            </h2>

            <p class="text-muted mb-0">
                مشاهده و مدیریت سفارش‌های مشتریان
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
         Orders
    ========================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">


            <?php if (empty($orders)): ?>

                <div class="text-center py-5">

                    <i
                        class="bi bi-box-seam text-muted"
                        style="font-size:60px;"
                    ></i>

                    <p class="text-muted mt-3 mb-0">
                        هنوز سفارشی ثبت نشده است.
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
                                    شماره سفارش
                                </th>

                                <th>
                                    مشتری
                                </th>

                                <th>
                                    تلفن
                                </th>

                                <th>
                                    مبلغ نهایی
                                </th>

                                <th>
                                    پرداخت
                                </th>

                                <th>
                                    وضعیت سفارش
                                </th>

                                <th>
                                    تاریخ
                                </th>

                                <th>
                                    عملیات
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($orders as $order): ?>


                            <tr>


                                <td>

                                    <?php
                                    echo (int)$order["id"];
                                    ?>

                                </td>


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
                                    echo htmlspecialchars(
                                        $order["phone"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </td>


                                <td>

                                    <strong>

                                        <?php
                                        echo number_format(
                                            (int)$order["final_amount"]
                                        );
                                        ?>

                                        تومان

                                    </strong>

                                </td>


                                <!-- پرداخت -->

                                <td>

                                    <?php

                                    $paymentStatus =
                                        $order["payment_status"];

                                    ?>

                                    <?php if ($paymentStatus === "paid"): ?>

                                        <span class="badge bg-success">

                                            <?php
                                            echo $paymentLabels[$paymentStatus];
                                            ?>

                                        </span>

                                    <?php elseif ($paymentStatus === "failed"): ?>

                                        <span class="badge bg-danger">

                                            <?php
                                            echo $paymentLabels[$paymentStatus];
                                            ?>

                                        </span>

                                    <?php elseif ($paymentStatus === "refunded"): ?>

                                        <span class="badge bg-warning text-dark">

                                            <?php
                                            echo $paymentLabels[$paymentStatus];
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">

                                            <?php
                                            echo $paymentLabels[$paymentStatus];
                                            ?>

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- وضعیت سفارش -->

                                <td>

                                    <?php

                                    $orderStatus =
                                        $order["status"];

                                    ?>


                                    <?php if ($orderStatus === "delivered"): ?>

                                        <span class="badge bg-success">

                                            <?php
                                            echo $statusLabels[$orderStatus];
                                            ?>

                                        </span>

                                    <?php elseif ($orderStatus === "cancelled"): ?>

                                        <span class="badge bg-danger">

                                            <?php
                                            echo $statusLabels[$orderStatus];
                                            ?>

                                        </span>

                                    <?php elseif ($orderStatus === "shipped"): ?>

                                        <span class="badge bg-info text-dark">

                                            <?php
                                            echo $statusLabels[$orderStatus];
                                            ?>

                                        </span>

                                    <?php elseif ($orderStatus === "processing"): ?>

                                        <span class="badge bg-warning text-dark">

                                            <?php
                                            echo $statusLabels[$orderStatus];
                                            ?>

                                        </span>

                                    <?php elseif ($orderStatus === "confirmed"): ?>

                                        <span class="badge bg-primary">

                                            <?php
                                            echo $statusLabels[$orderStatus];
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">

                                            <?php
                                            echo $statusLabels[$orderStatus];
                                            ?>

                                        </span>

                                    <?php endif; ?>


                                </td>


                                <!-- تاریخ -->

                                <td>

                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            $order["created_at"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </small>

                                </td>


                                <!-- عملیات -->

                                <td>

                                    <div class="d-flex gap-1">


                                        <!-- جزئیات -->

                                        <a
                                            href="/HABIBI/admin/order.php?id=<?php echo (int)$order["id"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="جزئیات سفارش"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <!-- تغییر وضعیت -->

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#statusModal<?php echo (int)$order["id"]; ?>"
                                            title="تغییر وضعیت"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </button>


                                    </div>

                                </td>


                            </tr>


                            <!-- =========================
                                 Modal وضعیت
                            ========================== -->

                            <div
                                class="modal fade"
                                id="statusModal<?php echo (int)$order["id"]; ?>"
                                tabindex="-1"
                            >

                                <div class="modal-dialog">

                                    <div class="modal-content">


                                        <form method="POST">


                                            <div class="modal-header">

                                                <h5 class="modal-title">

                                                    تغییر وضعیت سفارش

                                                </h5>


                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                ></button>

                                            </div>


                                            <div class="modal-body">


                                                <p>

                                                    سفارش:

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


                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="status"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php echo (int)$order["id"]; ?>"
                                                >


                                                <label class="form-label">

                                                    وضعیت جدید

                                                </label>


                                                <select
                                                    name="status"
                                                    class="form-select"
                                                    required
                                                >


                                                    <?php foreach ($statusLabels as $key => $label): ?>

                                                        <option
                                                            value="<?php echo $key; ?>"
                                                            <?php echo $orderStatus === $key ? "selected" : ""; ?>
                                                        >

                                                            <?php
                                                            echo $label;
                                                            ?>

                                                        </option>

                                                    <?php endforeach; ?>


                                                </select>


                                            </div>


                                            <div class="modal-footer">

                                                <button
                                                    type="button"
                                                    class="btn btn-secondary"
                                                    data-bs-dismiss="modal"
                                                >

                                                    انصراف

                                                </button>


                                                <button
                                                    type="submit"
                                                    class="btn btn-danger"
                                                >

                                                    ذخیره تغییرات

                                                </button>

                                            </div>


                                        </form>

                                    </div>

                                </div>

                            </div>


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
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /HABIBI/pages/login.php");
    exit;
}

$userId = (int)$_SESSION["user_id"];

if (empty($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    header("Location: /HABIBI/pages/cart.php");
    exit;
}

$error = "";


/* =========================
   اطلاعات کاربر
========================= */

$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, full_name, email, phone
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /HABIBI/pages/logout.php");
    exit;
}


/* =========================
   آدرس‌های کاربر
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM addresses
    WHERE user_id = ?
    ORDER BY is_default DESC, id DESC
");

$stmt->execute([$userId]);

$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   محاسبه سبد
========================= */

$cartTotal = 0;

foreach ($_SESSION["cart"] as $item) {

    $price = (float)($item["price"] ?? 0);
    $quantity = (int)($item["quantity"] ?? 0);

    $cartTotal += $price * $quantity;
}

$shippingCost = 0;
$discountAmount = 0;
$finalAmount = $cartTotal;


/* =========================
   ثبت سفارش
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["place_order"])) {

    try {

        $pdo->beginTransaction();


        /* =====================
           انتخاب آدرس
        ===================== */

        $useNewAddress = isset($_POST["new_address"]) &&
                         $_POST["new_address"] === "1";

        $addressId = 0;


        /* آدرس جدید */

        if ($useNewAddress) {

            $recipientName = trim($_POST["recipient_name"] ?? "");
            $phone = trim($_POST["phone"] ?? "");
            $province = trim($_POST["province"] ?? "");
            $city = trim($_POST["city"] ?? "");
            $postalCode = trim($_POST["postal_code"] ?? "");
            $address = trim($_POST["address"] ?? "");
            $title = trim($_POST["title"] ?? "");


            if (
                $recipientName === "" ||
                $phone === "" ||
                $province === "" ||
                $city === "" ||
                $address === ""
            ) {
                throw new Exception("لطفاً اطلاعات آدرس را کامل وارد کنید.");
            }


            $stmt = $pdo->prepare("
                INSERT INTO addresses
                (
                    user_id,
                    title,
                    recipient_name,
                    phone,
                    province,
                    city,
                    postal_code,
                    address,
                    is_default
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");

            $stmt->execute([
                $userId,
                $title,
                $recipientName,
                $phone,
                $province,
                $city,
                $postalCode !== "" ? $postalCode : null,
                $address
            ]);

            $addressId = (int)$pdo->lastInsertId();

        } else {

            $addressId = isset($_POST["address_id"])
                ? (int)$_POST["address_id"]
                : 0;


            if ($addressId <= 0) {
                throw new Exception("لطفاً یک آدرس انتخاب کنید.");
            }


            /* بررسی تعلق آدرس به کاربر */

            $stmt = $pdo->prepare("
                SELECT id
                FROM addresses
                WHERE id = ? AND user_id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $addressId,
                $userId
            ]);

            if (!$stmt->fetch()) {
                throw new Exception("آدرس انتخاب‌شده معتبر نیست.");
            }
        }


        /* =====================
           شماره سفارش
        ===================== */

        $orderNumber =
            "HAB" .
            date("YmdHis") .
            rand(100, 999);


        /* =====================
           ایجاد سفارش
        ===================== */

        $stmt = $pdo->prepare("
            INSERT INTO orders
            (
                user_id,
                address_id,
                order_number,
                total_amount,
                shipping_cost,
                discount_amount,
                final_amount,
                status,
                payment_status,
                notes
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?)
        ");

        $stmt->execute([
            $userId,
            $addressId,
            $orderNumber,
            $cartTotal,
            $shippingCost,
            $discountAmount,
            $finalAmount,
            trim($_POST["notes"] ?? "")
        ]);

        $orderId = (int)$pdo->lastInsertId();


        /* =====================
           محصولات سفارش
        ===================== */

        foreach ($_SESSION["cart"] as $item) {

            $productId = (int)($item["product_id"] ?? 0);
            $weightId = (int)($item["weight_id"] ?? 0);
            $quantity = (int)($item["quantity"] ?? 0);


            if ($productId <= 0 || $weightId <= 0 || $quantity <= 0) {
                throw new Exception("اطلاعات یکی از محصولات سبد خرید نامعتبر است.");
            }


            /* دریافت محصول و وزن */

            $stmt = $pdo->prepare("
                SELECT
                    p.id AS product_id,
                    p.name AS product_name,
                    p.status AS product_status,
                    pw.id AS weight_id,
                    pw.weight,
                    pw.unit,
                    pw.price,
                    pw.stock,
                    pw.status AS weight_status
                FROM products p
                INNER JOIN product_weights pw
                    ON pw.product_id = p.id
                WHERE p.id = ?
                AND pw.id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $productId,
                $weightId
            ]);

            $product = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$product) {
                throw new Exception("یکی از محصولات سبد خرید پیدا نشد.");
            }


            if (
                $product["product_status"] !== "active" ||
                $product["weight_status"] !== "active"
            ) {
                throw new Exception(
                    "یکی از محصولات دیگر قابل خرید نیست."
                );
            }


            /* بررسی موجودی */

            if ($quantity > (int)$product["stock"]) {
                throw new Exception(
                    "موجودی «" .
                    $product["product_name"] .
                    "» کافی نیست."
                );
            }


            $unitPrice = (float)$product["price"];
            $totalPrice = $unitPrice * $quantity;


            /* ثبت آیتم سفارش */

            $stmt = $pdo->prepare("
                INSERT INTO order_items
                (
                    order_id,
                    product_id,
                    product_weight_id,
                    product_name,
                    weight,
                    unit,
                    quantity,
                    unit_price,
                    total_price
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $orderId,
                $productId,
                $weightId,
                $product["product_name"],
                $product["weight"],
                $product["unit"],
                $quantity,
                $unitPrice,
                $totalPrice
            ]);


            /* کم کردن موجودی */

            $stmt = $pdo->prepare("
                UPDATE product_weights
                SET stock = stock - ?
                WHERE id = ?
                AND stock >= ?
            ");

            $stmt->execute([
                $quantity,
                $weightId,
                $quantity
            ]);


            if ($stmt->rowCount() !== 1) {
                throw new Exception(
                    "موجودی محصول تغییر کرده است. لطفاً دوباره تلاش کنید."
                );
            }
        }


        /* =====================
           ثبت پرداخت
        ===================== */

        $stmt = $pdo->prepare("
            INSERT INTO payments
            (
                order_id,
                amount,
                method,
                status
            )
            VALUES (?, ?, 'cash_on_delivery', 'pending')
        ");

        $stmt->execute([
            $orderId,
            $finalAmount
        ]);


        /* =====================
           پایان تراکنش
        ===================== */

        $pdo->commit();


        $_SESSION["cart"] = [];
        $_SESSION["last_order_id"] = $orderId;
        $_SESSION["last_order_number"] = $orderNumber;


        header(
            "Location: /HABIBI/pages/order-success.php"
        );

        exit;


    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = $e->getMessage();
    }
}


require_once __DIR__ . "/../includes/header.php";

?>


<div class="container py-5">

    <div class="text-center mb-5">

        <h2>
            تکمیل سفارش
        </h2>

        <p class="text-muted">
            اطلاعات ارسال سفارش خود را وارد کنید.
        </p>

    </div>


    <?php if ($error): ?>

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


    <form method="POST">

        <div class="row g-4">


            <!-- =========================
                 اطلاعات ارسال
            ========================== -->

            <div class="col-lg-7">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <h4 class="mb-4">

                            <i class="bi bi-geo-alt"></i>

                            آدرس ارسال

                        </h4>


                        <?php if (!empty($addresses)): ?>

                            <div class="mb-4">

                                <label class="form-label fw-bold">

                                    انتخاب آدرس

                                </label>


                                <?php foreach ($addresses as $address): ?>

                                    <div class="border rounded p-3 mb-2">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input"
                                                type="radio"
                                                name="address_id"
                                                value="<?php echo (int)$address["id"]; ?>"
                                                id="address_<?php echo (int)$address["id"]; ?>"
                                            >

                                            <label
                                                class="form-check-label"
                                                for="address_<?php echo (int)$address["id"]; ?>"
                                            >

                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $address["recipient_name"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                    ?>
                                                </strong>

                                                <br>

                                                <?php
                                                echo htmlspecialchars(
                                                    $address["province"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                                ?>

                                                -

                                                <?php
                                                echo htmlspecialchars(
                                                    $address["city"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                                ?>

                                                <br>

                                                <?php
                                                echo htmlspecialchars(
                                                    $address["address"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                                ?>

                                                <br>

                                                <small>
                                                    تلفن:
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $address["phone"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                    ?>
                                                </small>

                                            </label>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>


                        <!-- =====================
                             آدرس جدید
                        ====================== -->

                        <div class="border rounded p-3">

                            <div class="form-check mb-3">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="new_address"
                                    value="1"
                                    id="newAddress"
                                >

                                <label
                                    class="form-check-label fw-bold"
                                    for="newAddress"
                                >

                                    استفاده از آدرس جدید

                                </label>

                            </div>


                            <div id="newAddressForm" style="display:none;">


                                <div class="mb-3">

                                    <label class="form-label">
                                        عنوان آدرس
                                    </label>

                                    <input
                                        type="text"
                                        name="title"
                                        class="form-control"
                                        placeholder="مثلاً منزل"
                                    >

                                </div>


                                <div class="mb-3">

                                    <label class="form-label">
                                        نام گیرنده
                                    </label>

                                    <input
                                        type="text"
                                        name="recipient_name"
                                        class="form-control"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $user["full_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>"
                                    >

                                </div>


                                <div class="mb-3">

                                    <label class="form-label">
                                        شماره تماس
                                    </label>

                                    <input
                                        type="text"
                                        name="phone"
                                        class="form-control"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $user["phone"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>"
                                    >

                                </div>


                                <div class="row">

                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            استان
                                        </label>

                                        <input
                                            type="text"
                                            name="province"
                                            class="form-control"
                                        >

                                    </div>


                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            شهر
                                        </label>

                                        <input
                                            type="text"
                                            name="city"
                                            class="form-control"
                                        >

                                    </div>

                                </div>


                                <div class="mb-3">

                                    <label class="form-label">
                                        کد پستی
                                    </label>

                                    <input
                                        type="text"
                                        name="postal_code"
                                        class="form-control"
                                    >

                                </div>


                                <div class="mb-3">

                                    <label class="form-label">
                                        آدرس کامل
                                    </label>

                                    <textarea
                                        name="address"
                                        class="form-control"
                                        rows="4"
                                    ></textarea>

                                </div>

                            </div>

                        </div>


                        <div class="mt-4">

                            <label class="form-label">
                                توضیحات سفارش
                            </label>

                            <textarea
                                name="notes"
                                class="form-control"
                                rows="3"
                                placeholder="توضیحات اختیاری..."
                            ></textarea>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================
                 خلاصه سفارش
            ========================== -->

            <div class="col-lg-5">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <h4 class="mb-4">
                            خلاصه سفارش
                        </h4>


                        <?php foreach ($_SESSION["cart"] as $item): ?>

                            <?php
                            $itemPrice =
                                (float)($item["price"] ?? 0);

                            $itemQuantity =
                                (int)($item["quantity"] ?? 1);

                            $itemTotal =
                                $itemPrice * $itemQuantity;
                            ?>


                            <div class="d-flex justify-content-between mb-3">

                                <div>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $item["product_name"] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </strong>

                                    <br>

                                    <small class="text-muted">

                                        <?php
                                        echo htmlspecialchars(
                                            $item["weight"] ?? "",
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

                                        ×

                                        <?php echo $itemQuantity; ?>

                                    </small>

                                </div>


                                <strong>

                                    <?php echo number_format($itemTotal); ?>

                                    تومان

                                </strong>

                            </div>

                        <?php endforeach; ?>


                        <hr>


                        <div class="d-flex justify-content-between mb-3">

                            <span>
                                مبلغ کالاها
                            </span>

                            <strong>

                                <?php echo number_format($cartTotal); ?>

                                تومان

                            </strong>

                        </div>


                        <div class="d-flex justify-content-between mb-3">

                            <span>
                                هزینه ارسال
                            </span>

                            <strong>
                                رایگان
                            </strong>

                        </div>


                        <hr>


                        <div class="d-flex justify-content-between mb-4">

                            <strong>
                                مبلغ نهایی
                            </strong>

                            <strong class="fs-5">

                                <?php echo number_format($finalAmount); ?>

                                تومان

                            </strong>

                        </div>


                        <div class="alert alert-light border">

                            <i class="bi bi-cash"></i>

                            پرداخت در محل

                            <div class="small text-muted mt-1">

                                فعلاً پرداخت در محل  فعال است.

                            </div>

                        </div>


                        <button
                            type="submit"
                            name="place_order"
                            value="1"
                            class="btn btn-danger w-100 btn-lg"
                        >

                            <i class="bi bi-check-circle"></i>

                            ثبت نهایی سفارش

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>


<script>

const newAddress = document.getElementById("newAddress");
const newAddressForm = document.getElementById("newAddressForm");

if (newAddress && newAddressForm) {

    newAddress.addEventListener("change", function () {

        newAddressForm.style.display =
            this.checked ? "block" : "none";

    });

}

</script>


<?php require_once __DIR__ . "/../includes/footer.php"; ?>
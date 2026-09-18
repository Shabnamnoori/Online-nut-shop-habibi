<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================================================
   ایجاد سبد خرید
========================================================= */

if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


/* =========================================================
   پیام‌ها
========================================================= */

$error = "";
$success = "";


/* =========================================================
   افزودن محصول به سبد خرید
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "add"
) {

    $productId = isset($_POST["product_id"]) && is_numeric($_POST["product_id"])
        ? (int) $_POST["product_id"]
        : 0;

    $weightId = isset($_POST["product_weight_id"]) && is_numeric($_POST["product_weight_id"])
        ? (int) $_POST["product_weight_id"]
        : 0;

    $quantity = isset($_POST["quantity"]) && is_numeric($_POST["quantity"])
        ? (int) $_POST["quantity"]
        : 1;


    if ($productId <= 0 || $weightId <= 0) {

        $error = "اطلاعات محصول صحیح نیست.";

    } elseif ($quantity < 1) {

        $error = "تعداد محصول صحیح نیست.";

    } else {


        /* =================================================
           دریافت محصول و وزن انتخاب‌شده
        ================================================= */

        $stmt = $pdo->prepare("
            SELECT
                p.id AS product_id,
                p.name AS product_name,
                p.image,
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

            WHERE
                p.id = :product_id
                AND pw.id = :weight_id
                AND p.status = 'active'
                AND pw.status = 'active'

            LIMIT 1
        ");

        $stmt->execute([
            ":product_id" => $productId,
            ":weight_id" => $weightId
        ]);

        $item = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$item) {

            $error = "محصول یا وزن انتخاب‌شده پیدا نشد.";

        } elseif ((int) $item["stock"] <= 0) {

            $error = "این محصول در وزن انتخاب‌شده موجود نیست.";

        } else {


            /* =================================================
               کلید یکتا برای محصول + وزن
            ================================================= */

            $cartKey =
                $productId . "_" . $weightId;


            /* =================================================
               تعداد فعلی داخل سبد
            ================================================= */

            $currentQuantity = 0;

            if (isset($_SESSION["cart"][$cartKey])) {

                $currentQuantity =
                    (int) $_SESSION["cart"][$cartKey]["quantity"];

            }


            $newQuantity =
                $currentQuantity + $quantity;


            /* =================================================
               بررسی موجودی
            ================================================= */

            if ($newQuantity > (int) $item["stock"]) {

                $error =
                    "تعداد انتخاب‌شده بیشتر از موجودی محصول است.";

            } else {


                /* =================================================
                   ذخیره در Session
                ================================================= */

                $_SESSION["cart"][$cartKey] = [

                    "product_id" =>
                        (int) $item["product_id"],

                    "product_name" =>
                        $item["product_name"],

                    "image" =>
                        $item["image"],

                    "weight_id" =>
                        (int) $item["weight_id"],

                    "weight" =>
                        (float) $item["weight"],

                    "unit" =>
                        $item["unit"],

                    "price" =>
                        (float) $item["price"],

                    "quantity" =>
                        $newQuantity

                ];


                $success =
                    "محصول با موفقیت به سبد خرید اضافه شد.";

            }

        }

    }

}


/* =========================================================
   تغییر تعداد محصول
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "update"
) {

    $cartKey = $_POST["cart_key"] ?? "";

    $quantity =
        isset($_POST["quantity"]) && is_numeric($_POST["quantity"])
            ? (int) $_POST["quantity"]
            : 0;


    if (
        $cartKey === "" ||
        !isset($_SESSION["cart"][$cartKey])
    ) {

        $error = "محصول موردنظر در سبد خرید وجود ندارد.";

    } elseif ($quantity < 1) {

        unset($_SESSION["cart"][$cartKey]);

        $success =
            "محصول از سبد خرید حذف شد.";

    } else {


        $weightId =
            (int) $_SESSION["cart"][$cartKey]["weight_id"];


        /* =================================================
           بررسی موجودی جدید
        ================================================= */

        $stockStmt = $pdo->prepare("
            SELECT stock, status
            FROM product_weights
            WHERE id = :id
            LIMIT 1
        ");

        $stockStmt->execute([
            ":id" => $weightId
        ]);

        $stockData =
            $stockStmt->fetch(PDO::FETCH_ASSOC);


        if (!$stockData || $stockData["status"] !== "active") {

            $error =
                "این وزن محصول دیگر موجود نیست.";

        } elseif ($quantity > (int) $stockData["stock"]) {

            $error =
                "تعداد انتخاب‌شده بیشتر از موجودی است.";

        } else {

            $_SESSION["cart"][$cartKey]["quantity"] =
                $quantity;

            $success =
                "تعداد محصول به‌روزرسانی شد.";

        }

    }

}


/* =========================================================
   حذف محصول
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "remove"
) {

    $cartKey = $_POST["cart_key"] ?? "";


    if (
        $cartKey !== "" &&
        isset($_SESSION["cart"][$cartKey])
    ) {

        unset($_SESSION["cart"][$cartKey]);

        $success =
            "محصول از سبد خرید حذف شد.";

    }

}


/* =========================================================
   خالی کردن سبد خرید
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "clear"
) {

    $_SESSION["cart"] = [];

    $success =
        "سبد خرید خالی شد.";

}


/* =========================================================
   محاسبه مجموع
========================================================= */

$cartTotal = 0;

$cartCount = 0;


foreach ($_SESSION["cart"] as $cartItem) {

    $quantity =
        (int) ($cartItem["quantity"] ?? 0);

    $price =
        (float) ($cartItem["price"] ?? 0);


    $cartTotal +=
        $price * $quantity;


    $cartCount +=
        $quantity;

}


require_once __DIR__ . "/../includes/header.php";

?>


<main>

    <div class="container py-5">


        <!-- =====================================================
             عنوان
        ====================================================== -->

        <div class="text-center mb-5">

            <h1>
                سبد خرید
            </h1>

            <p>
                محصولات انتخاب‌شده شما
            </p>

        </div>



        <!-- =====================================================
             پیام خطا
        ====================================================== -->

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



        <!-- =====================================================
             پیام موفقیت
        ====================================================== -->

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

        <?php endif; ?>



        <?php if (empty($_SESSION["cart"])): ?>


            <!-- =================================================
                 سبد خالی
            ================================================== -->

            <div class="text-center py-5">


                <i class="bi bi-bag-x fs-1"></i>


                <h3 class="mt-4">

                    سبد خرید شما خالی است.

                </h3>


                <p class="text-muted">

                    هنوز محصولی به سبد خرید اضافه نکرده‌اید.

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


            <div class="row g-4">


                <!-- =================================================
                     لیست محصولات
                ================================================== -->

                <div class="col-lg-8">


                    <?php foreach ($_SESSION["cart"] as $cartKey => $cartItem): ?>


                        <?php

                        $itemPrice =
                            (float) $cartItem["price"];

                        $itemQuantity =
                            (int) $cartItem["quantity"];

                        $itemTotal =
                            $itemPrice * $itemQuantity;

                        ?>


                        <div class="card mb-3">


                            <div class="card-body">


                                <div class="row align-items-center g-3">


                                    <!-- تصویر -->

                                    <div class="col-4 col-md-2">


                                        <?php if (!empty($cartItem["image"])): ?>

                                            <img
                                                src="/HABIBI/assets/images/products/<?php
                                                    echo htmlspecialchars(
                                                        $cartItem["image"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                ?>"
                                                class="img-fluid rounded"
                                                alt="<?php
                                                    echo htmlspecialchars(
                                                        $cartItem["product_name"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                ?>"
                                            >

                                        <?php else: ?>

                                            <div class="text-center">

                                                <i class="bi bi-image fs-1"></i>

                                            </div>

                                        <?php endif; ?>


                                    </div>



                                    <!-- اطلاعات -->

                                    <div class="col-8 col-md-4">


                                        <h5>

                                            <?php
                                            echo htmlspecialchars(
                                                $cartItem["product_name"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>

                                        </h5>


                                        <p class="mb-1">

                                            وزن:

                                            <?php

                                            echo rtrim(
                                                rtrim(
                                                    number_format(
                                                        (float) $cartItem["weight"],
                                                        2
                                                    ),
                                                    "0"
                                                ),
                                                "."
                                            );

                                            ?>

                                            <?php
                                            echo htmlspecialchars(
                                                $cartItem["unit"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>

                                        </p>


                                        <p class="mb-0">

                                            قیمت واحد:

                                            <strong>

                                                <?php
                                                echo number_format(
                                                    $itemPrice
                                                );
                                                ?>

                                                تومان

                                            </strong>

                                        </p>


                                    </div>



                                    <!-- تعداد -->

                                    <div class="col-7 col-md-3">


                                        <form
                                            method="POST"
                                            action=""
                                            class="d-flex gap-2"
                                        >


                                            <input
                                                type="hidden"
                                                name="action"
                                                value="update"
                                            >


                                            <input
                                                type="hidden"
                                                name="cart_key"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $cartKey,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                ?>"
                                            >


                                            <input
                                                type="number"
                                                name="quantity"
                                                class="form-control"
                                                value="<?php
                                                    echo $itemQuantity;
                                                ?>"
                                                min="1"
                                            >


                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger"
                                                title="به‌روزرسانی"
                                            >

                                                <i class="bi bi-arrow-repeat"></i>

                                            </button>


                                        </form>


                                    </div>



                                   <!-- مبلغ -->

                                    <div class="col-5 col-md-2 text-end">


                                        <strong>

                                            <?php
                                            echo number_format(
                                                $itemTotal
                                            );
                                            ?>

                                            تومان

                                        </strong>


                                    </div>



                                    <!-- حذف -->

                                    <div class="col-12">


                                        <form
                                            method="POST"
                                            action=""
                                        >


                                            <input
                                                type="hidden"
                                                name="action"
                                                value="remove"
                                            >


                                            <input
                                                type="hidden"
                                                name="cart_key"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $cartKey,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                            >

                                                <i class="bi bi-trash"></i>

                                                حذف از سبد

                                            </button>


                                        </form>


                                    </div>


                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                    <!-- =================================================
                         خالی کردن سبد
                    ================================================== -->

                    <form
                        method="POST"
                        action=""
                        class="mt-3"
                    >


                        <input
                            type="hidden"
                            name="action"
                            value="clear"
                        >


                        <button
                            type="submit"
                            class="btn btn-outline-secondary"
                        >

                            <i class="bi bi-trash"></i>

                            خالی کردن سبد خرید

                        </button>


                    </form>


                </div>



                <!-- =================================================
                     خلاصه سبد
                ================================================== -->

                <div class="col-lg-4">


                    <div class="card">


                        <div class="card-body">


                            <h4 class="mb-4">

                                خلاصه سبد خرید

                            </h4>


                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    تعداد کالا:
                                </span>

                                <strong><?php
                                    echo $cartCount;
                                    ?>

                                    عدد

                                </strong>

                            </div>


                            <hr>


                            <div class="d-flex justify-content-between mb-4">

                                <span>
                                    مبلغ کل:
                                </span>

                                <strong class="fs-5">

                                    <?php
                                    echo number_format(
                                        $cartTotal
                                    );
                                    ?>

                                    تومان

                                </strong>

                            </div>


                            <a
                                href="/HABIBI/pages/checkout.php"
                                class="btn btn-danger w-100"
                            >

                                <i class="bi bi-credit-card"></i>

                                ادامه و ثبت سفارش

                            </a>


                            <a
                                href="/HABIBI/pages/products.php"
                                class="btn btn-outline-secondary w-100 mt-2"
                            >

                                ادامه خرید

                            </a>


                        </div>


                    </div>


                </div>


            </div>


        <?php endif; ?>


    </div>

</main>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
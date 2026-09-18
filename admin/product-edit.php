<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================
   دسترسی ادمین
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    (int)($_SESSION["role_id"] ?? 0) !== 1
) {
    header("Location: /HABIBI/");
    exit;
}


/* =========================
   شناسه محصول
========================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    header("Location: /HABIBI/admin/products.php");
    exit;
}

$productId = (int)$_GET["id"];


/* =========================
   دریافت محصول
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$productId]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$product) {
    header("Location: /HABIBI/admin/products.php");
    exit;
}


/* =========================
   دسته‌بندی‌ها
========================= */

$stmt = $pdo->query("
    SELECT id, name
    FROM categories
    WHERE status = 'active'
    ORDER BY name ASC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   وزن‌ها
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM product_weights
    WHERE product_id = ?
    ORDER BY weight ASC
");

$stmt->execute([$productId]);

$weights = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   پیام خطا
========================= */

$error = "";


/* =========================
   ذخیره تغییرات
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $categoryId = (int)($_POST["category_id"] ?? 0);
    $description = trim($_POST["description"] ?? "");
    $price = (int)($_POST["price"] ?? 0);
    $stock = (int)($_POST["stock"] ?? 0);
    $status = $_POST["status"] ?? "active";
    $isFeatured = isset($_POST["is_featured"]) ? 1 : 0;


    if ($name === "" || $categoryId <= 0) {

        $error = "نام محصول و دسته‌بندی الزامی هستند.";

    } else {

        try {

            $pdo->beginTransaction();


            /* =========================
               تصویر جدید
            ========================== */

            $imageName = $product["image"];


            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] === UPLOAD_ERR_OK
            ) {

                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                $fileType = mime_content_type(
                    $_FILES["image"]["tmp_name"]
                );


                if (!isset($allowedTypes[$fileType])) {

                    throw new Exception(
                        "فرمت تصویر مجاز نیست."
                    );
                }


                $imageName =
                    time() . "-" .
                    preg_replace(
                        "/[^A-Za-z0-9\-_]/",
                        "",
                        pathinfo(
                            $_FILES["image"]["name"],
                            PATHINFO_FILENAME
                        )
                    ) .
                    "." .
                    $allowedTypes[$fileType];


                $uploadDir =
                    __DIR__ .
                    "/../assets/images/products/";


                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }


                if (
                    !move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $uploadDir . $imageName
                    )
                ) {

                    throw new Exception(
                        "آپلود تصویر انجام نشد."
                    );
                }
            }


            /* =========================
               بروزرسانی محصول
            ========================== */

            $stmt = $pdo->prepare("
                UPDATE products
                SET
                    category_id = ?,
                    name = ?,
                    description = ?,
                    image = ?,
                    price = ?,
                    stock = ?,
                    status = ?,
                    is_featured = ?
                WHERE id = ?
            ");


            $stmt->execute([
                $categoryId,
                $name,
                $description !== "" ? $description : null,
                $imageName,
                $price,
                $stock,
                $status,
                $isFeatured,
                $productId
            ]);


            /* =========================
               وزن‌های قبلی
            ========================== */

            $deleteWeights = $pdo->prepare("
                DELETE FROM product_weights
                WHERE product_id = ?
            ");

            $deleteWeights->execute([
                $productId
            ]);


            /* =========================
               وزن‌های جدید
            ========================== */

            $weightsInput =
                $_POST["weight"] ?? [];

            $unitsInput =
                $_POST["unit"] ?? [];

            $pricesInput =
                $_POST["weight_price"] ?? [];

            $stocksInput =
                $_POST["weight_stock"] ?? [];


            $weightStmt = $pdo->prepare("
                INSERT INTO product_weights
                (
                    product_id,
                    weight,
                    unit,
                    price,
                    stock,
                    status
                )
                VALUES
                (?, ?, ?, ?, ?, 'active')
            ");


            for (
                $i = 0;
                $i < count($weightsInput);
                $i++
            ) {

                $weight =
                    (float)($weightsInput[$i] ?? 0);

                $unit =
                    trim($unitsInput[$i] ?? "گرم");

                $weightPrice =
                    (int)($pricesInput[$i] ?? 0);

                $weightStock =
                    (int)($stocksInput[$i] ?? 0);


                if ($weight <= 0) {
                    continue;
                }


                $weightStmt->execute([
                    $productId,
                    $weight,
                    $unit,
                    $weightPrice,
                    $weightStock
                ]);
            }


            $pdo->commit();


            header(
                "Location: /HABIBI/admin/products.php?updated=1"
            );

            exit;


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                "خطا در ذخیره اطلاعات: " .
                $e->getMessage();
        }
    }


    /* =========================
       نگه داشتن اطلاعات فرم
    ========================== */

    $product["name"] = $name;
    $product["category_id"] = $categoryId;
    $product["description"] = $description;
    $product["price"] = $price;
    $product["stock"] = $stock;
    $product["status"] = $status;
    $product["is_featured"] = $isFeatured;
}

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
        ویرایش محصول | حبیبی
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


    <!-- عنوان -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                ویرایش محصول
            </h2>

            <p class="text-muted mb-0">

                <?php
                echo htmlspecialchars(
                    $product["name"],
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>

            </p>

        </div>


        <a
            href="/HABIBI/admin/products.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-right"></i>

            بازگشت

        </a>

    </div>


    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle"></i>

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
        enctype="multipart/form-data"
    >


        <div class="row g-4">


            <!-- =========================
                 اطلاعات محصول
            ========================== -->

            <div class="col-lg-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <h4 class="mb-4">
                            اطلاعات محصول
                        </h4>


                        <div class="mb-3">

                            <label class="form-label">
                                نام محصول
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($product["name"], ENT_QUOTES, "UTF-8"); ?>"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                دسته‌بندی
                            </label>

                            <select
                                name="category_id"
                                class="form-select"
                                required
                            >

                                <?php foreach ($categories as $category): ?>

                                    <option
                                        value="<?php echo (int)$category["id"]; ?>"
                                        <?php
                                        echo (
                                            (int)$product["category_id"] ===
                                            (int)$category["id"]
                                        )
                                            ? "selected"
                                            : "";
                                        ?>
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $category["name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                توضیحات
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="5"
                            ><?php echo htmlspecialchars($product["description"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                تصویر جدید
                            </label>

                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                        </div>


                        <?php if (!empty($product["image"])): ?>

                            <div class="mt-3">

                                <p class="text-muted mb-2">
                                    تصویر فعلی:
                                </p>

                                <img
                                    src="/HABIBI/assets/images/products/<?php echo htmlspecialchars($product["image"], ENT_QUOTES, "UTF-8"); ?>"
                                    alt=""
                                    style="
                                        width:120px;
                                        height:120px;
                                        object-fit:cover;
                                        border-radius:12px;
                                    "
                                >

                            </div>

                        <?php endif; ?>


                    </div>

                </div>

            </div>


            <!-- =========================
                 تنظیمات
            ========================== -->

            <div class="col-lg-4">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <h4 class="mb-4">
                            تنظیمات
                        </h4>


                        <div class="mb-3">

                            <label class="form-label">
                                قیمت پایه
                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                min="0"
                                value="<?php echo (int)$product["price"]; ?>"
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                موجودی پایه
                            </label>

                            <input
                                type="number"
                                name="stock"
                                class="form-control"
                                min="0"
                                value="<?php echo (int)$product["stock"]; ?>"
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                وضعیت
                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="active"
                                    <?php echo $product["status"] === "active" ? "selected" : ""; ?>
                                >
                                    فعال
                                </option>

                                <option
                                    value="inactive"
                                    <?php echo $product["status"] === "inactive" ? "selected" : ""; ?>
                                >
                                    غیرفعال
                                </option>

                            </select>

                        </div>


                        <div class="form-check">

                            <input
                                type="checkbox"
                                name="is_featured"
                                class="form-check-input"
                                id="featured"
                                <?php echo $product["is_featured"] ? "checked" : ""; ?>
                            >

                            <label
                                for="featured"
                                class="form-check-label"
                            >

                                محصول ویژه

                            </label>

                        </div>

                    </div>

                </div>

            </div>


        </div>


        <!-- =========================
             وزن‌ها
        ========================== -->

        <div class="card border-0 shadow-sm mt-4">

            <div class="card-body p-4">


                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h4 class="mb-0">
                        وزن‌ها و قیمت‌ها
                    </h4>


                    <button
                        type="button"
                        class="btn btn-outline-success"
                        id="addWeight"
                    >

                        <i class="bi bi-plus-lg"></i>

                        افزودن وزن

                    </button>

                </div>


                <div id="weightsContainer">


                    <?php if (!empty($weights)): ?>


                        <?php foreach ($weights as $weight): ?>


                            <div class="row g-3 weight-row mb-3">


                                <div class="col-md-3">

                                    <label class="form-label">
                                        وزن
                                    </label>

                                    <input
                                        type="number"
                                        name="weight[]"
                                        class="form-control"
                                        min="0"
                                        step="0.01"
                                        value="<?php echo htmlspecialchars($weight["weight"]); ?>"
                                    >

                                </div>


                                <div class="col-md-2">

                                    <label class="form-label">
                                        واحد
                                    </label>

                                    <input
                                        type="text"
                                        name="unit[]"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($weight["unit"], ENT_QUOTES, "UTF-8"); ?>"
                                    >

                                </div>


                                <div class="col-md-3">

                                    <label class="form-label">
                                        قیمت
                                    </label>

                                    <input
                                        type="number"
                                        name="weight_price[]"
                                        class="form-control"
                                        min="0"
                                        value="<?php echo (int)$weight["price"]; ?>"
                                    >

                                </div>


                                <div class="col-md-3">

                                    <label class="form-label">
                                        موجودی
                                    </label>

                                    <input
                                        type="number"
                                        name="weight_stock[]"
                                        class="form-control"
                                        min="0"
                                        value="<?php echo (int)$weight["stock"]; ?>"
                                    >

                                </div>


                                <div class="col-md-1 d-flex align-items-end">

                                    <button
                                        type="button"
                                        class="btn btn-outline-danger remove-weight"
                                    >

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div class="row g-3 weight-row mb-3">

                            <div class="col-md-3">

                                <label class="form-label">
                                    وزن
                                </label>

                                <input
                                    type="number"
                                    name="weight[]"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                >

                            </div>


                            <div class="col-md-2">

                                <label class="form-label">
                                    واحد
                                </label>

                                <input
                                    type="text"
                                    name="unit[]"
                                    class="form-control"
                                    value="گرم"
                                >

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    قیمت
                                </label>

                                <input
                                    type="number"
                                    name="weight_price[]"
                                    class="form-control"
                                    min="0"
                                >

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    موجودی
                                </label>

                                <input
                                    type="number"
                                    name="weight_stock[]"
                                    class="form-control"
                                    min="0"
                                >

                            </div>


                            <div class="col-md-1 d-flex align-items-end">

                                <button
                                    type="button"
                                    class="btn btn-outline-danger remove-weight"
                                >

                                    <i class="bi bi-trash"></i>

                                </button>

                            </div>

                        </div>


                    <?php endif; ?>


                </div>

            </div>

        </div>


        <!-- ذخیره -->

        <div class="mt-4 text-end">

            <button
                type="submit"
                class="btn btn-danger px-5"
            >

                <i class="bi bi-check-lg"></i>

                ذخیره تغییرات

            </button>

        </div>


    </form>


</div>


<script>

const container =
    document.getElementById("weightsContainer");

const addButton =
    document.getElementById("addWeight");


addButton.addEventListener(
    "click",
    function () {

        const row =
            document.createElement("div");

        row.className =
            "row g-3 weight-row mb-3";

        row.innerHTML = `

            <div class="col-md-3">

                <label class="form-label">
                    وزن
                </label>

                <input
                    type="number"
                    name="weight[]"
                    class="form-control"
                    min="0"
                    step="0.01"
                >

            </div>

            <div class="col-md-2">

                <label class="form-label">
                    واحد
                </label>

                <input
                    type="text"
                    name="unit[]"
                    class="form-control"
                    value="گرم"
                >

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    قیمت
                </label>

                <input
                    type="number"
                    name="weight_price[]"
                    class="form-control"
                    min="0"
                >

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    موجودی
                </label>

                <input
                    type="number"
                    name="weight_stock[]"
                    class="form-control"
                    min="0"
                >

            </div>

            <div class="col-md-1 d-flex align-items-end">

                <button
                    type="button"
                    class="btn btn-outline-danger remove-weight"
                >

                    <i class="bi bi-trash"></i>

                </button>

            </div>
        `;

        container.appendChild(row);

    }
);


container.addEventListener(
    "click",
    function (event) {

        const button =
            event.target.closest(".remove-weight");

        if (!button) {
            return;
        }

        const rows =
            container.querySelectorAll(".weight-row");

        if (rows.length <= 1) {
            return;
        }

        button.closest(".weight-row").remove();

    }
);

</script>


<script
    src="/HABIBI/assets/bootstrap/js/bootstrap.bundle.min.js"
></script>

</body>

</html>

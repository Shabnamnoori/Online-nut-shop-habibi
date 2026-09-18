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
   ثبت محصول
========================= */

$error = "";

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

            /* =========================
               ساخت Slug
            ========================== */

            $slug = "product-" . time() . "-" . rand(100, 999);


            /* =========================
               تصویر
            ========================== */

            $imageName = null;

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

                    $error = "فرمت تصویر مجاز نیست.";

                } else {

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


                    move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $uploadDir . $imageName
                    );
                }
            }


            if ($error === "") {

                /* =========================
                   ثبت محصول
                ========================== */

                $stmt = $pdo->prepare("
                    INSERT INTO products
                    (
                        category_id,
                        name,
                        slug,
                        description,
                        image,
                        price,
                        stock,
                        status,
                        is_featured
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $categoryId,
                    $name,
                    $slug,
                    $description !== ""
                        ? $description
                        : null,
                    $imageName,
                    $price,
                    $stock,
                    $status,
                    $isFeatured
                ]);


                $productId = (int)$pdo->lastInsertId();


                /* =========================
                   وزن‌ها
                ========================== */

                $weights =
                    $_POST["weight"] ?? [];

                $units =
                    $_POST["unit"] ?? [];

                $weightPrices =
                    $_POST["weight_price"] ?? [];

                $weightStocks =
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
                    $i < count($weights);
                    $i++
                ) {

                    $weight =
                        (float)($weights[$i] ?? 0);

                    $unit =
                        trim($units[$i] ?? "گرم");

                    $weightPrice =
                        (int)($weightPrices[$i] ?? 0);

                    $weightStock =
                        (int)($weightStocks[$i] ?? 0);


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


                header(
                    "Location: /HABIBI/admin/products.php?created=1"
                );

                exit;
            }

        } catch (PDOException $e) {

            $error =
                "خطا در ثبت محصول: " .
                $e->getMessage();
        }
    }
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
        افزودن محصول | حبیبی
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
         عنوان
    ========================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                افزودن محصول
            </h2>

            <p class="text-muted mb-0">
                ثبت محصول جدید در فروشگاه
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

            <?php echo htmlspecialchars($error); ?>

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


                        <!-- نام -->

                        <div class="mb-3">

                            <label class="form-label">
                                نام محصول
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- دسته -->

                        <div class="mb-3">

                            <label class="form-label">
                                دسته‌بندی
                            </label>

                            <select
                                name="category_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    انتخاب دسته‌بندی
                                </option>


                                <?php foreach ($categories as $category): ?>

                                    <option
                                        value="<?php echo (int)$category["id"]; ?>"
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


                        <!-- توضیحات -->

                        <div class="mb-3">

                            <label class="form-label">
                                توضیحات
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="5"
                            ></textarea>

                        </div>


                        <!-- تصویر -->

                        <div class="mb-3">

                            <label class="form-label">
                                تصویر محصول
                            </label>

                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <small class="text-muted">
                                فرمت‌های JPG، PNG و WEBP
                            </small>

                        </div>


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


                        <!-- قیمت -->

                        <div class="mb-3">

                            <label class="form-label">
                                قیمت پایه
                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                min="0"
                                value="0"
                            >

                            <small class="text-muted">
                                تومان
                            </small>

                        </div>


                        <!-- موجودی -->

                        <div class="mb-3">

                            <label class="form-label">
                                موجودی پایه
                            </label>

                            <input
                                type="number"
                                name="stock"
                                class="form-control"
                                min="0"
                                value="0"
                            >

                        </div>


                        <!-- وضعیت -->

                        <div class="mb-3">

                            <label class="form-label">
                                وضعیت
                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option value="active">
                                    فعال
                                </option>

                                <option value="inactive">
                                    غیرفعال
                                </option>

                            </select>

                        </div>


                        <!-- ویژه -->

                        <div class="form-check mb-4">

                            <input
                                type="checkbox"
                                name="is_featured"
                                class="form-check-input"
                                id="featured"
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


                    <!-- وزن اول -->

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
                                placeholder="250"
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
                                placeholder="230000"
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
                                placeholder="30"
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


                </div>


            </div>

        </div>


        <!-- =========================
             ثبت
        ========================== -->

        <div class="mt-4 text-end">

            <button
                type="submit"
                class="btn btn-danger px-5"
            >

                <i class="bi bi-check-lg"></i>

                ثبت محصول

            </button>

        </div>


    </form>


</div>


<script>

const container =
    document.getElementById("weightsContainer");

const addButton =
    document.getElementById("addWeight");


function createWeightRow() {

    const row =
        document.createElement("div");

    row.className =
        "row g-3 weight-row mb-3";

    row.innerHTML = `

        <div class="col-md-3">

            <input
                type="number"
                name="weight[]"
                class="form-control"
                min="0"
                step="0.01"
                placeholder="250"
            >

        </div>

        <div class="col-md-2">

            <input
                type="text"
                name="unit[]"
                class="form-control"
                value="گرم"
            >

        </div>

        <div class="col-md-3">

            <input
                type="number"
                name="weight_price[]"
                class="form-control"
                min="0"
                placeholder="230000"
            >

        </div>

        <div class="col-md-3">

            <input
                type="number"
                name="weight_stock[]"
                class="form-control"
                min="0"
                placeholder="30"
            >

        </div>

        <div class="col-md-1">

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


addButton.addEventListener(
    "click",
    createWeightRow
);


container.addEventListener(
    "click",
    function(event) {

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
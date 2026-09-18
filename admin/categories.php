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
   مسیر تصاویر دسته‌بندی
========================= */

$imageDirectory = __DIR__ . "/../assets/images/categories/";
$imageUrl = "/HABIBI/assets/images/categories/";


/* اگر پوشه وجود نداشت، بساز */

if (!is_dir($imageDirectory)) {

    mkdir(
        $imageDirectory,
        0755,
        true
    );
}


/* =========================
   حذف دسته‌بندی
========================= */

if (
    isset($_GET["delete"]) &&
    is_numeric($_GET["delete"])
) {

    $id = (int)$_GET["delete"];

    try {

        /* بررسی وجود محصول */

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM products
            WHERE category_id = ?
        ");

        $stmt->execute([$id]);

        $productCount = (int)$stmt->fetchColumn();


        if ($productCount > 0) {

            $error =
                "این دسته‌بندی دارای محصول است و قابل حذف نیست.";

        } else {

            /* دریافت عکس قبل از حذف */

            $stmt = $pdo->prepare("
                SELECT image
                FROM categories
                WHERE id = ?
            ");

            $stmt->execute([$id]);

            $categoryImage = $stmt->fetchColumn();


            /* حذف از دیتابیس */

            $stmt = $pdo->prepare("
                DELETE FROM categories
                WHERE id = ?
            ");

            $stmt->execute([$id]);


            /* حذف فایل عکس */

            if (
                $categoryImage &&
                is_string($categoryImage)
            ) {

                $imagePath =
                    $imageDirectory . basename($categoryImage);

                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }


            header(
                "Location: /HABIBI/admin/categories.php?deleted=1"
            );

            exit;
        }

    } catch (PDOException $e) {

        $error =
            "خطا در حذف دسته‌بندی.";
    }
}


/* =========================
   پیام حذف
========================= */

if (isset($_GET["deleted"])) {

    $message =
        "دسته‌بندی با موفقیت حذف شد.";
}


/* =========================================================
   افزودن دسته‌بندی
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "add"
) {

    $name =
        trim($_POST["name"] ?? "");

    $slug =
        trim($_POST["slug"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $status =
        $_POST["status"] ?? "active";


    /* =========================
       بررسی نام
    ========================= */

    if ($name === "") {

        $error =
            "نام دسته‌بندی الزامی است.";

    } else {


        /* =========================
           ساخت slug
        ========================= */

        if ($slug === "") {

            $slug =
                "category-" .
                time() .
                "-" .
                rand(100, 999);
        }


        /* =========================
           آپلود تصویر
        ========================= */

        $imageName = null;


        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES["image"]["error"] !== UPLOAD_ERR_OK
            ) {

                $error =
                    "خطا در آپلود تصویر.";

            } else {

                $tmpName =
                    $_FILES["image"]["tmp_name"];

                $originalName =
                    $_FILES["image"]["name"];


                /* بررسی نوع فایل */

                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png"  => "png",
                    "image/webp" => "webp"
                ];


                $mimeType =
                    mime_content_type($tmpName);


                if (
                    !isset($allowedTypes[$mimeType])
                ) {

                    $error =
                        "فرمت تصویر مجاز نیست. فقط JPG، PNG و WEBP مجاز هستند.";

                } else {


                    /* بررسی حجم */

                    if ($_FILES["image"]["size"] > 5 * 1024 * 1024) {

                        $error =
                            "حجم تصویر نباید بیشتر از 5 مگابایت باشد.";

                    } else {


                        /* نام جدید فایل */

                        $extension =
                            $allowedTypes[$mimeType];


                        $imageName =
                            "category_" .
                            time() .
                            "_" .
                            bin2hex(random_bytes(5)) .
                            "." .
                            $extension;


                        $destination =
                            $imageDirectory . $imageName;


                        if (
                            !move_uploaded_file(
                                $tmpName,
                                $destination
                            )
                        ) {

                            $error =
                                "ذخیره تصویر با خطا مواجه شد.";

                            $imageName = null;
                        }

                    }
                }
            }
        }


        /* =========================
           ثبت در دیتابیس
        ========================= */

        if ($error === "") {

            try {

                $stmt = $pdo->prepare("
                    INSERT INTO categories
                    (
                        name,
                        slug,
                        description,
                        image,
                        status
                    )
                    VALUES
                    (?, ?, ?, ?, ?)
                ");


                $stmt->execute([

                    $name,

                    $slug,

                    $description !== ""
                        ? $description
                        : null,

                    $imageName,

                    $status

                ]);


                header(
                    "Location: /HABIBI/admin/categories.php?added=1"
                );

                exit;


            } catch (PDOException $e) {

                /* اگر ثبت دیتابیس شکست خورد،
                   عکس آپلودشده را هم حذف کن */

                if (
                    $imageName &&
                    is_file($imageDirectory . $imageName)
                ) {

                    unlink(
                        $imageDirectory . $imageName
                    );
                }


                $error =
                    "خطا در ثبت دسته‌بندی. احتمالاً slug تکراری است.";
            }
        }
    }
}


/* =========================
   پیام افزودن
========================= */

if (isset($_GET["added"])) {

    $message =
        "دسته‌بندی با موفقیت اضافه شد.";
}


/* =========================================================
   ویرایش دسته‌بندی
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "edit"
) {

    $id =
        (int)($_POST["id"] ?? 0);

    $name =
        trim($_POST["name"] ?? "");

    $slug =
        trim($_POST["slug"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $status =
        $_POST["status"] ?? "active";


    if (
        $id <= 0 ||
        $name === ""
    ) {

        $error =
            "اطلاعات دسته‌بندی صحیح نیست.";

    } else {


        try {

            /* =========================
               دریافت عکس قبلی
            ========================= */

            $stmt = $pdo->prepare("
                SELECT image
                FROM categories
                WHERE id = ?
            ");

            $stmt->execute([$id]);

            $oldImage =
                $stmt->fetchColumn();


            /* =========================
               عکس جدید
            ========================= */

            $newImage =
                $oldImage;


            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {

                if (
                    $_FILES["image"]["error"] !== UPLOAD_ERR_OK
                ) {

                    throw new Exception(
                        "خطا در آپلود تصویر."
                    );
                }


                $tmpName =
                    $_FILES["image"]["tmp_name"];


                /* بررسی نوع */

                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png"  => "png",
                    "image/webp" => "webp"
                ];


                $mimeType =
                    mime_content_type($tmpName);


                if (
                    !isset($allowedTypes[$mimeType])
                ) {

                    throw new Exception(
                        "فرمت تصویر مجاز نیست. فقط JPG، PNG و WEBP مجاز هستند."
                    );
                }


                /* بررسی حجم */

                if (
                    $_FILES["image"]["size"] >
                    5 * 1024 * 1024
                ) {

                    throw new Exception(
                        "حجم تصویر نباید بیشتر از 5 مگابایت باشد."
                    );
                }


                /* نام جدید */

                $extension =
                    $allowedTypes[$mimeType];


                $newImage =
                    "category_" .
                    time() .
                    "_" .
                    bin2hex(random_bytes(5)) .
                    "." .
                    $extension;


                $destination =
                    $imageDirectory . $newImage;


                if (
                    !move_uploaded_file(
                        $tmpName,
                        $destination
                    )
                ) {

                    throw new Exception(
                        "ذخیره تصویر با خطا مواجه شد."
                    );
                }
            }


            /* =========================
               بروزرسانی دیتابیس
            ========================= */

            $stmt = $pdo->prepare("
                UPDATE categories

                SET
                    name = ?,
                    slug = ?,
                    description = ?,
                    image = ?,
                    status = ?

                WHERE id = ?
            ");


            $stmt->execute([

                $name,

                $slug,

                $description !== ""
                    ? $description
                    : null,

                $newImage,

                $status,

                $id

            ]);


            /* =========================
               حذف عکس قدیمی
               فقط اگر عکس جدید آپلود شده
            ========================= */

            if (
                $newImage !== $oldImage &&
                $oldImage
            ) {

                $oldImagePath =
                    $imageDirectory .
                    basename($oldImage);


                if (
                    is_file($oldImagePath)
                ) {

                    unlink($oldImagePath);
                }
            }


            header(
                "Location: /HABIBI/admin/categories.php?updated=1"
            );

            exit;


        } catch (Exception $e) {

            /* اگر عکس جدید آپلود شده ولی ثبت نشد */

            if (
                isset($newImage) &&
                $newImage &&
                isset($oldImage) &&
                $newImage !== $oldImage &&
                is_file($imageDirectory . $newImage)
            ) {

                unlink(
                    $imageDirectory . $newImage
                );
            }


            $error =
                $e->getMessage();
        }
    }
}


/* =========================
   پیام ویرایش
========================= */

if (isset($_GET["updated"])) {

    $message =
        "دسته‌بندی با موفقیت ویرایش شد.";
}


/* =========================
   دریافت دسته‌بندی‌ها
========================= */

$stmt = $pdo->query("
    SELECT
        c.id,
        c.name,
        c.slug,
        c.description,
        c.image,
        c.status,
        c.created_at,

        (
            SELECT COUNT(*)
            FROM products p
            WHERE p.category_id = c.id
        ) AS product_count

    FROM categories c

    ORDER BY c.id DESC
");

$categories =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        مدیریت دسته‌بندی‌ها | حبیبی
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
                مدیریت دسته‌بندی‌ها
            </h2>

            <p class="text-muted mb-0">
                مدیریت دسته‌بندی محصولات فروشگاه
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
         پیام
    ========================== -->

    <?php if ($message !== ""): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle"></i>

            <?php
            echo htmlspecialchars(
                $message,
                ENT_QUOTES,
                "UTF-8"
            );
            ?>

        </div>

    <?php endif; ?>


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


    <div class="row g-4">


        <!-- =========================
             افزودن دسته‌بندی
        ========================== -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h4 class="mb-4">

                        <i class="bi bi-plus-circle"></i>

                        افزودن دسته‌بندی

                    </h4>


                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="add"
                        >


                        <!-- نام -->

                        <div class="mb-3">

                            <label class="form-label">
                                نام دسته‌بندی
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- Slug -->

                        <div class="mb-3">

                            <label class="form-label">
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                class="form-control"
                                placeholder="example-category"
                            >

                            <small class="text-muted">

                                در صورت خالی بودن،
                                خودکار ساخته می‌شود.

                            </small>

                        </div>


                        <!-- تصویر -->

                        <div class="mb-3">

                            <label class="form-label">
                                تصویر دسته‌بندی
                            </label>

                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <small class="text-muted">

                                فرمت‌های مجاز:
                                JPG، PNG، WEBP
                                — حداکثر 5 مگابایت

                            </small>

                        </div>


                        <!-- توضیحات -->

                        <div class="mb-3">

                            <label class="form-label">
                                توضیحات
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                            ></textarea>

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


                        <button
                            type="submit"
                            class="btn btn-danger w-100"
                        >

                            <i class="bi bi-plus-lg"></i>

                            افزودن دسته‌بندی

                        </button>


                    </form>

                </div>

            </div>

        </div>


        <!-- =========================
             لیست
        ========================== -->

        
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-0">


                    <?php if (empty($categories)): ?>

                        <div class="text-center py-5">

                            <i
                                class="bi bi-grid text-muted"
                                style="font-size:60px;"
                            ></i>

                            <p class="text-muted mt-3">
                                دسته‌بندی‌ای وجود ندارد.
                            </p>

                        </div>

                    <?php else: ?>


                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead>

                                    <tr>

                                        <th>
                                            تصویر
                                        </th>

                                        <th>
                                            نام
                                        </th>

                                        <th>
                                            Slug
                                        </th>

                                        <th>
                                            محصولات
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


                                <?php foreach ($categories as $category): ?>


                                    <tr>


                                        <!-- تصویر -->

                                        <td>

                                            <?php if (!empty($category["image"])): ?>

                                                <img
                                                    src="<?php echo $imageUrl . htmlspecialchars($category["image"], ENT_QUOTES, "UTF-8"); ?>"
                                                    alt="<?php echo htmlspecialchars($category["name"], ENT_QUOTES, "UTF-8"); ?>"
                                                    width="70"
                                                    height="52"
                                                    style="object-fit:cover;border-radius:8px;"
                                                >

                                            <?php else: ?>

                                                <div
                                                    class="bg-light d-flex align-items-center justify-content-center"
                                                    style="width:70px;height:52px;border-radius:8px;"
                                                >

                                                    <i class="bi bi-image text-muted"></i>

                                                </div>

                                            <?php endif; ?>

                                        </td>


                                        <!-- نام -->

                                        <td>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $category["name"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                                ?>

                                            </strong>

                                        </td>


                                        <!-- Slug -->

                                        <td>

                                            <small class="text-muted">

                                                <?php
                                                echo htmlspecialchars(
                                                    $category["slug"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                                ?>

                                            </small>

                                        </td>


                                        <!-- محصولات -->

                                        <td>

                                            <span class="badge bg-light text-dark">

                                                <?php
                                                echo (int)$category["product_count"];
                                                ?>

                                            </span>

                                        </td>


                                        <!-- وضعیت -->

                                        <td>

                                            <?php if ($category["status"] === "active"): ?>

                                                <span class="badge bg-success">
                                                    فعال
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">
                                                    غیرفعال
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- عملیات -->

                                        <td>

                                            <div class="d-flex gap-1">


                                                <!-- ویرایش -->

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal<?php echo (int)$category["id"]; ?>"
                                                    title="ویرایش"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </button>


                                                <!-- حذف -->

                                                <?php if ((int)$category["product_count"] === 0): ?>

                                                    <a
                                                        href="/HABIBI/admin/categories.php?delete=<?php echo (int)$category["id"]; ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟');"
                                                        title="حذف"
                                                    >

                                                        <i class="bi bi-trash"></i>

                                                    </a>

                                                <?php else: ?>

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        disabled
                                                        title="این دسته دارای محصول است"
                                                    >

                                                        <i class="bi bi-lock"></i>

                                                    </button>

                                                <?php endif; ?>


                                            </div>

                                        </td>


                                    </tr>


                                    <!-- =========================
                                         Modal ویرایش
                                    ========================== -->

                                    <div
                                        class="modal fade"
                                        id="editModal<?php echo (int)$category["id"]; ?>"
                                        tabindex="-1"
                                    >

                                        <div class="modal-dialog">

                                            <div class="modal-content">


                                                <form
                                                    method="POST"
                                                    enctype="multipart/form-data"
                                                >


                                                    <div class="modal-header">

                                                        <h5 class="modal-title">

                                                            ویرایش دسته‌بندی

                                                        </h5>


                                                        <button
                                                            type="button"
                                                            class="btn-close"
                                                            data-bs-dismiss="modal"
                                                        ></button>

                                                    </div>


                                                    <div class="modal-body">


                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="edit"
                                                        >


                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?php echo (int)$category["id"]; ?>"
                                                        >


                                                        <!-- نام -->

                                                        <div class="mb-3">

                                                            <label class="form-label">
                                                                نام دسته‌بندی
                                                            </label>

                                                            <input
                                                                type="text"
                                                                name="name"
                                                                class="form-control"
                                                                value="<?php echo htmlspecialchars($category["name"], ENT_QUOTES, "UTF-8"); ?>"
                                                                required
                                                            >

                                                        </div>


                                                        <!-- Slug -->

                                                        <div class="mb-3">

                                                            <label class="form-label">
                                                                Slug
                                                            </label>

                                                            <input
                                                                type="text"
                                                                name="slug"
                                                                class="form-control"
                                                                value="<?php echo htmlspecialchars($category["slug"], ENT_QUOTES, "UTF-8"); ?>"
                                                            >

                                                        </div>


                                                        <!-- تصویر فعلی -->

                                                        <?php if (!empty($category["image"])): ?>

                                                            <div class="mb-3">

                                                                <label class="form-label">
                                                                    تصویر فعلی
                                                                </label>

                                                                <div>

                                                                    <img
                                                                        src="<?php echo $imageUrl . htmlspecialchars($category["image"], ENT_QUOTES, "UTF-8"); ?>"
                                                                        alt=""
                                                                        width="120"
                                                                        height="90"
                                                                        style="object-fit:cover;border-radius:10px;"
                                                                    >

                                                                </div>

                                                            </div>

                                                        <?php endif; ?>


                                                        <!-- تصویر جدید -->

                                                        <div class="mb-3">

                                                            <label class="form-label">
                                                                تغییر تصویر
                                                            </label>

                                                            <input
                                                                type="file"
                                                                name="image"
                                                                class="form-control"
                                                                accept="image/jpeg,image/png,image/webp"
                                                            >

                                                            <small class="text-muted">

                                                                اگر تصویر جدید انتخاب نکنید،
                                                                تصویر فعلی حفظ می‌شود.

                                                            </small>

                                                        </div>


                                                        <!-- توضیحات -->

                                                        <div class="mb-3">

                                                            <label class="form-label">
                                                                توضیحات
                                                            </label>

                                                            <textarea
                                                                name="description"
                                                                class="form-control"
                                                                rows="4"
                                                            ><?php echo htmlspecialchars($category["description"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>

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

                                                                <option
                                                                    value="active"
                                                                    <?php echo $category["status"] === "active" ? "selected" : ""; ?>
                                                                >
                                                                    فعال
                                                                </option>

                                                                <option
                                                                    value="inactive"
                                                                    <?php echo $category["status"] === "inactive" ? "selected" : ""; ?>
                                                                >
                                                                    غیرفعال
                                                                </option>

                                                            </select>

                                                        </div>


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
                                                            class="btn btn-primary"
                                                        >

                                                            <i class="bi bi-check-lg"></i>

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


    </div>


</div>


<script
    src="/HABIBI/assets/bootstrap/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
 
        
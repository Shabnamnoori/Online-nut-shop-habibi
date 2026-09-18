<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================================================
   دسترسی ادمین
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    (int)($_SESSION["role_id"] ?? 0) !== 1
) {
    header("Location: /HABIBI/");
    exit;
}


/* =========================================================
   حذف محصول
========================================================= */

if (
    isset($_GET["delete"]) &&
    is_numeric($_GET["delete"])
) {

    $productId = (int) $_GET["delete"];

    try {

        /* -------------------------------------------------
           1. ابتدا اطلاعات محصول و نام عکس را می‌گیریم
        ------------------------------------------------- */

        $stmt = $pdo->prepare("
            SELECT
                id,
                image
            FROM products
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);


        /* -------------------------------------------------
           اگر محصول وجود نداشت
        ------------------------------------------------- */

        if (!$product) {

            header(
                "Location: /HABIBI/admin/products.php?error=notfound"
            );

            exit;
        }


        /* -------------------------------------------------
           2. نام عکس را ذخیره می‌کنیم
        ------------------------------------------------- */

        $imageName = trim($product["image"] ?? "");


        /* -------------------------------------------------
           3. حذف محصول از دیتابیس
        ------------------------------------------------- */

        $stmt = $pdo->prepare("
            DELETE FROM products
            WHERE id = ?
        ");

        $stmt->execute([$productId]);


        /* -------------------------------------------------
           4. اگر محصول با موفقیت حذف شد،
              عکس آن را از پوشه حذف می‌کنیم
        ------------------------------------------------- */

        if ($stmt->rowCount() > 0 && $imageName !== "") {


            /*
             * مسیر واقعی پوشه تصاویر محصولات
             *
             * __DIR__ در این فایل برابر است با:
             * C:\xampp\htdocs\HABIBI\admin
             *
             * پس با ../ می‌رویم به پوشه HABIBI
             */

            $imageDirectory =
                __DIR__ . "/../assets/images/products/";


            /*
             * فقط نام فایل را نگه می‌داریم
             *
             * این کار برای جلوگیری از دستکاری مسیر
             * بسیار مهم است.
             */

            $safeImageName =
                basename($imageName);


            /*
             * مسیر کامل فایل عکس
             */

            $imagePath =
                $imageDirectory . $safeImageName;


            /*
             * اگر فایل واقعاً وجود داشت،
             * آن را حذف می‌کنیم.
             */

            if (
                is_file($imagePath)
            ) {

                @unlink($imagePath);

            }

        }


        /* -------------------------------------------------
           5. بازگشت به صفحه محصولات
        ------------------------------------------------- */

        header(
            "Location: /HABIBI/admin/products.php?deleted=1"
        );

        exit;


    } catch (PDOException $e) {

        /*
         * اگر خطایی در دیتابیس اتفاق افتاد
         */

        header(
            "Location: /HABIBI/admin/products.php?error=delete"
        );

        exit;
    }
}


/* =========================================================
   پیام‌ها
========================================================= */

$message = "";
$messageType = "success";


if (isset($_GET["deleted"])) {

    $message =
        "محصول و تصویر آن با موفقیت حذف شدند.";

    $messageType = "success";
}


if (isset($_GET["error"])) {

    if ($_GET["error"] === "delete") {

        $message =
            "حذف محصول انجام نشد. لطفاً دوباره تلاش کنید.";

        $messageType = "danger";

    } elseif ($_GET["error"] === "notfound") {

        $message =
            "محصول موردنظر پیدا نشد.";

        $messageType = "warning";
    }
}


/* =========================================================
   دریافت محصولات
========================================================= */

$stmt = $pdo->query("
    SELECT
        p.id,
        p.name,
        p.image,
        p.price,
        p.stock,
        p.status,
        p.is_featured,
        c.name AS category_name

    FROM products p

    INNER JOIN categories c
        ON c.id = p.category_id

    ORDER BY p.id DESC
");

$products =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>

<html
    lang="fa"
    dir="rtl"
>


<head>


    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>
        مدیریت محصولات | حبیبی
    </title>


    <!-- Bootstrap -->

    <link
        rel="stylesheet"
        href="/HABIBI/assets/bootstrap/css/bootstrap.rtl.min.css"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="/HABIBI/assets/bootstrap-icons/bootstrap-icons.css"
    >


</head>


<body>


<div class="container py-5">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >


        <div>

            <h2 class="mb-2">

                مدیریت محصولات

            </h2>


            <p class="text-muted mb-0">

                مدیریت محصولات و موجودی فروشگاه

            </p>

        </div>


        <div class="d-flex gap-2">


            <!-- داشبورد -->

            <a
                href="/HABIBI/admin/index.php"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-right"></i>

                داشبورد

            </a>


            <!-- افزودن محصول -->

            <a
                href="/HABIBI/admin/product-create.php"
                class="btn btn-danger"
            >

                <i class="bi bi-plus-lg"></i>

                افزودن محصول

            </a>


        </div>


    </div>



    <!-- =====================================================
         پیام
    ====================================================== -->

    <?php if ($message !== ""): ?>


        <div
            class="alert alert-<?php echo $messageType; ?>"
        >


            <?php if ($messageType === "success"): ?>

                <i class="bi bi-check-circle"></i>

            <?php elseif ($messageType === "danger"): ?>

                <i class="bi bi-exclamation-triangle"></i>

            <?php else: ?>

                <i class="bi bi-info-circle"></i>

            <?php endif; ?>


            <?php

            echo htmlspecialchars(
                $message,
                ENT_QUOTES,
                "UTF-8"
            );

            ?>


        </div>


    <?php endif; ?>



    <!-- =====================================================
         محصولات
    ====================================================== -->

    <div class="card border-0 shadow-sm">


        <div class="card-body p-0">


            <?php if (empty($products)): ?>


                <!-- =========================================
                     بدون محصول
                ========================================== -->

                <div class="text-center py-5">


                    <i
                        class="bi bi-box-seam text-muted"
                        style="font-size:60px;"
                    ></i>


                    <p class="mt-3 text-muted">

                        هنوز محصولی ثبت نشده است.

                    </p>


                    <a
                        href="/HABIBI/admin/product-create.php"
                        class="btn btn-danger"
                    >

                        <i class="bi bi-plus-lg"></i>

                        افزودن اولین محصول

                    </a>


                </div>


            <?php else: ?>


                <!-- =========================================
                     جدول محصولات
                ========================================== -->

                <div class="table-responsive">


                    <table
                        class="table table-hover align-middle mb-0"
                    >


                        <thead>


                            <tr>


                                <th>
                                    تصویر
                                </th>


                                <th>
                                    محصول
                                </th>


                                <th>
                                    دسته‌بندی
                                </th>


                                <th>
                                    قیمت پایه
                                </th>


                                <th>
                                    موجودی
                                </th>


                                <th>
                                    وضعیت
                                </th>


                                <th>
                                    ویژه
                                </th>


                                <th>
                                    عملیات
                                </th>


                            </tr>


                        </thead>


                        <tbody>


                        <?php foreach ($products as $product): ?>


                            <tr>


                                <!-- =================================
                                     تصویر
                                ================================== -->

                                <td>


                                    <?php if (!empty($product["image"])): ?>


                                        <img
                                            src="/HABIBI/assets/images/products/<?php
                                                echo htmlspecialchars(
                                                    basename($product["image"]),
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                            ?>"
                                            alt="<?php
                                                echo htmlspecialchars(
                                                    $product["name"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                            ?>"
                                            width="60"
                                            height="60"
                                            style="
                                                object-fit:cover;
                                                border-radius:8px;
                                            "
                                        >


                                    <?php else: ?>


                                        <div
                                            class="bg-light d-flex align-items-center justify-content-center"
                                            style="
                                                width:60px;
                                                height:60px;
                                                border-radius:8px;
                                            "
                                        >


                                            <i
                                                class="bi bi-image text-muted"
                                            ></i>


                                        </div>


                                    <?php endif; ?>


                                </td>



                                <!-- =================================
                                     نام محصول
                                ================================== -->

                                <td>


                                    <strong>


                                        <?php

                                        echo htmlspecialchars(
                                            $product["name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>


                                    </strong>


                                </td>



                                <!-- =================================
                                     دسته‌بندی
                                ================================== -->

                                <td>


                                    <?php

                                    echo htmlspecialchars(
                                        $product["category_name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>


                                </td>



                                <!-- =================================
                                     قیمت
                                ================================== -->

                                <td>


                                    <?php

                                    echo number_format(
                                        (float)$product["price"]
                                    );

                                    ?>


                                    تومان


                                </td>



                                <!-- =================================
                                     موجودی
                                ================================== -->

                                <td>


                                    <?php

                                    echo (int)$product["stock"];

                                    ?>


                                </td>



                                <!-- =================================
                                     وضعیت
                                ================================== -->

                                <td>


                                    <?php if (
                                        $product["status"] === "active"
                                    ): ?>


                                        <span
                                            class="badge bg-success"
                                        >

                                            فعال

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="badge bg-secondary"
                                        >

                                            غیرفعال

                                        </span>


                                    <?php endif; ?>


                                </td>



                                <!-- =================================
                                     محصول ویژه
                                ================================== -->

                                <td>


                                    <?php if (
                                        $product["is_featured"]
                                    ): ?>


                                        <i
                                            class="bi bi-star-fill text-warning"
                                            title="محصول ویژه"
                                        ></i>


                                    <?php else: ?>


                                        <i
                                            class="bi bi-star text-muted"
                                        ></i>


                                    <?php endif; ?>


                                </td>



                                <!-- =================================
                                     عملیات
                                ================================== -->

                                <td>


                                    <div
                                        class="d-flex gap-1"
                                    >


                                        <!-- ویرایش -->

                                        <a
                                            href="/HABIBI/admin/product-edit.php?id=<?php echo (int)$product["id"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="ویرایش"
                                        >

                                            <i
                                                class="bi bi-pencil"
                                            ></i>

                                        </a>



                                        <!-- مدیریت وزن‌ها -->

                                        <a
                                            href="/HABIBI/admin/product-weights.php?id=<?php echo (int)$product["id"]; ?>"
                                            class="btn btn-sm btn-outline-success"
                                            title="مدیریت وزن‌ها"
                                        >

                                            <i
                                                class="bi bi-boxes"
                                            ></i>

                                        </a>



                                        <!-- حذف -->

                                        <a
                                            href="/HABIBI/admin/products.php?delete=<?php echo (int)$product["id"]; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            title="حذف"
                                            onclick="return confirm('آیا از حذف این محصول مطمئن هستید؟\\n\\nتصویر محصول نیز از پوشه تصاویر حذف خواهد شد.');"
                                        >

                                            <i
                                                class="bi bi-trash"
                                            ></i>

                                        </a>


                                    </div>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php endif; ?>


        </div>


    </div>


</div>



<!-- Bootstrap JS -->

<script
    src="/HABIBI/assets/bootstrap/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
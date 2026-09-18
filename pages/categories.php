<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


// دریافت دسته‌بندی‌ها و تعداد محصولات هر دسته
$stmt = $pdo->query("
    SELECT
        c.id,
        c.name,
        COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p
        ON p.category_id = c.id
        AND p.status = 'active'
    GROUP BY c.id, c.name
    ORDER BY c.id ASC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


// هدر
require_once __DIR__ . "/../includes/header.php";

?>

<main>

    <!-- عنوان صفحه -->

    <section class="py-5">

        <div class="container">

            <div class="text-center mb-5">

                

                <h1 class="mt-2">
                    دسته‌بندی محصولات
                </h1>

                <p class="text-muted">
                    دسته‌بندی موردنظر خود را انتخاب کنید.
                </p>

            </div>


            <!-- دسته‌بندی‌ها -->

            <div class="row g-4">

                <?php if (empty($categories)): ?>

                    <div class="col-12">

                        <div class="alert alert-warning text-center">

                            هنوز دسته‌بندی‌ای ثبت نشده است.

                        </div>

                    </div>

                <?php else: ?>


                    <?php foreach ($categories as $category): ?>

                        <?php

                        $categoryId = (int) $category["id"];

                        $categoryName = $category["name"];

                        $productCount = (int) $category["product_count"];


                        // تصویر دسته‌بندی
                        $image = "";

                        switch ($categoryId) {

                            case 1:
                                $image = "nuts.jpg";
                                break;

                            case 2:
                                $image = "dried-fruits.jpg";
                                break;

                            case 3:
                                $image = "nuts-kernels.jpg";
                                break;

                            case 4:
                                $image = "saffron.jpg";
                                break;

                            default:
                                $image = "";
                                break;

                        }

                        ?>


                        <div class="col-6 col-md-4 col-lg-3">

                            <a
                                href="/HABIBI/pages/products.php?category=<?php echo $categoryId; ?>"
                                class="text-decoration-none"
                            >

                                <div class="card h-100 border-0 shadow-sm overflow-hidden">


                                    <!-- تصویر -->

                                    <?php if ($image !== ""): ?>

                                        <img
                                            src="/HABIBI/assets/images/products/<?php echo htmlspecialchars($image, ENT_QUOTES, "UTF-8"); ?>"
                                            class="card-img-top"
                                            alt="<?php echo htmlspecialchars($categoryName, ENT_QUOTES, "UTF-8"); ?>"
                                            style="height:220px;object-fit:cover;"
                                        >

                                    <?php else: ?>

                                        <div
                                            class="d-flex align-items-center justify-content-center bg-light"
                                            style="height:220px;"
                                        >

                                            <i class="bi bi-grid fs-1 text-muted"></i>

                                        </div>

                                    <?php endif; ?>


                                    <!-- اطلاعات -->

                                    <div class="card-body text-center">

                                        <h3 class="h5 text-dark mb-2">

                                            <?php
                                            echo htmlspecialchars(
                                                $categoryName,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>

                                        </h3>


                                        <p class="text-muted mb-3">

                                            <?php echo $productCount; ?>

                                            محصول

                                        </p>


                                        <span class="btn btn-outline-danger">

                                            مشاهده محصولات

                                            <i class="bi bi-arrow-left"></i>

                                        </span>

                                    </div>


                                </div>

                            </a>

                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>

            </div>

        </div>

    </section>

</main>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
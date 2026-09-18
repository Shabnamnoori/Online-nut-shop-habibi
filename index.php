<?php

require_once __DIR__ . "/includes/config.php";


/* =====================================================
   محصولات ویژه
===================================================== */

$stmt = $pdo->query("
    SELECT
        p.id,
        p.name,
        p.slug,
        p.description,
        p.image,
        c.name AS category_name,

        (
            SELECT pw.price
            FROM product_weights pw
            WHERE pw.product_id = p.id
              AND pw.status = 'active'
              AND pw.weight = 1000
            ORDER BY pw.id ASC
            LIMIT 1
        ) AS kilo_price,

        (
            SELECT pw.price
            FROM product_weights pw
            WHERE pw.product_id = p.id
              AND pw.status = 'active'
            ORDER BY pw.weight ASC, pw.id ASC
            LIMIT 1
        ) AS lowest_price

    FROM products p

    INNER JOIN categories c
        ON c.id = p.category_id

    WHERE p.status = 'active'
      AND p.is_featured = 1
      AND c.status = 'active'

    ORDER BY p.id DESC

    LIMIT 4
");

$featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);


require_once __DIR__ . "/includes/header.php";

?>


<main>


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="habibi-hero">

        <div class="container">

            <div class="row align-items-center g-4">


                <!-- متن -->

                <div class="col-lg-6">

                    <div class="habibi-hero-content">


                        <span class="habibi-hero-badge">

                            <i class="bi bi-stars"></i>

                            طعم اصیل و تازه ایرانی

                        </span>


                        <h1>

                            خوشمزه‌ترین

                            <span>
                                خشکبار
                            </span>

                            برای لحظه‌های شیرین شما

                        </h1>


                        <p>

                            مجموعه‌ای از بهترین و تازه‌ترین خشکبار و آجیل
                            با کیفیت بالا، بسته‌بندی زیبا و ارسال مطمئن.

                        </p>


                        <div class="habibi-hero-buttons">


                            <a
                                href="/HABIBI/pages/products.php"
                                class="habibi-btn"
                            >

                                <i class="bi bi-bag"></i>

                                مشاهده محصولات

                            </a>


                            <a
                                href="/HABIBI/pages/categories.php"
                                class="habibi-btn habibi-btn-outline"
                            >

                                دسته‌بندی‌ها

                                <i class="bi bi-arrow-left"></i>

                            </a>


                        </div>


                        <!-- ویژگی‌ها -->

                        <div class="habibi-hero-features">


                            <div>

                                <i class="bi bi-patch-check-fill"></i>

                                <span>
                                    کیفیت تضمین‌شده
                                </span>

                            </div>


                            <div>

                                <i class="bi bi-truck"></i>

                                <span>
                                    ارسال سریع
                                </span>

                            </div>


                            <div>

                                <i class="bi bi-heart-fill"></i>

                                <span>
                                    انتخاب تازه
                                </span>

                            </div>


                        </div>


                    </div>

                </div>


                <!-- تصویر -->

                <div class="col-lg-6">

                    <div class="habibi-hero-image">


                        <div class="hero-decoration hero-decoration-one">
                            ✦
                        </div>


                        <div class="hero-decoration hero-decoration-two">
                            ✦
                        </div>


                        <div class="habibi-hero-image-frame">

                            <img
                                src="/HABIBI/assets/images/banners/hero.jpg"
                                alt="خشکبار حبیبی"
                            >

                        </div>


                        <div class="hero-floating-card">


                            <i class="bi bi-award-fill"></i>


                            <div>

                                <strong>
                                    انتخاب ویژه
                                </strong>

                                <span>
                                    محصولات تازه و باکیفیت
                                </span>

                            </div>


                        </div>


                    </div>

                </div>


            </div>

        </div>

    </section>



    <!-- =====================================================
         CATEGORIES
    ====================================================== -->

    <section class="habibi-section habibi-categories-section">

        <div class="container">


            <div class="habibi-section-title">

                <span class="eyebrow">
                    انتخاب کنید
                </span>


                <h2>
                    دسته‌بندی محصولات
                </h2>


                <p>
                    هر چیزی که برای یک پذیرایی خوشمزه لازم دارید.
                </p>


            </div>


            <div class="row g-4">


                <!-- آجیل -->

                <div class="col-6 col-md-4 col-lg-3">

                    <a
                        href="/HABIBI/pages/products.php?category=1"
                        class="habibi-category-card"
                    >

                        <div class="category-image">

                            <img
                                src="/HABIBI/assets/images/products/nuts.jpg"
                                alt="آجیل"
                            >

                        </div>


                        <div class="category-content">

                            <h3>
                                آجیل
                            </h3>


                            <span>

                                مشاهده محصولات

                                <i class="bi bi-arrow-left"></i>

                            </span>

                        </div>

                    </a>

                </div>


                <!-- میوه خشک -->

                <div class="col-6 col-md-4 col-lg-3">

                    <a
                        href="/HABIBI/pages/products.php?category=2"
                        class="habibi-category-card"
                    >

                        <div class="category-image">

                            <img
                                src="/HABIBI/assets/images/products/dried-fruits.jpg"
                                alt="میوه خشک"
                            >

                        </div>


                        <div class="category-content">

                            <h3>
                                میوه خشک
                            </h3>


                            <span>

                                مشاهده محصولات

                                <i class="bi bi-arrow-left"></i>

                            </span>

                        </div>

                    </a>

                </div>


                <!-- مغزها -->

                <div class="col-6 col-md-4 col-lg-3">

                    <a
                        href="/HABIBI/pages/products.php?category=3"
                        class="habibi-category-card"
                    >

                        <div class="category-image">

                            <img
                                src="/HABIBI/assets/images/products/nuts-kernels.jpg"
                                alt="مغزها"
                            >

                        </div>


                        <div class="category-content">

                            <h3>
                                مغزها
                            </h3>


                            <span>

                                مشاهده محصولات

                                <i class="bi bi-arrow-left"></i>

                            </span>

                        </div>

                    </a>

                </div>


                <!-- زعفران و ادویه -->

                <div class="col-6 col-md-4 col-lg-3">

                    <a
                        href="/HABIBI/pages/products.php?category=4"
                        class="habibi-category-card"
                    >

                        <div class="category-image">

                            <img
                                src="/HABIBI/assets/images/products/saffron.jpg"
                                alt="زعفران و ادویه"
                            >

                        </div>


                        <div class="category-content">

                            <h3>
                                زعفران و ادویه
                            </h3>


                            <span>

                                مشاهده محصولات

                                <i class="bi bi-arrow-left"></i>

                            </span>

                        </div>

                    </a>

                </div>


            </div>

        </div>

    </section>



    <!-- =====================================================
         FEATURED PRODUCTS
    ====================================================== -->

    <section class="habibi-section habibi-products-section">

        <div class="container">


            <div class="habibi-section-title">

                <span class="eyebrow">
                    پیشنهاد حبیبی
                </span>


                <h2>
                    محصولات محبوب
                </h2>


                <p>
                    چند انتخاب خوشمزه برای شروع خرید شما.
                </p>


            </div>


            <div class="row g-4">


                <?php if (empty($featuredProducts)): ?>


                    <div class="col-12">

                        <div class="text-center py-5">

                            <p class="text-muted mb-0">

                                در حال حاضر محصول ویژه‌ای برای نمایش وجود ندارد.

                            </p>

                        </div>

                    </div>


                <?php else: ?>


                    <?php foreach ($featuredProducts as $product): ?>


                        <?php

                        $price = $product["kilo_price"];

                        if ($price === null) {
                            $price = $product["lowest_price"];
                        }

                        ?>


                        <div class="col-6 col-lg-3">


                            <div class="habibi-product-card">


                                <!-- تصویر -->

                                <div class="product-image">


                                    <span class="product-badge">

                                        پیشنهاد حبیبی

                                    </span>


                                    <?php if (!empty($product["image"])): ?>


                                        <img
                                            src="/HABIBI/assets/images/products/<?php echo htmlspecialchars(
                                                $product["image"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ); ?>"
                                            alt="<?php echo htmlspecialchars(
                                                $product["name"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ); ?>"
                                        >


                                    <?php else: ?>


                                        <img
                                            src="/HABIBI/assets/images/products/default.jpg"
                                            alt="محصول حبیبی"
                                        >


                                    <?php endif; ?>


                                </div>


                                <!-- اطلاعات محصول -->

                                <div class="product-content">


                                    <span class="product-category">

                                        <?php

                                        echo htmlspecialchars(
                                            $product["category_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>

                                    </span>


                                    <h3>

                                        <?php

                                        echo htmlspecialchars(
                                            $product["name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>

                                    </h3>


                                    <div class="product-bottom">


                                        <div class="habibi-price">


                                            <?php

                                            echo number_format(
                                                (int)$price
                                            );

                                            ?>


                                            <small>
                                                تومان
                                            </small>


                                        </div>


                                        <a
                                            href="/HABIBI/pages/product.php?id=<?php echo (int)$product["id"]; ?>"
                                            class="product-add-cart"
                                            title="مشاهده محصول"
                                        >

                                            <i class="bi bi-bag-plus"></i>

                                        </a>


                                    </div>


                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>


            </div>


            <!-- مشاهده همه -->

            <div class="text-center mt-5">


                <a
                    href="/HABIBI/pages/products.php"
                    class="habibi-btn"
                >

                    مشاهده همه محصولات

                    <i class="bi bi-arrow-left"></i>

                </a>


            </div>


        </div>

    </section>



    <!-- =====================================================
         SPECIAL BANNER
    ====================================================== -->

    <section class="habibi-special-section">


        <div class="container">


            <div class="habibi-special-box">


                <div class="row align-items-center g-4">


                    <div class="col-lg-7">


                        <span class="eyebrow">
                            حبیبی برای شما
                        </span>


                        <h2>
                            کیفیتی که طعمش را احساس می‌کنید.
                        </h2>


                        <p>

                            از انتخاب بهترین خشکبار تا بسته‌بندی و ارسال،
                            همه چیز با دقت انجام می‌شود تا خریدی خوشمزه و مطمئن داشته باشید.

                        </p>


                        <a
                            href="/HABIBI/pages/products.php"
                            class="habibi-btn"
                        >

                            شروع خرید

                            <i class="bi bi-arrow-left"></i>

                        </a>


                    </div>


                    <div class="col-lg-5 text-center">


                        <img
                            src="/HABIBI/assets/images/banners/special.jpg"
                            alt="خشکبار حبیبی"
                            class="img-fluid"
                        >


                    </div>


                </div>


            </div>


        </div>


    </section>



</main>


<?php

require_once __DIR__ . "/includes/footer.php";

?>
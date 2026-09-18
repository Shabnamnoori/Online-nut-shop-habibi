<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================================================
   دریافت فیلترها
========================================================= */

$categoryId = (
    isset($_GET["category"]) &&
    is_numeric($_GET["category"])
)
    ? (int) $_GET["category"]
    : 0;

$search = trim($_GET["q"] ?? "");


/* =========================================================
   دریافت محصولات
========================================================= */

$sql = "
    SELECT
        p.id,
        p.category_id,
        p.name,
        p.slug,
        p.description,
        p.image,
        p.price,
        p.stock,
        c.name AS category_name,

        MIN(
            CASE
                WHEN pw.status = 'active'
                THEN pw.price
            END
        ) AS min_weight_price,

        COALESCE(
            SUM(
                CASE
                    WHEN pw.status = 'active'
                    THEN pw.stock
                    ELSE 0
                END
            ),
            0
        ) AS weight_stock

    FROM products p

    LEFT JOIN categories c
        ON c.id = p.category_id
        AND c.status = 'active'

    LEFT JOIN product_weights pw
        ON pw.product_id = p.id

    WHERE p.status = 'active'
";


$params = [];


/* =========================================================
   فیلتر دسته‌بندی
========================================================= */

if ($categoryId > 0) {

    $sql .= "
        AND p.category_id = :category_id
    ";

    $params["category_id"] = $categoryId;
}


/* =========================================================
   جستجو
========================================================= */

if ($search !== "") {

    $sql .= "
        AND (
            p.name LIKE :search_name
            OR p.description LIKE :search_description
        )
    ";

    $searchValue = "%" . $search . "%";

    $params["search_name"] = $searchValue;
    $params["search_description"] = $searchValue;
}


/* =========================================================
   گروه‌بندی
========================================================= */

$sql .= "
    GROUP BY
        p.id,
        p.category_id,
        p.name,
        p.slug,
        p.description,
        p.image,
        p.price,
        p.stock,
        c.name
";


/* =========================================================
   مرتب‌سازی
========================================================= */

$sql .= "
    ORDER BY p.id DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   دریافت دسته‌بندی‌های فعال
========================================================= */

$categoryStmt = $pdo->query("
    SELECT
        id,
        name
    FROM categories
    WHERE status = 'active'
    ORDER BY id ASC
");

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   عنوان دسته‌بندی انتخاب‌شده
========================================================= */

$selectedCategoryName = "";

if ($categoryId > 0) {

    foreach ($categories as $category) {

        if ((int)$category["id"] === $categoryId) {

            $selectedCategoryName =
                $category["name"];

            break;
        }
    }
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
                محصولات حبیبی
            </h1>

            <p>
                انواع خشکبار و آجیل با کیفیت
            </p>

        </div>


        <!-- =====================================================
             جستجو و دسته‌بندی
        ====================================================== -->

        <div class="row mb-5">


            <!-- جستجو -->

            <div class="col-md-8">

                <form
                    method="GET"
                    action=""
                    class="d-flex gap-2"
                >

                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        placeholder="جستجوی محصول..."
                        value="<?php
                            echo htmlspecialchars(
                                $search,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                    >


                    <?php if ($categoryId > 0): ?>

                        <input
                            type="hidden"
                            name="category"
                            value="<?php echo $categoryId; ?>"
                        >

                    <?php endif; ?>


                    <button
                        type="submit"
                        class="btn btn-danger"
                    >

                        <i class="bi bi-search"></i>

                       

                    </button>

                </form>

            </div>


            <!-- دسته‌بندی -->

            <div class="col-md-4 mt-3 mt-md-0">

                <form
                    method="GET"
                    action=""
                >


                    <?php if ($search !== ""): ?>

                        <input
                            type="hidden"
                            name="q"
                            value="<?php
                                echo htmlspecialchars(
                                    $search,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                        >

                    <?php endif; ?>


                    <select
                        name="category"
                        class="form-select"
                        onchange="this.form.submit()"
                    >

                        <option value="0">

                            همه دسته‌بندی‌ها

                        </option>


                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php echo (int)$category["id"]; ?>"
                                <?php
                                echo (
                                    $categoryId ===
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

                </form>

            </div>

        </div>


        <!-- =====================================================
             فیلتر فعال
        ====================================================== -->

        <?php if ($search !== "" || $categoryId > 0): ?>

            <div class="mb-4 text-muted">


                <?php if ($search !== ""): ?>

                    نتیجه جستجو برای:

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </strong>

                <?php endif; ?>


                <?php if ($categoryId > 0): ?>

                    <?php if ($search !== ""): ?>

                        <span class="mx-2">
                            |
                        </span>

                    <?php endif; ?>


                    دسته:

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $selectedCategoryName,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </strong>

                <?php endif; ?>


            </div>

        <?php endif; ?>


        <!-- =====================================================
             محصولات
        ====================================================== -->

        <div class="row g-4">


            <?php if (empty($products)): ?>


                <div class="col-12">

                    <div class="alert alert-warning text-center">

                        <i class="bi bi-search"></i>

                        محصولی با این مشخصات پیدا نشد.

                    </div>

                </div>


            <?php else: ?>


                <?php foreach ($products as $product): ?>


                    <?php

                    $image =
                        trim($product["image"] ?? "");

                    $minPrice =
                        $product["min_weight_price"];

                    $basePrice =
                        (float)$product["price"];

                    $stock =
                        (int)$product["stock"];

                    $weightStock =
                        (int)$product["weight_stock"];


                    /*
                     * اگر وزن فعال وجود داشته باشد،
                     * کمترین قیمت وزن نمایش داده می‌شود.
                     */

                    if (
                        $minPrice !== null &&
                        (float)$minPrice > 0
                    ) {

                        $displayPrice =
                            (float)$minPrice;

                    } else {

                        $displayPrice =
                            $basePrice;

                    }


                    /*
                     * بررسی موجودی
                     */

                    $hasStock =
                        ($weightStock > 0 || $stock > 0);

                    ?>


                    <div class="col-12 col-sm-6 col-lg-4">


                        <div class="card h-100">


                            <!-- تصویر -->

                            <?php if ($image !== ""): ?>

                                <img
                                    src="/HABIBI/assets/images/products/<?php
                                        echo htmlspecialchars(
                                            $image,
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>"
                                    class="card-img-top"
                                    alt="<?php
                                        echo htmlspecialchars(
                                            $product["name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <div class="text-center p-5">

                                    <i class="bi bi-image fs-1"></i>

                                    <div class="mt-2">
                                        تصویر موجود نیست
                                    </div>

                                </div>

                            <?php endif; ?>


                            <!-- اطلاعات -->

                            <div class="card-body d-flex flex-column">


                                <!-- دسته -->

                                <small class="text-muted">

                                    <?php
                                    echo htmlspecialchars(
                                        $product["category_name"]
                                            ?? "بدون دسته‌بندی",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </small>


                                <!-- نام -->

                                <h5 class="card-title mt-2">

                                    <?php
                                    echo htmlspecialchars(
                                        $product["name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </h5>


                                <!-- توضیحات -->

                                <?php

                                $description =
                                    trim(
                                        $product["description"] ?? ""
                                    );

                                ?>


                                <?php if ($description !== ""): ?>

                                    <p class="card-text">

                                        <?php

                                        $shortDescription =
                                            mb_substr(
                                                $description,
                                                0,
                                                100
                                            );

                                        echo htmlspecialchars(
                                            $shortDescription,
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        if (
                                            mb_strlen(
                                                $description
                                            ) > 100
                                        ) {

                                            echo "...";
                                        }

                                        ?>

                                    </p>

                                <?php endif; ?>


                                <!-- قیمت -->

                                <div class="mb-3">


                                    <?php if ($minPrice !== null): ?>

                                        <small class="text-muted d-block">

                                            شروع قیمت از

                                        </small>

                                    <?php endif; ?>


                                    <strong>

                                        <?php
                                        echo number_format(
                                            $displayPrice
                                        );
                                        ?>

                                        تومان

                                    </strong>


                                </div>


                                <!-- موجودی -->

                                <div class="mb-3">


                                    <?php if ($hasStock): ?>

                                        <span class="badge bg-success">

                                            موجود

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">

                                            ناموجود

                                        </span>

                                    <?php endif; ?>


                                </div>


                                <!-- مشاهده محصول -->

                                <a
                                    href="/HABIBI/pages/product.php?id=<?php echo (int)$product["id"]; ?>"
                                    class="btn btn-danger w-100 mt-auto"
                                >

                                    <i class="bi bi-eye"></i>

                                    مشاهده محصول

                                </a>


                            </div>

                        </div>

                    </div>


                <?php endforeach; ?>


            <?php endif; ?>


        </div>


    </div>

</main>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
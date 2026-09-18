<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";


/* =========================================================
   بررسی ID محصول
========================================================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: /HABIBI/pages/products.php");
    exit;

}

$productId = (int) $_GET["id"];


/* =========================================================
   دریافت محصول
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        p.*,
        c.name AS category_name
    FROM products p

    LEFT JOIN categories c
        ON p.category_id = c.id

    WHERE
        p.id = :id
        AND p.status = 'active'

    LIMIT 1
");

$stmt->execute([
    ":id" => $productId
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);


/* =========================================================
   اگر محصول وجود نداشت
========================================================= */

if (!$product) {

    header("Location: /HABIBI/pages/products.php");
    exit;

}


/* =========================================================
   دریافت وزن‌های محصول
========================================================= */

$weightStmt = $pdo->prepare("
    SELECT
        id,
        weight,
        unit,
        price,
        stock
    FROM product_weights
    WHERE
        product_id = :product_id
        AND status = 'active'
        AND stock > 0
    ORDER BY weight ASC
");

$weightStmt->execute([
    ":product_id" => $productId
]);

$weights = $weightStmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   تصویر محصول
========================================================= */

$image = $product["image"] ?? "";


/* =========================================================
   Header
========================================================= */

require_once __DIR__ . "/../includes/header.php";

?>


<main>

    <div class="container py-5">


        <div class="row g-5">


            <!-- =================================================
                 تصویر محصول
            ================================================== -->

            <div class="col-lg-6">

                <?php if ($image !== ""): ?>

                    <img
                        src="/HABIBI/assets/images/products/<?php
                            echo htmlspecialchars(
                                $image,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                        class="img-fluid rounded"
                        alt="<?php
                            echo htmlspecialchars(
                                $product["name"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                    >

                <?php else: ?>

                    <div class="text-center p-5 border rounded">

                        <i class="bi bi-image fs-1"></i>

                        <p class="mt-3">
                            تصویر محصول موجود نیست.
                        </p>

                    </div>

                <?php endif; ?>

            </div>



            <!-- =================================================
                 اطلاعات محصول
            ================================================== -->

            <div class="col-lg-6">


                <!-- دسته‌بندی -->

                <div class="text-muted mb-2">

                    <?php
                    echo htmlspecialchars(
                        $product["category_name"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>


                <!-- نام -->

                <h1 class="mb-3">

                    <?php
                    echo htmlspecialchars(
                        $product["name"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </h1>


                <!-- توضیحات -->

                <p class="mb-4">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $product["description"] ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        )
                    );
                    ?>

                </p>



                <!-- =================================================
                     انتخاب وزن
                ================================================== -->

                <?php if (!empty($weights)): ?>


                    <h5 class="mb-3">
                        انتخاب وزن:
                    </h5>


                    <form
                        method="POST"
                        action="/HABIBI/pages/cart.php"
                    >


                        <input
                            type="hidden"
                            name="action"
                            value="add"
                        >


                        <input
                            type="hidden"
                            name="product_id"
                            value="<?php echo $productId; ?>"
                        >


                        <div class="row g-2 mb-4">


                            <?php foreach ($weights as $index => $weight): ?>


                                <div class="col-4">


                                    <input
                                        type="radio"
                                        class="btn-check"
                                        name="product_weight_id"
                                        id="weight-<?php echo (int) $weight["id"]; ?>"
                                        value="<?php echo (int) $weight["id"]; ?>"
                                        <?php echo $index === 0 ? "checked" : ""; ?>
                                    >


                                    <label
                                        class="btn btn-outline-danger w-100"
                                        for="weight-<?php echo (int) $weight["id"]; ?>"
                                    >

                                        <?php
                                        echo rtrim(
                                            rtrim(
                                                number_format(
                                                    (float) $weight["weight"],
                                                    2
                                                ),
                                                "0"
                                            ),
                                            "."
                                        );
                                        ?>

                                        <?php
                                        echo htmlspecialchars(
                                            $weight["unit"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </label>

                                </div>


                            <?php endforeach; ?>


                        </div>



                        <!-- =================================================
                             قیمت
                        ================================================== -->

                        <div class="mb-3">

                            <span>
                                قیمت:
                            </span>

                            <strong
                                id="productPrice"
                                class="fs-4"
                            >

                                <?php
                                echo number_format(
                                    (float) $weights[0]["price"]
                                );
                                ?>

                                تومان

                            </strong>

                        </div>



                        <!-- =================================================
                             موجودی
                        ================================================== -->

                        <div
                            id="productStock"
                            class="text-muted mb-4"
                        >

                            موجودی:

                            <?php
                            echo (int) $weights[0]["stock"];
                            ?>

                            عدد

                        </div>



                        <!-- =================================================
                             تعداد
                        ================================================== -->

                        <div class="mb-4">

                            <label
                                for="quantity"
                                class="form-label"
                            >
                                تعداد
                            </label>

                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="form-control"
                                value="1"
                                min="1"
                                max="<?php echo (int) $weights[0]["stock"]; ?>"
                            >

                        </div>



                        <!-- =================================================
                             افزودن به سبد
                        ================================================== -->

                        <button
                            type="submit"
                            class="btn btn-danger btn-lg w-100"
                        >

                            <i class="bi bi-bag-plus"></i>

                            افزودن به سبد خرید

                        </button>


                    </form>


                <?php else: ?>


                    <div class="alert alert-warning">

                        این محصول در حال حاضر موجود نیست.

                    </div>


                <?php endif; ?>


            </div>


        </div>


    </div>

</main>


<script>

const weights = <?php
echo json_encode(
    $weights,
    JSON_UNESCAPED_UNICODE
);
?>;


const priceElement =
    document.getElementById("productPrice");

const stockElement =
    document.getElementById("productStock");

const quantityInput =
    document.getElementById("quantity");


document.querySelectorAll(
    'input[name="product_weight_id"]'
).forEach(function (radio) {


    radio.addEventListener(
        "change",
        function () {


            const selectedId =
                parseInt(this.value);


            const selectedWeight =
                weights.find(function (item) {

                    return parseInt(item.id) === selectedId;

                });


            if (!selectedWeight) {
                return;
            }


            /* قیمت */

            priceElement.innerHTML =
                Number(
                    selectedWeight.price
                ).toLocaleString("fa-IR")
                + " تومان";


            /* موجودی */

            stockElement.innerHTML =
                "موجودی: "
                + Number(
                    selectedWeight.stock
                ).toLocaleString("fa-IR")
                + " عدد";


            /* حداکثر تعداد */

            quantityInput.max =
                selectedWeight.stock;


            /* اگر تعداد بیشتر از موجودی بود */

            if (
                parseInt(quantityInput.value)
                >
                parseInt(selectedWeight.stock)
            ) {

                quantityInput.value = 1;

            }

        }
    );

});

</script>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
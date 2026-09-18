<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/config.php";

require_once __DIR__ . "/../includes/header.php";

?>

<main>


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="py-5">

        <div class="container">

            <div class="row align-items-center g-5">


                <!-- متن -->

                <div class="col-lg-6">

                    <h1 class="display-5 fw-bold mt-3 mb-4">

                        طعم اصیل،
                        <span class="text-danger">
                            انتخابی مطمئن
                        </span>

                    </h1>

                    <p class="text-muted lh-lg">

                        فروشگاه اینترنتی خشکبار حبیبی با هدف ارائه
                        محصولات تازه، باکیفیت و خوش‌طعم راه‌اندازی شده است.
                        ما تلاش می‌کنیم مجموعه‌ای متنوع از آجیل،
                        خشکبار، مغزها و محصولات ارزشمند ایرانی را
                        با کیفیت مناسب در اختیار شما قرار دهیم.

                    </p>

                    <p class="text-muted lh-lg">

                        در حبیبی کیفیت محصولات، تازگی، بسته‌بندی مناسب
                        و رضایت مشتریان از مهم‌ترین اولویت‌های ماست.
                        هدف ما این است که خرید خشکبار برای شما
                        ساده، سریع و لذت‌بخش باشد.

                    </p>


                    <a
                        href="/HABIBI/pages/products.php"
                        class="btn btn-danger px-4 py-3 mt-2"
                    >

                        <i class="bi bi-bag"></i>

                        مشاهده محصولات

                    </a>

                </div>


                <!-- تصویر -->

                <div class="col-lg-6">

                    <div
                        class="rounded-4 overflow-hidden shadow"
                    >

                        <img
                            src="/HABIBI/assets/images/banners/about.jpg"
                            alt="درباره فروشگاه حبیبی"
                            class="w-100"
                            style="height:420px;object-fit:cover;"
                        >

                    </div>

                </div>


            </div>

        </div>

    </section>



    <!-- =====================================================
         VALUES
    ====================================================== -->

    <section class="py-5 bg-light">

        <div class="container">

            <div class="text-center mb-5">

                <span class="text-danger fw-bold">
                    چرا حبیبی؟
                </span>

                <h2 class="fw-bold mt-2">
                    ارزش‌های ما
                </h2>

                <p class="text-muted">
                    چیزهایی که برای ما اهمیت بیشتری دارند.
                </p>

            </div>


            <div class="row g-4">


                <!-- کیفیت -->

                <div class="col-md-6 col-lg-3">

                    <div class="card border-0 shadow-sm h-100 text-center p-4">

                        <div class="fs-1 text-danger mb-3">

                            <i class="bi bi-patch-check-fill"></i>

                        </div>

                        <h3 class="h5 fw-bold">
                            کیفیت بالا
                        </h3>

                        <p class="text-muted mb-0">

                            تلاش می‌کنیم محصولات باکیفیت و
                            مناسب برای مشتریان ارائه کنیم.

                        </p>

                    </div>

                </div>


                <!-- تازگی -->

                <div class="col-md-6 col-lg-3">

                    <div class="card border-0 shadow-sm h-100 text-center p-4">

                        <div class="fs-1 text-danger mb-3">

                            <i class="bi bi-stars"></i>

                        </div>

                        <h3 class="h5 fw-bold">
                            تازگی محصولات
                        </h3>

                        <p class="text-muted mb-0">

                            تازگی و کیفیت محصول یکی از
                            اولویت‌های اصلی حبیبی است.

                        </p>

                    </div>

                </div>


                <!-- ارسال -->

                <div class="col-md-6 col-lg-3">

                    <div class="card border-0 shadow-sm h-100 text-center p-4">

                        <div class="fs-1 text-danger mb-3">

                            <i class="bi bi-truck"></i>

                        </div>

                        <h3 class="h5 fw-bold">
                            ارسال مطمئن
                        </h3>

                        <p class="text-muted mb-0">

                            سفارش‌ها با بسته‌بندی مناسب
                            برای ارسال آماده می‌شوند.

                        </p>

                    </div>

                </div>


                <!-- رضایت -->

                <div class="col-md-6 col-lg-3">

                    <div class="card border-0 shadow-sm h-100 text-center p-4">

                        <div class="fs-1 text-danger mb-3">

                            <i class="bi bi-heart-fill"></i>

                        </div>

                        <h3 class="h5 fw-bold">
                            رضایت مشتری
                        </h3>

                        <p class="text-muted mb-0">

                            رضایت مشتریان برای ما مهم‌ترین
                            بخش تجربه خرید است.

                        </p>

                    </div>

                </div>


            </div>

        </div>

    </section>



    <!-- =====================================================
         STATS
    ====================================================== -->

    <section class="py-5">

        <div class="container">

            <div class="row g-4 text-center">


                <div class="col-6 col-md-3">

                    <div class="p-3">

                        <div class="display-6 fw-bold text-danger">
                            100٪
                        </div>

                        <p class="text-muted mb-0">
                            توجه به کیفیت
                        </p>

                    </div>

                </div>


                <div class="col-6 col-md-3">

                    <div class="p-3">

                        <div class="display-6 fw-bold text-danger">
                            تازه
                        </div>

                        <p class="text-muted mb-0">
                            محصولات منتخب
                        </p>

                    </div>

                </div>


                <div class="col-6 col-md-3">

                    <div class="p-3">

                        <div class="display-6 fw-bold text-danger">
                            سریع
                        </div>

                        <p class="text-muted mb-0">
                            آماده‌سازی سفارش
                        </p>

                    </div>

                </div>


                <div class="col-6 col-md-3">

                    <div class="p-3">

                        <div class="display-6 fw-bold text-danger">
                            حبیبی
                        </div>

                        <p class="text-muted mb-0">
                            انتخاب شما
                        </p>

                    </div>

                </div>


            </div>

        </div>

    </section>



    <!-- =====================================================
         CTA
    ====================================================== -->

    <section class="py-5">

        <div class="container">

            <div
                class="bg-dark text-white rounded-4 p-5 text-center"
            >

                <i class="bi bi-bag-heart fs-1 text-danger"></i>

                <h2 class="fw-bold mt-3">
                    آماده یک خرید خوشمزه هستید؟
                </h2>

                <p class="text-white-50">
                    محصولات حبیبی را ببینید و انتخاب موردعلاقه‌تان را پیدا کنید.
                </p>

                <a
                    href="/HABIBI/pages/products.php"
                    class="btn btn-danger px-4 py-3"
                >

                    مشاهده محصولات

                    <i class="bi bi-arrow-left"></i>

                </a>

            </div>

        </div>

    </section>


</main>


<?php

require_once __DIR__ . "/../includes/footer.php";

?>
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config.php";


/* =========================================
   وضعیت ورود کاربر
========================================= */

$isLoggedIn = isset($_SESSION["user_id"]);


/* =========================================
   وضعیت ادمین
========================================= */

$isAdmin = (
    isset($_SESSION["role_id"]) &&
    (int) $_SESSION["role_id"] === 1
);


/* =========================================
   نام کاربر
========================================= */

$fullName = "";

if ($isLoggedIn) {

    $fullName = htmlspecialchars(
        $_SESSION["full_name"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

}


/* =========================================
   تعداد سبد خرید
========================================= */

$cartCount = 0;

if (
    isset($_SESSION["cart"]) &&
    is_array($_SESSION["cart"])
) {

    foreach ($_SESSION["cart"] as $cartItem) {

        $cartCount += (int) (
            $cartItem["quantity"] ?? 0
        );

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
        حبیبی | فروشگاه اینترنتی خشکبار
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


   

    <!-- Main CSS -->

    <link
        rel="stylesheet"
        href="/HABIBI/assets/css/style.css?v=1"
    >
    <link
        rel="stylesheet"
        href="/HABIBI/assets/css/pages.css?v=1"
    >
<link
    rel="stylesheet"
    href="/HABIBI/assets/css/contact.css?v=1"
>
    <!-- Header CSS -->

    <link
        rel="stylesheet"
        href="/HABIBI/assets/css/header.css?v=1"
    >

</head>


<body>


<!-- =========================================
     HEADER
========================================= -->

<header class="habibi-header">


    <div class="container">


        <div class="habibi-header-inner">


            <!-- =================================
                 MOBILE MENU
            ================================== -->

            <button
                type="button"
                class="habibi-menu-button"
                id="openSideMenu"
                aria-label="باز کردن منو"
            >

                <i class="bi bi-list"></i>

            </button>


            <!-- =================================
                 LOGO
            ================================== -->

            <a
                href="/HABIBI/"
                class="habibi-logo"
            >

                <img
                    src="/HABIBI/assets/images/logo/habibi-logo.png"
                    alt="حبیبی"
                >

            </a>


            <!-- =================================
                 DESKTOP NAVIGATION
            ================================== -->

            <nav class="habibi-nav">


                <a href="/HABIBI/">
                    خانه
                </a>


                <a href="/HABIBI/pages/products.php">
                    محصولات
                </a>


                <a href="/HABIBI/pages/categories.php">
                    دسته‌بندی‌ها
                </a>


                <a href="/HABIBI/pages/about.php">
                    درباره ما
                </a>


                <a href="/HABIBI/pages/contact.php">
                    تماس با ما
                </a>


            </nav>


            <!-- =================================
                 HEADER ACTIONS
            ================================== -->

            <div class="habibi-actions">


                <!-- =================================
                     SEARCH
                ================================== -->

                <button
                    type="button"
                    class="habibi-action"
                    id="openSearch"
                    title="جستجو"
                    aria-label="جستجو"
                >

                    <i class="bi bi-search"></i>

                </button>


                <!-- =================================
                     USER
                ================================== -->

                <?php if ($isLoggedIn): ?>


                    <div
                        class="habibi-user-dropdown"
                        id="userDropdown"
                    >


                        <button
                            type="button"
                            class="habibi-action habibi-user-button"
                            id="userButton"
                            title="حساب کاربری"
                            aria-expanded="false"
                        >

                            <i class="bi bi-person-circle"></i>

                            <span class="user-name">
                                <?php echo $fullName; ?>
                            </span>

                            <i class="bi bi-chevron-down user-chevron"></i>

                        </button>


                        <!-- USER MENU -->

                        <div
                            class="habibi-user-menu"
                            id="userMenu"
                        >


                            <div class="user-menu-greeting">

                                <span>
                                    سلام،
                                </span>

                                <strong>
                                    <?php echo $fullName; ?>
                                </strong>

                            </div>


                            <!-- حساب کاربری -->

                            <a
                                href="/HABIBI/pages/profile.php"
                                class="user-menu-item"
                            >

                                <i class="bi bi-person"></i>

                                <span>
                                    حساب کاربری
                                </span>

                            </a>


                            <!-- سفارش‌ها -->

                            <?php if (!$isAdmin): ?>

                                <a
                                    href="/HABIBI/pages/orders.php"
                                    class="user-menu-item"
                                >

                                    <i class="bi bi-box-seam"></i>

                                    <span>
                                        سفارش‌های من
                                    </span>

                                </a>

                            <?php endif; ?>


                            <!-- سبد خرید -->

                            <?php if (!$isAdmin): ?>

                                <a
                                    href="/HABIBI/pages/cart.php"
                                    class="user-menu-item"
                                >

                                    <i class="bi bi-bag"></i>

                                    <span>
                                        سبد خرید
                                    </span>


                                    <?php if ($cartCount > 0): ?>

                                        <span class="badge bg-danger me-auto">
                                            <?php echo $cartCount; ?>
                                        </span>

                                    <?php endif; ?>


                                </a>

                            <?php endif; ?>


                            <!-- پنل مدیریت -->

                            <?php if ($isAdmin): ?>

                                <a
                                    href="/HABIBI/admin/index.php"
                                    class="user-menu-item"
                                >

                                    <i class="bi bi-speedometer2"></i>

                                    <span>پنل مدیریت
                                    </span>

                                </a>

                            <?php endif; ?>


                            <div class="user-menu-divider"></div>


                            <!-- خروج -->

                            <a
                                href="/HABIBI/pages/logout.php"
                                class="user-menu-item user-menu-logout"
                            >

                                <i class="bi bi-box-arrow-right"></i>

                                <span>
                                    خروج از حساب
                                </span>

                            </a>


                        </div>

                    </div>


                <?php else: ?>


                    <!-- ورود / ثبت‌نام -->

                    <a
                        href="/HABIBI/pages/login.php"
                        class="habibi-action"
                        title="ورود / ثبت‌نام"
                        aria-label="ورود / ثبت‌نام"
                    >

                        <i class="bi bi-person"></i>

                    </a>


                <?php endif; ?>


                <!-- =================================
                     CART
                ================================== -->

                <?php if (!$isAdmin): ?>

                    <a
                        href="/HABIBI/pages/cart.php"
                        class="habibi-action habibi-cart"
                        title="سبد خرید"
                        aria-label="سبد خرید"
                    >

                        <i class="bi bi-bag"></i>


                        <?php if ($cartCount > 0): ?>

                            <span class="habibi-cart-badge">

                                <?php echo $cartCount; ?>

                            </span>

                        <?php endif; ?>


                    </a>

                <?php endif; ?>


            </div>


        </div>


    </div>


    <!-- =========================================
         SEARCH BOX
    ========================================== -->

    <div
        class="habibi-search"
        id="searchBox"
    >

        <div class="container">

           <form
    action="/HABIBI/pages/products.php"
    method="GET"
    class="habibi-search-form"
>

                <input
                    type="text"
                    name="q"
                    placeholder="جستجوی خشکبار..."
                    autocomplete="off"
                >

                <button
                    type="submit"
                    aria-label="جستجو"
                >

                    <i class="bi bi-search"></i>

                </button>

            </form>

        </div>

    </div>


</header>


<!-- =========================================
     MOBILE OVERLAY
========================================== -->

<div
    class="habibi-overlay"
    id="sideOverlay"
></div>


<!-- =========================================
     MOBILE SIDE MENU
========================================== -->

<aside
    class="habibi-side-menu"
    id="sideMenu"
>


    <div class="habibi-side-header">

        <strong>
            منوی حبیبی
        </strong>


        <button
            type="button"
            class="habibi-side-close"
            id="closeSideMenu"
            aria-label="بستن منو"
        >

            <i class="bi bi-x-lg"></i>

        </button>

    </div>


    <nav class="habibi-side-links">


        <a href="/HABIBI/">

            <i class="bi bi-house"></i>

            <span>
                خانه
            </span>

        </a>


        <a href="/HABIBI/pages/products.php">

            <i class="bi bi-grid"></i>

            <span>
                محصولات
            </span>

        </a>


        <a href="/HABIBI/pages/categories.php">

            <i class="bi bi-tags"></i>

            <span>
                دسته‌بندی‌ها
            </span>

        </a>


        <a href="/HABIBI/pages/about.php">

            <i class="bi bi-info-circle"></i>

            <span>
                درباره ما
            </span>

        </a>


        <a href="/HABIBI/pages/contact.php">

            <i class="bi bi-envelope"></i>

            <span>
                تماس با ما
            </span>

        </a>


        <?php if ($isAdmin): ?>

            <a
                href="/HABIBI/admin/index.php"
                class="mobile-admin-link"
            >

                <i class="bi bi-speedometer2"></i>

                <span>
                    پنل مدیریت
                </span>

            </a>

        <?php endif; ?>


        <div class="side-menu-divider"></div>


        <?php if ($isLoggedIn): ?>


            <a href="/HABIBI/pages/profile.php">

                <i class="bi bi-person"></i>

                <span>
                    حساب کاربری
                </span>

            </a>


            <?php if (!$isAdmin): ?>

                <a href="/HABIBI/pages/orders.php">

                    <i class="bi bi-box-seam"></i>

                    <span>
                        سفارش‌های من
                    </span>

                </a>

            <?php endif; ?>


            <a
                href="/HABIBI/pages/logout.php"
                class="text-danger"
            >

                <i class="bi bi-box-arrow-right"></i>

                <span>
                    خروج از حساب
                </span>

            </a>


        <?php else: ?>


            <a href="/HABIBI/pages/login.php">

                <i class="bi bi-person"></i>

                <span>
                    ورود / ثبت‌نام
                </span>

            </a>


        <?php endif; ?>


    </nav>


</aside>


<!-- =========================================
     HEADER JAVASCRIPT
========================================== -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    /* =====================================
       SEARCH
    ====================================== */

    const searchButton =
        document.getElementById("openSearch");

    const searchBox =
        document.getElementById("searchBox");


    if (searchButton && searchBox) {

        searchButton.addEventListener(
            "click",
            function (event) {

                event.stopPropagation();

                searchBox.classList.toggle("show");


                if (searchBox.classList.contains("show")) {

                    const input =
                        searchBox.querySelector("input");


                    if (input) {

                        setTimeout(function () {

                            input.focus();

                        }, 100);

                    }

                }

            }
        );

    }


    /* =====================================
       USER DROPDOWN
    ====================================== */

    const userButton =
        document.getElementById("userButton");

    const userDropdown =
        document.getElementById("userDropdown");


    if (userButton && userDropdown) {

        userButton.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                event.stopPropagation();

                userDropdown.classList.toggle("open");


                const isOpen =
                    userDropdown.classList.contains("open");


                userButton.setAttribute(
                    "aria-expanded",
                    isOpen ? "true" : "false"
                );

            }
        );


        document.addEventListener(
            "click",
            function (event) {

                if (!userDropdown.contains(event.target)) {

                    userDropdown.classList.remove("open");

                    userButton.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                }

            }
        );

    }


    /* =====================================
       MOBILE MENU
    ====================================== */

    const sideMenu =
        document.getElementById("sideMenu");

    const sideOverlay =
        document.getElementById("sideOverlay");

    const openSideMenu =
        document.getElementById("openSideMenu");

    const closeSideMenu =
        document.getElementById("closeSideMenu");


    function openMenu() {

        if (!sideMenu || !sideOverlay) {
            return;
        }


        sideMenu.classList.add("show");

        sideOverlay.classList.add("show");

        document.body.style.overflow = "hidden";

    }


    function closeMenu() {

        if (!sideMenu || !sideOverlay) {
            return;
        }


        sideMenu.classList.remove("show");

        sideOverlay.classList.remove("show");

        document.body.style.overflow = "";

    }


    if (openSideMenu) {

        openSideMenu.addEventListener(
            "click",
            openMenu
        );

    }


    if (closeSideMenu) {

        closeSideMenu.addEventListener(
            "click",
            closeMenu
        );

    }


    if (sideOverlay) {

        sideOverlay.addEventListener(
            "click",
            closeMenu
        );

    }


    /* =====================================
       ESC
    ====================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeMenu();


                if (searchBox) {

                    searchBox.classList.remove("show");

                }


                if (userDropdown) {

                    userDropdown.classList.remove("open");

                }

            }

        }
    );


});

</script>


<!-- Bootstrap JS -->

<script
    src="/HABIBI/assets/bootstrap/js/bootstrap.bundle.min.js"
></script>
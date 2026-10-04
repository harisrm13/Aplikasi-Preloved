<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "auth.php";
require_once "db.php";


/* =========================
   DATA DASHBOARD
========================= */

// Total produk
$resultProducts = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
");

$totalProducts = $resultProducts->fetch_assoc()["total"];


// Pesanan user
$userId = (int) ($_SESSION["user_id"]?? 0);

$resultOrders = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE user_id = $userId
");

$totalOrders = $resultOrders->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - SecondSpace</title>


    <style>

/* ==================================================
   GLOBAL
================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f6f8;
    color: #111827;
}


/* ==================================================
   NAVBAR
================================================== */

.navbar {
    background: #111827;
    color: #ffffff;

    padding: 16px 7%;

    display: flex;
    align-items: center;

    position: sticky;
    top: 0;

    z-index: 1000;

    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);

    animation: navbarDown 0.5s ease-out;
}


/* ==================================================
   LOGO
================================================== */

.navbar .logo {
    display: inline-block;

    text-decoration: none;

    font-size: 22px;
    font-weight: 800;

    letter-spacing: -0.5px;

    white-space: nowrap;

    margin-right: auto;

    flex-shrink: 0;

    transition:
        transform 0.25s ease,
        opacity 0.25s ease;

    animation: logoSlide 0.6s ease-out;
}

.navbar .logo-second {
    color: #ffffff;
}

.navbar .logo-space {
    color: #9ff3d0;
}

.navbar .logo:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}


/* ==================================================
   MENU NAVBAR
================================================== */

.navbar .nav-menu {
    display: flex;

    align-items: center;

    gap: 24px;

    margin-left: auto;

    animation: menuFade 0.7s ease-out;
}


/* ==================================================
   MENU LINK
================================================== */

.navbar .nav-menu a {
    color: #ffffff;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    position: relative;

    padding: 5px 0;

    transition:
        color 0.25s ease,
        transform 0.25s ease;
}


/* ==================================================
   GARIS BAWAH MENU
================================================== */

.navbar .nav-menu a::after {
    content: "";

    position: absolute;

    left: 0;
    bottom: -9px;

    width: 100%;
    height: 3px;

    background: #ffffff;

    border-radius: 10px;

    transform: scaleX(0);

    transform-origin: left;

    transition: transform 0.35s ease;
}


/* ==================================================
   ACTIVE MENU
================================================== */

.navbar .nav-menu a.active {
    color: #ffffff;
}


/* ==================================================
   GARIS ACTIVE
================================================== */

.navbar .nav-menu a.active::after {
    transform: scaleX(1);
}


/* ==================================================
   HOVER MENU
================================================== */

.navbar .nav-menu a:hover::after {
    transform: scaleX(1);
}

.navbar .nav-menu a:hover {
    color: #9ff3d0;

    transform: translateY(-2px);
}


/* ==================================================
   LOGOUT
================================================== */

.navbar .nav-menu a.logout {
    background: #374151;

    color: #ffffff;

    padding: 9px 20px;

    border-radius: 10px;

    transition:
        background 0.25s ease,
        color 0.25s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease;
}


/* Logout tidak punya underline */
.navbar .nav-menu a.logout::after {
    display: none;
}


.navbar .nav-menu a.logout:hover {
    background: #4b5563;

    color: #ffffff;

    transform: translateY(-2px);

    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.18);
}


/* ==================================================
   WELCOME
================================================== */

.container {
    max-width: 1250px;

    margin: 0 auto;

    padding: 35px 20px 50px;

    animation: contentFade 0.7s ease-out;
}


/* ==================================================
   WELCOME CARD
================================================== */

.welcome {
    background:
        linear-gradient(
            135deg,
            #111827 0%,
            #1f2937 100%
        );

    border: 1px solid #374151;

    border-radius: 18px;

    padding: 35px;

    margin-bottom: 30px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.18);

    animation: sectionUp 0.7s ease-out;
}


.welcome h1 {
    font-size: 30px;

    color: #ffffff;

    margin-bottom: 10px;
}


.welcome p {
    font-size: 16px;

    color: #d1d5db;

    line-height: 1.7;
}


/* ==================================================
   STATISTICS
================================================== */

.stats {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 28px;

    margin-bottom: 40px;
}


/* ==================================================
   STAT CARD
================================================== */

.stat-card {
    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    padding: 28px 30px;

    min-height: 190px;

    box-shadow:
        0 6px 20px rgba(0, 0, 0, 0.05);

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;

    animation:
        cardUp 0.6s ease-out both;
}


.stat-card:nth-child(1) {
    animation-delay: 0.1s;
}


.stat-card:nth-child(2) {
    animation-delay: 0.2s;
}


.stat-card:hover {
    transform: translateY(-5px);

    box-shadow:
        0 12px 25px rgba(0, 0, 0, 0.08);
}


/* ==================================================
   ICON STATISTIK
================================================== */

.stat-icon {
    width: 46px;
    height: 46px;

    background: #e5e7eb;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 20px;

    margin-bottom: 16px;

    transition:
        background 0.25s ease,
        transform 0.25s ease;
}


.stat-card:hover .stat-icon {
    background: #d1d5db;

    transform: translateY(-3px);
}


/* ==================================================
   STAT TITLE
================================================== */

.stat-card h3 {
    font-size: 16px;

    color: #64748b;

    margin-bottom: 12px;
}


/* ==================================================
   STAT NUMBER
================================================== */

.stat-number {
    font-size: 42px;

    font-weight: 700;

    color: #111827;
}


/* ==================================================
   MENU SECTION
================================================== */

.menu-section {
    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    padding: 32px 35px;

    margin-bottom: 30px;

    box-shadow:
        0 6px 20px rgba(0, 0, 0, 0.05);

    animation: sectionUp 0.8s ease-out;
}


.menu-section h2 {
    font-size: 28px;

    color: #111827;

    margin-bottom: 25px;
}


/* ==================================================
   MENU GRID
================================================== */

.menu-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;
}


/* ==================================================
   MENU CARD
================================================== */

.menu-card {
    display: block;

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 25px;

    text-decoration: none;

    color: #111827;

    transition:
        transform 0.25s ease,
        background 0.25s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;

    animation:
        cardUp 0.6s ease-out both;
}


.menu-card:nth-child(1) {
    animation-delay: 0.1s;
}

.menu-card:nth-child(2) {
    animation-delay: 0.2s;
}

.menu-card:nth-child(3) {
    animation-delay: 0.3s;
}

.menu-card:nth-child(4) {
    animation-delay: 0.4s;
}

.menu-card:nth-child(5) {
    animation-delay: 0.5s;
}

.menu-card:nth-child(6) {
    animation-delay: 0.6s;
}


.menu-card:hover {
    background: #ffffff;

    border-color: #d1d5db;

    transform: translateY(-5px);

    box-shadow:
        0 10px 22px rgba(0, 0, 0, 0.07);
}


.menu-card-icon {
    width: 46px;
    height: 46px;

    background: #e5e7eb;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 20px;

    margin-bottom: 15px;

    transition:
        background 0.25s ease,
        transform 0.25s ease;
}


.menu-card:hover .menu-card-icon {
    background: #d1d5db;

    transform: translateY(-3px);
}


.menu-card h3 {
    font-size: 17px;

    margin-bottom: 8px;
}


.menu-card p {
    font-size: 13px;

    color: #64748b;

    line-height: 1.5;
}


/* ==================================================
   PROMO
================================================== */

.promo {
    background: #111827;

    color: #ffffff;

    border-radius: 18px;

    padding: 35px;

    box-shadow:
        0 6px 20px rgba(0, 0, 0, 0.08);

    animation: sectionUp 0.9s ease-out;
}


.promo h2 {
    font-size: 25px;

    margin-bottom: 10px;
}


.promo p {
    color: #d1d5db;

    font-size: 15px;

    line-height: 1.7;
}


/* ==================================================
   ANIMASI NAVBAR
================================================== */

@keyframes navbarDown {
    from {
        opacity: 0;

        transform:
            translateY(-25px);
    }

    to {
        opacity: 1;

        transform:
            translateY(0);
    }
}


/* ==================================================
   ANIMASI LOGO
================================================== */

@keyframes logoSlide {
    from {
        opacity: 0;

        transform:
            translateX(-20px);
    }

    to {
        opacity: 1;

        transform:
            translateX(0);
    }
}


/* ==================================================
   ANIMASI MENU
================================================== */

@keyframes menuFade {
    from {
        opacity: 0;

        transform:
            translateX(20px);
    }

    to {
        opacity: 1;

        transform:
            translateX(0);
    }
}


/* ==================================================
   ANIMASI CONTENT
================================================== */

@keyframes contentFade {
    from {
        opacity: 0;

        transform:
            translateY(15px);
    }

    to {
        opacity: 1;

        transform:
            translateY(0);
    }
}


/* ==================================================
   ANIMASI CARD
================================================== */

@keyframes cardUp {
    from {
        opacity: 0;

        transform:
            translateY(25px);
    }

    to {
        opacity: 1;

        transform:
            translateY(0);
    }
}


/* ==================================================
   ANIMASI SECTION
================================================== */

@keyframes sectionUp {
    from {
        opacity: 0;

        transform:
            translateY(20px);
    }

    to {
        opacity: 1;

        transform:
            translateY(0);
    }
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 1100px) {

    .menu-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media (max-width: 768px) {

    .navbar {
        padding: 16px 20px;

        flex-direction: column;

        align-items: center;

        gap: 14px;
    }


    .navbar .logo {
        margin-right: 0;
    }


    .navbar .nav-menu {
        width: 100%;

        justify-content: center;

        flex-wrap: wrap;

        gap: 13px;

        margin-left: 0;
    }


    .navbar .nav-menu a {
        font-size: 12px;
    }


    .container {
        padding:
            25px 15px 40px;
    }


    .welcome {
        padding: 25px;
    }


    .welcome h1 {
        font-size: 25px;
    }


    .welcome p {
        font-size: 14px;
    }


    .stats {
        grid-template-columns: 1fr;

        gap: 18px;
    }


    .stat-card {
        min-height: 150px;

        padding: 25px;
    }


    .stat-number {
        font-size: 40px;
    }


    .menu-section {
        padding: 25px 20px;
    }


    .menu-section h2 {
        font-size: 23px;
    }


    .menu-grid {
        grid-template-columns: 1fr;

        gap: 15px;
    }


    .promo {
        padding: 25px;
    }

}


@media (max-width: 400px) {

    .navbar .nav-menu {
        gap: 10px;
    }


    .navbar .nav-menu a {
        font-size: 11px;
    }


    .navbar .nav-menu a.logout {
        padding: 8px 14px;
    }

}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <a href="index.php" class="logo">
        <span class="logo-second">Second</span><span class="logo-space">Space</span>
    </a>

    <div class="nav-menu">

        <a href="index.php" class="active">
            Dashboard
        </a>

        <a href="produk.php">
            Produk
        </a>

        <a href="keranjang.php">
            Keranjang
        </a>

        <a href="pesanan.php">
            Pesanan
        </a>

        <a href="alamat.php">
            Alamat
        </a>

        <a href="contact.php">
            Contact
        </a>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     CONTENT
========================= -->

<div class="container">


    <!-- WELCOME -->

    <div class="welcome">

        <h1>
            Selamat Datang di SecondSpace
        </h1>

        <p>
            Temukan berbagai produk preloved pilihan dengan harga
            terjangkau dan kualitas terbaik.
        </p>

    </div>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats">


        <!-- TOTAL PRODUK -->

        <div class="stat-card">

            <div class="stat-icon">
                🛍️
            </div>

            <h3>
                Total Produk
            </h3>

            <div class="stat-number">
                <?= $totalProducts; ?>
            </div>

        </div>


        <!-- PESANAN -->

        <div class="stat-card">

            <div class="stat-icon">
                📦
            </div>

            <h3>
                Pesanan Saya
            </h3>

            <div class="stat-number">
                <?= $totalOrders; ?>
            </div>

        </div>


    </div>


    <!-- =========================
         MENU
    ========================= -->

    <div class="menu-section">

        <h2>
            Menu
        </h2>


        <div class="menu-grid">


            <a href="produk.php" class="menu-card">

                <div class="menu-card-icon">
                    🛍️
                </div>

                <h3>
                    Produk
                </h3>

                <p>
                    Lihat dan pilih berbagai produk preloved.
                </p>

            </a>


            <a href="keranjang.php" class="menu-card">

                <div class="menu-card-icon">
                    🛒
                </div>

                <h3>
                    Keranjang
                </h3>

                <p>
                    Lihat produk yang sudah kamu masukkan ke keranjang.
                </p>

            </a>


            <a href="pesanan.php" class="menu-card">

                <div class="menu-card-icon">
                    📦
                </div>

                <h3>
                    Pesanan
                </h3>

                <p>
                    Lihat riwayat dan status pesanan kamu.
                </p>

            </a>


            <a href="alamat.php" class="menu-card">

                <div class="menu-card-icon">
                    📍
                </div>

                <h3>
                    Alamat
                </h3>

                <p>
                    Kelola alamat pengiriman kamu.
                </p>

            </a>


            <a href="contact.php" class="menu-card">

                <div class="menu-card-icon">
                    💬
                </div>

                <h3>
                    Contact
                </h3>

                <p>
                    Hubungi kami jika membutuhkan bantuan.
                </p>

            </a>


            <a href="logout.php" class="menu-card">

                <div class="menu-card-icon">
                    🚪
                </div>

                <h3>
                    Logout
                </h3>

                <p>
                    Keluar dari akun SecondSpace.
                </p>

            </a>


        </div>

    </div>


    <!-- =========================
         PROMO
    ========================= -->

    <div class="promo">

        <h2>
            Second Life, New Style.
        </h2>

        <p>
            Berikan kesempatan kedua untuk barang berkualitas
            dan temukan gaya baru dengan harga yang lebih terjangkau.
        </p>

    </div>


</div>


</body>

</html>
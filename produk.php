<?php

session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// ======================================================
// FILTER KATEGORI
// ======================================================

$category = $_GET["category"] ?? "Semua";

$allowedCategories = [
    "Baju",
    "Kemeja",
    "Celana",
    "Sepatu"
];

if (
    $category !== "Semua" &&
    !in_array($category, $allowedCategories)
) {
    $category = "Semua";
}

// Menentukan apakah halaman dibuka melalui filter kategori
$isCategoryFilter = isset($_GET["category"]);

// ======================================================
// QUERY PRODUK
// ======================================================

if ($category === "Semua") {

    $stmt = $conn->prepare(
        "SELECT * FROM products ORDER BY id DESC"
    );

} else {

    $stmt = $conn->prepare(
        "SELECT * FROM products
         WHERE category = ?
         ORDER BY id DESC"
    );

    $stmt->bind_param("s", $category);
}

$stmt->execute();

$products = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Produk - SecondSpace PreLoved
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f6f8;

            color: #222;
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

            box-shadow:
                0 3px 15px
                rgba(0, 0, 0, 0.08);

            /*
             * Animasi navbar aktif ketika berpindah
             * dari halaman Dashboard ke Produk.
             */
            animation:
                navbarDown 0.5s ease-out;
        }

        /*
         * Ketika membuka filter kategori,
         * animasi navbar dimatikan.
         */
        .navbar.no-animation {
            animation: none;
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
        }

        .navbar .logo-second {
            color: #ffffff;
        }

        .navbar .logo-space {
            color: #9ff3d0;
        }

        .navbar .logo:hover {
            transform:
                translateY(-2px);

            opacity: 0.9;
        }

        /*
         * Logo hanya dianimasikan jika bukan
         * hasil klik filter kategori.
         */

        .navbar:not(.no-animation) .logo {
            animation:
                logoSlide 0.6s ease-out;
        }

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
           MENU NAVBAR
        ================================================== */

        .navbar-links {
            display: flex;

            align-items: center;

            gap: 24px;

            margin-left: auto;
        }

        /*
         * Menu dianimasikan ketika berpindah
         * antar halaman utama.
         */

        .navbar:not(.no-animation)
        .navbar-links {
            animation:
                menuFade 0.7s ease-out;
        }

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

        .navbar-links a {
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
           GARIS MENU
        ================================================== */

        .navbar-links a::after {
            content: "";

            position: absolute;

            left: 0;

            bottom: -9px;

            width: 100%;

            height: 3px;

            background: #ffffff;

            border-radius: 10px;

            transform:
                scaleX(0);

            transform-origin: left;

            transition:
                transform 0.35s ease;
        }

        /* ==================================================
           MENU AKTIF
        ================================================== */

        .navbar-links a.active {
            color: #ffffff;
        }

        .navbar-links a.active::after {
            transform:
                scaleX(1);
        }

        /* ==================================================
           HOVER MENU
        ================================================== */

        .navbar-links a:hover::after {
            transform:
                scaleX(1);
        }

        .navbar-links a:hover {
            color: #9ff3d0;

            transform:
                translateY(-2px);
        }

        /* ==================================================
           LOGOUT
        ================================================== */

        .navbar-links a.logout {
            background: #374151;

            color: #ffffff;

            padding: 9px 20px;

            border-radius: 10px;

            transition:
                background 0.25s ease,
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .navbar-links a.logout::after {
            display: none;
        }

        .navbar-links a.logout:hover {
            background: #4b5563;

            color: #ffffff;

            transform:
                translateY(-2px);

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.18);
        }

        /* ==================================================
           CONTAINER
        ================================================== */

        .container {
            max-width: 1200px;

            margin: 40px auto;

            padding: 0 20px;
        }

        /* ==================================================
           HEADER
        ================================================== */

        .header {
            margin-bottom: 28px;
        }

        .header h1 {
            margin:
                0 0 8px;

            font-size: 32px;

            letter-spacing: -0.5px;
        }

        .header p {
            margin: 0;

            color: #777;

            font-size: 15px;

            line-height: 1.6;
        }

        /* ==================================================
           FILTER
        ================================================== */

        .filter {
            display: flex;

            gap: 10px;

            flex-wrap: wrap;

            margin-bottom: 30px;
        }

        .filter a {
            text-decoration: none;

            color: #444;

            background: white;

            border: 1px solid #e2e2e2;

            padding: 10px 19px;

            border-radius: 30px;

            font-size: 14px;

            transition:
                0.2s ease,
                transform 0.2s ease;
        }

        .filter a:hover {
            background: #111;

            color: white;

            border-color: #111;

            transform:
                translateY(-1px);
        }

        .filter .active {
            background: #111;

            color: white;

            border-color: #111;

            box-shadow:
                0 4px 12px
                rgba(0, 0, 0, 0.12);
        }

        /* ==================================================
           PRODUCT GRID
        ================================================== */

        .product-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 22px;

            width: 100%;
        }

        /* ==================================================
           PRODUCT CARD
        ================================================== */

        .product-card {
            background: white;

            border-radius: 16px;

            overflow: hidden;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.06);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;

            min-width: 0;

            display: flex;

            flex-direction: column;
        }

        .product-card:hover {
            transform:
                translateY(-6px);

            box-shadow:
                0 14px 32px
                rgba(0, 0, 0, 0.12);
        }

        /* ==================================================
           PRODUCT IMAGE
        ================================================== */

        .product-image {
            width: 100%;

            height: 255px;

            background:
                linear-gradient(
                    135deg,
                    #f7f7f7,
                    #eeeeee
                );

            display: flex;

            justify-content: center;

            align-items: center;

            overflow: hidden;

            position: relative;
        }

        .product-image img {
            width: 100%;

            height: 100%;

            object-fit: contain;

            padding: 14px;

            display: block;

            transition:
                transform 0.35s ease;
        }

        .product-card:hover
        .product-image img {
            transform:
                scale(1.04);
        }

        .image-placeholder {
            font-size: 64px;

            opacity: 0.55;
        }

        /* ==================================================
           CATEGORY BADGE
        ================================================== */

        .category-badge {
            position: absolute;

            top: 13px;

            left: 13px;

            background:
                rgba(
                    17,
                    17,
                    17,
                    0.88
                );

            color: white;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: bold;

            z-index: 2;
        }

        /* ==================================================
           PRODUCT INFO
        ================================================== */

        .product-info {
            padding: 18px;

            flex: 1;

            display: flex;

            flex-direction: column;
        }

        .category-small {
            color: #888;

            font-size: 12px;

            margin-bottom: 7px;

            text-transform: uppercase;

            letter-spacing: 0.6px;

            font-weight: bold;
        }

        .product-name {
            font-size: 17px;

            font-weight: bold;

            margin-bottom: 9px;

            line-height: 1.35;

            min-height: 46px;
        }

        .price {
            font-size: 20px;

            font-weight: bold;

            margin-bottom: 12px;

            color: #111;
        }

        .product-meta {
            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 8px;

            margin-bottom: 17px;

            flex-wrap: wrap;
        }

        .condition {
            color: #555;

            font-size: 12px;

            background: #f5f5f5;

            padding: 6px 9px;

            border-radius: 6px;
        }

        .stock {
            font-size: 12px;

            color: #555;
        }

        .stock.available {
            color: #15803d;

            font-weight: bold;
        }

        .stock.empty-stock {
            color: #b91c1c;

            font-weight: bold;
        }

        /* ==================================================
           BUTTON DETAIL
        ================================================== */

        .detail-button {
            display: block;

            text-align: center;

            text-decoration: none;

            background: #111;

            color: white;

            padding: 12px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: bold;

            margin-top: auto;

            transition:
                0.2s ease,
                transform 0.2s ease;
        }

        .detail-button:hover {
            background: #333;

            transform:
                translateY(-1px);
        }

        /* ==================================================
           EMPTY
        ================================================== */

        .empty {
            background: white;

            padding: 60px 30px;

            border-radius: 16px;

            text-align: center;

            color: #777;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }

        .empty-icon {
            font-size: 55px;

            margin-bottom: 15px;
        }

        .empty h3 {
            margin:
                0 0 8px;

            color: #222;
        }

        .empty p {
            margin: 0;

            font-size: 14px;
        }

        /* ==================================================
           TABLET
        ================================================== */

        @media (max-width: 1000px) {

            .product-grid {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

        }

        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 750px) {

            .navbar {
                flex-direction: column;

                gap: 15px;

                padding:
                    15px 20px;
            }

            .navbar .logo {
                margin-right: 0;
            }

            .navbar-links {
                flex-wrap: wrap;

                justify-content: center;

                gap: 12px;

                margin-left: 0;
            }

            .product-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 15px;
            }

            .product-image {
                height: 220px;
            }

            .product-info {
                padding: 14px;
            }

            .product-name {
                font-size: 15px;

                min-height: 41px;
            }

            .price {
                font-size: 18px;
            }

        }

        /* ==================================================
           HP KECIL
        ================================================== */

        @media (max-width: 500px) {

            .container {
                margin:
                    28px auto;
            }

            .header h1 {
                font-size: 27px;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .product-image {
                height: 280px;
            }

            .product-name {
                font-size: 17px;

                min-height: auto;
            }

            .price {
                font-size: 20px;
            }

        }

    </style>

</head>

<body>

<!-- ======================================================
     NAVBAR
====================================================== -->

<div class="navbar <?= $isCategoryFilter ? 'no-animation' : '' ?>">

    <a href="index.php" class="logo">

        <span class="logo-second">
            Second
        </span><span class="logo-space">Space</span>

    </a>

    <div class="navbar-links">

        <a href="index.php">
            Dashboard
        </a>

        <a
            href="produk.php"
            class="active"
        >
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

        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>

<!-- ======================================================
     CONTENT
====================================================== -->

<div class="container">

    <div class="header">

        <h1>
            Produk PreLoved
        </h1>

        <p>
            Temukan fashion preloved berkualitas
            dengan harga terjangkau.
        </p>

    </div>

    <!-- ==================================================
         FILTER KATEGORI
    ================================================== -->

    <div class="filter">

        <a
            href="produk.php?category=Semua"
            class="<?= $category === 'Semua' ? 'active' : '' ?>"
        >
            Semua
        </a>

        <a
            href="produk.php?category=Baju"
            class="<?= $category === 'Baju' ? 'active' : '' ?>"
        >
            Baju
        </a>

        <a
            href="produk.php?category=Kemeja"
            class="<?= $category === 'Kemeja' ? 'active' : '' ?>"
        >
            Kemeja
        </a>

        <a
            href="produk.php?category=Celana"
            class="<?= $category === 'Celana' ? 'active' : '' ?>"
        >
            Celana
        </a>

        <a
            href="produk.php?category=Sepatu"
            class="<?= $category === 'Sepatu' ? 'active' : '' ?>"
        >
            Sepatu
        </a>

    </div>

    <!-- ==================================================
         DAFTAR PRODUK
    ================================================== -->

    <?php if ($products->num_rows > 0): ?>

        <div class="product-grid">

            <?php while ($product = $products->fetch_assoc()): ?>

                <div class="product-card">

                    <!-- FOTO PRODUK -->

                    <div class="product-image">

                        <div class="category-badge">

                            <?= htmlspecialchars(
                                $product["category"]
                            ) ?>

                        </div>

                        <?php if (!empty($product["image"])): ?>

                            <img
                                src="images/<?= htmlspecialchars(
                                    $product["image"]
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $product["name"]
                                ) ?>"
                            >

                        <?php else: ?>

                            <span class="image-placeholder">
                                🛍️
                            </span>

                        <?php endif; ?>

                    </div>

                    <!-- INFORMASI PRODUK -->

                    <div class="product-info">

                        <div class="category-small">

                            <?= htmlspecialchars(
                                $product["category"]
                            ) ?>

                        </div>

                        <div class="product-name">

                            <?= htmlspecialchars(
                                $product["name"]
                            ) ?>

                        </div>

                        <div class="price">

                            Rp
                            <?= number_format(
                                $product["price"],
                                0,
                                ",",
                                "."
                            ) ?>

                        </div>

                        <div class="product-meta">

                            <div class="condition">

                                <?= htmlspecialchars(
                                    $product["condition_text"]
                                ) ?>

                            </div>

                            <?php if (
                                (int) $product["stock"] > 0
                            ): ?>

                                <div class="stock available">

                                    Stok:
                                    <?= (int) $product["stock"] ?>

                                </div>

                            <?php else: ?>

                                <div class="stock empty-stock">

                                    Stok Habis

                                </div>

                            <?php endif; ?>

                        </div>

                        <a
                            class="detail-button"
                            href="detail.php?id=<?= $product["id"] ?>"
                        >
                            Lihat Detail
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <div class="empty-icon">
                🛍️
            </div>

            <h3>
                Produk tidak ditemukan
            </h3>

            <p>
                Belum ada produk pada kategori ini.
            </p>

        </div>

    <?php endif; ?>

</div>

</body>

</html>
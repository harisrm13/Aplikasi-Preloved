<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: produk.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT *
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: produk.php");
    exit;
}

$image = !empty($product["image"])
    ? "images/" . htmlspecialchars($product["image"])
    : "";

$stock = (int) $product["stock"];

$conditionClass = strtolower($product["condition_text"]);

$conditionLabel = htmlspecialchars($product["condition_text"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($product["name"]) ?> - SecondSpace PreLoved
    </title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f6f7f9;
            color: #111827;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #111827;
            color: white;
            padding: 16px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }

        .logo {
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .nav-menu a:hover {
            color: #d1d5db;
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 86%;
            max-width: 1200px;
            margin: 40px auto 70px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 25px;
            transition: 0.2s;
        }

        .back-link:hover {
            color: #111827;
            transform: translateX(-3px);
        }

        /* =========================
           DETAIL CARD
        ========================= */

        .detail-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 45px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.07);
        }

        /* =========================
           IMAGE
        ========================= */

        .image-section {
            position: relative;
        }

        .image-box {
            width: 100%;
            height: 500px;
            background: #f3f4f6;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .image-box:hover img {
            transform: scale(1.04);
        }

        .image-placeholder {
            font-size: 80px;
        }

        /* =========================
           PRODUCT INFO
        ========================= */

        .info-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 10px 5px;
        }

        .category-badge {
            display: inline-block;
            width: fit-content;
            background: #f3f4f6;
            color: #374151;
            padding: 7px 13px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .product-name {
            font-size: 34px;
            line-height: 1.2;
            margin-bottom: 15px;
            color: #111827;
        }

        .price {
            font-size: 30px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 22px;
        }

        .info-row {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 13px;
            font-size: 14px;
        }

        .info-label {
            color: #6b7280;
            width: 90px;
        }

        .info-value {
            font-weight: 700;
            color: #111827;
        }

        .stock-available {
            color: #15803d;
        }

        .stock-empty {
            color: #dc2626;
        }

        .divider {
            width: 100%;
            height: 1px;
            background: #e5e7eb;
            margin: 22px 0;
        }

        .description-title {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 9px;
        }

        .description {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        /* =========================
           QUANTITY
        ========================= */

        .quantity-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .quantity-input {
            width: 90px;
            height: 43px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 0 12px;
            font-size: 15px;
            outline: none;
            margin-bottom: 18px;
        }

        .quantity-input:focus {
            border-color: #111827;
        }

        /* =========================
           BUTTON
        ========================= */

        .btn-cart {
            width: 100%;
            border: none;
            background: #111827;
            color: white;
            padding: 15px 20px;
            border-radius: 13px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.25s;
        }

        .btn-cart:hover {
            background: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.16);
        }

        .btn-disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .btn-disabled:hover {
            background: #d1d5db;
            transform: none;
            box-shadow: none;
        }

        /* =========================
           NOTE
        ========================= */

        .safe-note {
            margin-top: 16px;
            padding: 13px 15px;
            background: #f9fafb;
            border-radius: 12px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.6;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .detail-card {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .image-box {
                height: 450px;
            }

            .product-name {
                font-size: 30px;
            }

            .price {
                font-size: 27px;
            }
        }

        @media (max-width: 600px) {

            .navbar {
                padding: 14px 5%;
            }

            .logo {
                font-size: 18px;
            }

            .nav-menu {
                gap: 12px;
            }

            .nav-menu a {
                font-size: 12px;
            }

            .container {
                width: 92%;
                margin-top: 25px;
            }

            .detail-card {
                padding: 16px;
                border-radius: 18px;
                gap: 22px;
            }

            .image-box {
                height: 350px;
                border-radius: 15px;
            }

            .product-name {
                font-size: 25px;
            }

            .price {
                font-size: 25px;
            }

            .info-section {
                padding: 5px 2px;
            }

            .back-link {
                margin-bottom: 18px;
            }
        }

        @media (max-width: 400px) {

            .nav-menu a:nth-child(2) {
                display: none;
            }

            .image-box {
                height: 300px;
            }

            .product-name {
                font-size: 23px;
            }

            .price {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <div class="logo">
        SecondSpace
    </div>

    <div class="nav-menu">
        <a href="index.php">Home</a>
        <a href="produk.php">Produk</a>
        <a href="keranjang.php">Keranjang</a>
        <a href="logout.php">Logout</a>
    </div>

</nav>


<!-- =========================
     CONTENT
========================= -->

<div class="container">

    <a href="produk.php" class="back-link">
        ← Kembali ke Produk
    </a>

    <div class="detail-card">

        <!-- IMAGE -->

        <div class="image-section">

            <div class="image-box">

                <?php if ($image): ?>

                    <img
                        src="<?= $image ?>"
                        alt="<?= htmlspecialchars($product["name"]) ?>"
                    >

                <?php else: ?>

                    <div class="image-placeholder">
                        🛍️
                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- INFO -->

        <div class="info-section">

            <span class="category-badge">
                <?= htmlspecialchars($product["category"]) ?>
            </span>

            <h1 class="product-name">
                <?= htmlspecialchars($product["name"]) ?>
            </h1>

            <div class="price">
                Rp <?= number_format($product["price"], 0, ",", ".") ?>
            </div>


            <div class="info-row">

                <span class="info-label">
                    Kondisi
                </span>

                <span class="info-value">
                    <?= $conditionLabel ?>
                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    Stok
                </span>

                <span class="info-value <?= $stock > 0 ? 'stock-available' : 'stock-empty' ?>">

                    <?php if ($stock > 0): ?>

                        Tersedia (<?= $stock ?>)

                    <?php else: ?>

                        Stok Habis

                    <?php endif; ?>

                </span>

            </div>


            <div class="divider"></div>


            <div class="description-title">
                Deskripsi Produk
            </div>

            <div class="description">
                <?= nl2br(htmlspecialchars($product["description"] ?? "Tidak ada deskripsi produk.")) ?>
            </div>


            <?php if ($stock > 0): ?>

                <form action="keranjang.php" method="POST">

                    <input
                        type="hidden"
                        name="action"
                        value="add"
                    >

                    <input
                        type="hidden"
                        name="product_id"
                        value="<?= $product["id"] ?>"
                    >

                    <div class="quantity-title">
                        Jumlah
                    </div>

                    <input
                        class="quantity-input"
                        type="number"
                        name="quantity"
                        value="1"
                        min="1"
                        max="<?= $stock ?>"
                    >

                    <button
                        type="submit"
                        class="btn-cart"
                    >
                        🛒 Tambah ke Keranjang
                    </button>

                </form>

            <?php else: ?>

                <button
                    class="btn-cart btn-disabled"
                    disabled
                >
                    Stok Habis
                </button>

            <?php endif; ?>


            <div class="safe-note">
                🔒 Produk ini merupakan barang preloved.
                Pastikan kamu sudah membaca kondisi dan deskripsi produk sebelum membeli.
            </div>

        </div>

    </div>

</div>

</body>
</html>
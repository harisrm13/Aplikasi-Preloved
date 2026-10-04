<?php

session_start();
require_once "db.php";

/*
|--------------------------------------------------------------------------
| Cek keranjang
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["cart"])
    || empty($_SESSION["cart"])
) {
    header("Location: keranjang.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Ambil data user
|--------------------------------------------------------------------------
*/

$userId = $_SESSION["user_id"] ?? 0;

if ($userId <= 0) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Hitung subtotal
|--------------------------------------------------------------------------
*/

$subtotal = 0;

foreach ($_SESSION["cart"] as $item) {
    $subtotal += $item["price"] * $item["quantity"];
}


/*
|--------------------------------------------------------------------------
| Ongkir
|--------------------------------------------------------------------------
*/

$shipping = 15000;


/*
|--------------------------------------------------------------------------
| Total
|--------------------------------------------------------------------------
*/

$total = $subtotal + $shipping;

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
        Checkout - SecondSpace PreLoved
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


        .back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }


        .back:hover {
            color: #111827;
            transform: translateX(-3px);
        }


        /* =========================
           HEADER
        ========================= */

        .header {
            margin-bottom: 28px;
        }


        .header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: #111827;
        }


        .header p {
            color: #6b7280;
            font-size: 14px;
        }


        /* =========================
           CHECKOUT GRID
        ========================= */

        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.75fr);
            gap: 25px;
            align-items: start;
        }


        /* =========================
           CARD
        ========================= */

        .card {
            background: white;
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        }


        .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }


        .card-title .icon {
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }


        .card-title h2 {
            font-size: 19px;
            color: #111827;
        }


        .card-title p {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 3px;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {
            margin-bottom: 18px;
        }


        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }


        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d1d5db;
            border-radius: 11px;
            background: #fff;
            color: #111827;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }


        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #111827;
            box-shadow: 0 0 0 3px rgba(17,24,39,0.07);
        }


        .form-group textarea {
            min-height: 125px;
            resize: vertical;
            line-height: 1.6;
        }


        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #9ca3af;
        }


        /* =========================
           FORM GRID
        ========================= */

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }


        /* =========================
           BUTTON
        ========================= */

        .btn {
            width: 100%;
            border: none;
            border-radius: 13px;
            padding: 15px;
            margin-top: 8px;
            background: #111827;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.25s;
        }


        .btn:hover {
            background: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }


        /* =========================
           ORDER SUMMARY
        ========================= */

        .summary-card {
            position: sticky;
            top: 95px;
        }


        .product-list {
            margin-bottom: 20px;
        }


        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eef0f2;
        }


        .product-item:first-child {
            padding-top: 0;
        }


        .product-info {
            min-width: 0;
        }


        .product-name {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }


        .product-quantity {
            color: #6b7280;
            font-size: 12px;
        }


        .product-price {
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            color: #111827;
        }


        /* =========================
           SUMMARY
        ========================= */

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
        }


        .summary-row span:first-child {
            color: #6b7280;
        }


        .summary-row span:last-child {
            font-weight: 700;
            color: #374151;
            text-align: right;
        }


        .summary-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 20px 0;
        }


        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }


        .summary-total span:first-child {
            font-size: 15px;
            font-weight: 700;
        }


        .summary-total span:last-child {
            font-size: 23px;
            font-weight: 800;
        }


        /* =========================
           INFO BOX
        ========================= */

        .info-box {
            margin-top: 20px;
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

            .checkout-grid {
                grid-template-columns: 1fr;
            }


            .summary-card {
                position: static;
            }

        }


        @media (max-width: 650px) {

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


            .header h1 {
                font-size: 27px;
            }


            .card {
                padding: 20px;
                border-radius: 18px;
            }


            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }


            .product-item {
                align-items: flex-start;
            }


            .product-price {
                font-size: 12px;
            }


            .summary-total span:last-child {
                font-size: 20px;
            }

        }


        @media (max-width: 400px) {

            .nav-menu a:nth-child(2) {
                display: none;
            }


            .card {
                padding: 17px;
            }


            .header h1 {
                font-size: 24px;
            }


            .product-name {
                font-size: 13px;
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

        <a href="index.php">
            Home
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

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     CONTENT
========================= -->

<div class="container">


    <a
        href="keranjang.php"
        class="back"
    >
        ← Kembali ke Keranjang
    </a>


    <div class="header">

        <h1>
            Checkout
        </h1>

        <p>
            Lengkapi alamat pengiriman sebelum melanjutkan pembayaran.
        </p>

    </div>


    <div class="checkout-grid">


        <!-- =========================
             ALAMAT
        ========================= -->

        <div class="card">

            <div class="card-title">

                <div class="icon">
                    📍
                </div>

                <div>

                    <h2>
                        Alamat Pengiriman
                    </h2>

                    <p>
                        Pastikan data penerima sudah benar.
                    </p>

                </div>

            </div>


            <form
                action="pembayaran.php"
                method="POST"
            >


                <div class="form-group">

                    <label>
                        Nama Penerima
                    </label>

                    <input
                        type="text"
                        name="recipient_name"
                        placeholder="Masukkan nama penerima"
                        autocomplete="name"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Nomor HP
                    </label>

                    <input
                        type="tel"
                        name="phone"
                        placeholder="Contoh: 081234567890"
                        autocomplete="tel"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Alamat Lengkap
                    </label>

                    <textarea
                        name="address"
                        placeholder="Nama jalan, nomor rumah, RT/RW, kecamatan, dan detail lainnya"
                        autocomplete="street-address"
                        required
                    ></textarea>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Provinsi
                        </label>

                        <input
                            type="text"
                            name="province"
                            placeholder="Contoh: DKI Jakarta"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Kota / Kabupaten
                        </label>

                        <input
                            type="text"
                            name="city"
                            placeholder="Contoh: Jakarta Timur"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Kode Pos
                    </label>

                    <input
                        type="text"
                        name="postal_code"
                        placeholder="Contoh: 13450"
                        inputmode="numeric"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn"
                >
                    Lanjut ke Pembayaran →
                </button>


            </form>

        </div>


        <!-- =========================
             RINGKASAN
        ========================= -->

        <div class="card summary-card">

            <div class="card-title">

                <div class="icon">
                    🛍️
                </div>

                <div>

                    <h2>
                        Ringkasan Pesanan
                    </h2>

                    <p>
                        Produk yang akan dibeli.
                    </p>

                </div>

            </div>


            <div class="product-list">

                <?php foreach ($_SESSION["cart"] as $item): ?>

                    <div class="product-item">

                        <div class="product-info">

                            <div class="product-name">

                                <?= htmlspecialchars(
                                    $item["name"]
                                ) ?>

                            </div>


                            <div class="product-quantity">

                                <?= $item["quantity"] ?>
                                ×
                                Rp
                                <?= number_format(
                                    $item["price"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </div>

                        </div>


                        <div class="product-price">

                            Rp
                            <?= number_format(
                                $item["price"] * $item["quantity"],
                                0,
                                ",",
                                "."
                            ) ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


            <div class="summary-row">

                <span>
                    Subtotal
                </span>

                <span>
                    Rp
                    <?= number_format(
                        $subtotal,
                        0,
                        ",",
                        "."
                    ) ?>
                </span>

            </div>


            <div class="summary-row">

                <span>
                    Ongkir
                </span>

                <span>
                    Rp
                    <?= number_format(
                        $shipping,
                        0,
                        ",",
                        "."
                    ) ?>
                </span>

            </div>


            <div class="summary-divider"></div>


            <div class="summary-total">

                <span>
                    Total
                </span>

                <span>
                    Rp
                    <?= number_format(
                        $total,
                        0,
                        ",",
                        "."
                    ) ?>
                </span>

            </div>


            <div class="info-box">

                🔒 Data alamat digunakan untuk proses
                pengiriman pesanan kamu.

            </div>

        </div>


    </div>

</div>


</body>

</html>
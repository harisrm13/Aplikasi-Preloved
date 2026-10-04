<?php

session_start();

require_once "db.php";

/*
|--------------------------------------------------------------------------
| Cek Login
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Cek Keranjang
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
| Cek Data Checkout
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil Data Alamat
|--------------------------------------------------------------------------
*/
$recipientName = trim($_POST["recipient_name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$province = trim($_POST["province"] ?? "");
$city = trim($_POST["city"] ?? "");
$postalCode = trim($_POST["postal_code"] ?? "");

/*
|--------------------------------------------------------------------------
| Validasi
|--------------------------------------------------------------------------
*/
if (
    $recipientName === ""
    || $phone === ""
    || $address === ""
    || $province === ""
    || $city === ""
    || $postalCode === ""
) {
    die("Data alamat belum lengkap. Silakan kembali ke halaman checkout.");
}

/*
|--------------------------------------------------------------------------
| Simpan sementara data checkout ke Session
|--------------------------------------------------------------------------
*/
$_SESSION["checkout"] = [
    "recipient_name" => $recipientName,
    "phone" => $phone,
    "address" => $address,
    "province" => $province,
    "city" => $city,
    "postal_code" => $postalCode
];

/*
|--------------------------------------------------------------------------
| Hitung Total
|--------------------------------------------------------------------------
*/
$subtotal = 0;

foreach ($_SESSION["cart"] as $item) {
    $subtotal += $item["price"] * $item["quantity"];
}

$shipping = 15000;
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

    <title>Pembayaran - SecondSpace PreLoved</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f4f2;
            color: #222;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;

            background: #171717;
            color: white;

            padding: 18px 50px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .brand span {
            color: #cfcfcf;
            font-weight: normal;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-menu a {
            color: #ddd;
            text-decoration: none;
            font-size: 14px;

            transition: 0.2s;
        }

        .nav-menu a:hover {
            color: white;
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 1150px;
            margin: 40px auto;
            padding: 0 20px 60px;
        }

        /* =========================
           BACK
        ========================= */

        .back {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            color: #444;
            text-decoration: none;

            font-size: 14px;
            font-weight: 600;

            margin-bottom: 25px;

            transition: 0.2s;
        }

        .back:hover {
            color: #000;
            transform: translateX(-3px);
        }

        /* =========================
           HEADER
        ========================= */

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 34px;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #777;
            font-size: 15px;
        }

        /* =========================
           STEPPER
        ========================= */

        .steps {
            display: flex;
            align-items: center;
            margin-bottom: 35px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 9px;

            font-size: 13px;
            color: #999;
        }

        .step.active {
            color: #222;
            font-weight: bold;
        }

        .step-number {
            width: 30px;
            height: 30px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #ddd;
            color: #666;

            font-size: 13px;
            font-weight: bold;
        }

        .step.active .step-number {
            background: #222;
            color: white;
        }

        .step-line {
            width: 55px;
            height: 1px;
            background: #ddd;
            margin: 0 12px;
        }

        /* =========================
           GRID
        ========================= */

        .payment-grid {
            display: grid;
            grid-template-columns: 1.35fr 0.9fr;
            gap: 25px;
            align-items: start;
        }

        /* =========================
           CARD
        ========================= */

        .card {
            background: white;
            border-radius: 20px;

            padding: 28px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.06);

            border: 1px solid #eee;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 12px;

            margin-bottom: 24px;
        }

        .card-icon {
            width: 42px;
            height: 42px;

            background: #f0f0ee;

            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 20px;
        }

        .card-title h2 {
            font-size: 21px;
        }

        .card-title p {
            color: #888;
            font-size: 13px;
            margin-top: 3px;
        }

        /* =========================
           PAYMENT OPTION
        ========================= */

        .payment-option {
            position: relative;

            display: flex;
            align-items: center;
            gap: 15px;

            border: 1.5px solid #e2e2e2;
            border-radius: 15px;

            padding: 18px;

            margin-bottom: 14px;

            cursor: pointer;

            transition: 0.25s;
        }

        .payment-option:hover {
            border-color: #777;
            background: #fafafa;

            transform: translateY(-2px);

            box-shadow:
                0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .payment-option input {
            width: 18px;
            height: 18px;

            accent-color: #222;

            flex-shrink: 0;
        }

        .payment-icon {
            width: 44px;
            height: 44px;

            border-radius: 12px;

            background: #f3f3f1;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 21px;

            flex-shrink: 0;
        }

        .payment-content {
            flex: 1;
        }

        .payment-title {
            display: block;

            font-size: 15px;
            font-weight: bold;

            margin-bottom: 5px;
        }

        .payment-description {
            display: block;

            color: #888;
            font-size: 13px;
            line-height: 1.5;
        }

        .payment-arrow {
            color: #aaa;
            font-size: 18px;
        }

        /* =========================
           ADDRESS
        ========================= */

        .address-box {
            background: #f6f6f4;

            border: 1px solid #e8e8e5;

            border-radius: 15px;

            padding: 18px;

            margin-bottom: 25px;

            line-height: 1.7;

            font-size: 14px;
        }

        .address-label {
            font-size: 12px;
            color: #888;

            text-transform: uppercase;
            letter-spacing: 0.5px;

            margin-bottom: 7px;
        }

        .address-name {
            font-size: 16px;
            font-weight: bold;

            margin-bottom: 3px;
        }

        .address-phone {
            color: #555;
            margin-bottom: 7px;
        }

        .address-detail {
            color: #555;
        }

        /* =========================
           SUMMARY
        ========================= */

        .summary-row {
            display: flex;
            justify-content: space-between;

            margin-bottom: 16px;

            font-size: 14px;
            color: #666;
        }

        .summary-row span:last-child {
            color: #333;
            font-weight: 600;
        }

        .summary-total {
            border-top: 1px solid #e5e5e5;

            padding-top: 20px;
            margin-top: 20px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-total span:first-child {
            font-size: 16px;
            font-weight: bold;
        }

        .summary-total span:last-child {
            font-size: 24px;
            font-weight: bold;
            color: #111;
        }

        /* =========================
           INFO
        ========================= */

        .secure-box {
            display: flex;
            gap: 12px;

            background: #fafafa;

            border: 1px solid #eee;

            border-radius: 12px;

            padding: 14px;

            margin-top: 20px;

            font-size: 12px;
            color: #777;

            line-height: 1.5;
        }

        .secure-icon {
            font-size: 18px;
        }

        /* =========================
           BUTTON
        ========================= */

        .btn {
            width: 100%;

            border: none;
            border-radius: 13px;

            padding: 16px;

            margin-top: 22px;

            background: #1c1c1c;
            color: white;

            font-size: 15px;
            font-weight: bold;

            cursor: pointer;

            transition: 0.25s;
        }

        .btn:hover {
            background: #3a3a3a;

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(0, 0, 0, 0.15);
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 850px) {

            .navbar {
                padding: 16px 20px;
            }

            .nav-menu {
                gap: 12px;
            }

            .nav-menu a {
                font-size: 12px;
            }

            .payment-grid {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 28px;
            }

        }

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;
                gap: 12px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                margin-top: 25px;
            }

            .steps {
                overflow-x: auto;
                padding-bottom: 5px;
            }

            .step {
                white-space: nowrap;
            }

            .step-line {
                width: 25px;
                margin: 0 7px;
            }

            .card {
                padding: 20px;
                border-radius: 16px;
            }

            .payment-option {
                padding: 14px;
            }

            .payment-icon {
                width: 38px;
                height: 38px;
            }

            .summary-total span:last-child {
                font-size: 21px;
            }

        }

    </style>

</head>

<body>

    <!-- NAVBAR -->

    <nav class="navbar">

        <div class="brand">
            SecondSpace <span>PreLoved</span>
        </div>

        <div class="nav-menu">

            <a href="index.php">
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

            <a href="logout.php">
                Logout
            </a>

        </div>

    </nav>


    <div class="container">

        <a
            href="checkout.php"
            class="back"
        >
            ← Kembali ke Checkout
        </a>


        <!-- HEADER -->

        <div class="page-header">

            <h1>
                Pembayaran
            </h1>

            <p>
                Pilih metode pembayaran yang ingin digunakan.
            </p>

        </div>


        <!-- STEPPER -->

        <div class="steps">

            <div class="step active">

                <div class="step-number">
                    ✓
                </div>

                Checkout

            </div>

            <div class="step-line"></div>

            <div class="step active">

                <div class="step-number">
                    2
                </div>

                Pembayaran

            </div>

            <div class="step-line"></div>

            <div class="step">

                <div class="step-number">
                    3
                </div>

                Selesai

            </div>

        </div>


        <div class="payment-grid">


            <!-- =========================
                 METODE PEMBAYARAN
            ========================== -->

            <div class="card">

                <div class="card-title">

                    <div class="card-icon">
                        💳
                    </div>

                    <div>

                        <h2>
                            Metode Pembayaran
                        </h2>

                        <p>
                            Pilih salah satu metode pembayaran
                        </p>

                    </div>

                </div>


                <form
                    action="konfirmasi.php"
                    method="POST"
                >


                    <!-- TRANSFER BANK -->

                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="Transfer Bank"
                            required
                        >

                        <div class="payment-icon">
                            🏦
                        </div>

                        <div class="payment-content">

                            <span class="payment-title">
                                Transfer Bank
                            </span>

                            <span class="payment-description">
                                Pembayaran melalui transfer bank.
                            </span>

                        </div>

                        <div class="payment-arrow">
                            ›
                        </div>

                    </label>


                    <!-- E-WALLET -->

                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="E-Wallet"
                        >

                        <div class="payment-icon">
                            📱
                        </div>

                        <div class="payment-content">

                            <span class="payment-title">
                                E-Wallet
                            </span>

                            <span class="payment-description">
                                Pembayaran melalui e-wallet.
                            </span>

                        </div>

                        <div class="payment-arrow">
                            ›
                        </div>

                    </label>


                    <!-- COD -->

                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="COD"
                        >

                        <div class="payment-icon">
                            💵
                        </div>

                        <div class="payment-content">

                            <span class="payment-title">
                                COD
                            </span>

                            <span class="payment-description">
                                Bayar ketika barang diterima.
                            </span>

                        </div>

                        <div class="payment-arrow">
                            ›
                        </div>

                    </label>


                    <button
                        type="submit"
                        class="btn"
                    >
                        Lanjutkan Pembayaran →
                    </button>

                </form>

            </div>


            <!-- =========================
                 RINGKASAN
            ========================== -->

            <div class="card">

                <div class="card-title">

                    <div class="card-icon">
                        🛍️
                    </div>

                    <div>

                        <h2>
                            Ringkasan Pesanan
                        </h2>

                        <p>
                            Periksa kembali sebelum melanjutkan
                        </p>

                    </div>

                </div>


                <!-- ALAMAT -->

                <div class="address-box">

                    <div class="address-label">
                        Alamat Pengiriman
                    </div>

                    <div class="address-name">
                        <?= htmlspecialchars($recipientName) ?>
                    </div>

                    <div class="address-phone">
                        <?= htmlspecialchars($phone) ?>
                    </div>

                    <div class="address-detail">

                        <?= nl2br(
                            htmlspecialchars($address)
                        ) ?>

                        <br>

                        <?= htmlspecialchars($city) ?>,
                        <?= htmlspecialchars($province) ?>

                        <br>

                        <?= htmlspecialchars($postalCode) ?>

                    </div>

                </div>


                <!-- TOTAL -->

                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <span>
                        Rp <?= number_format(
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
                        Rp <?= number_format(
                            $shipping,
                            0,
                            ",",
                            "."
                        ) ?>
                    </span>

                </div>


                <div class="summary-total">

                    <span>
                        Total
                    </span>

                    <span>
                        Rp <?= number_format(
                            $total,
                            0,
                            ",",
                            "."
                        ) ?>
                    </span>

                </div>


                <div class="secure-box">

                    <div class="secure-icon">
                        🔒
                    </div>

                    <div>
                        Data checkout dan informasi pesanan
                        diproses secara aman melalui sistem
                        SecondSpace PreLoved.
                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>
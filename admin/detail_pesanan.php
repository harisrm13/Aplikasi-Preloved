<?php

require_once "auth_admin.php";
require_once "../db.php";

$order_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($order_id <= 0) {
    header("Location: pesanan.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| PROSES UPDATE PESANAN
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_status = trim($_POST["payment_status"] ?? "");
    $order_status = trim($_POST["order_status"] ?? "");
    $courier = trim($_POST["courier"] ?? "");
    $tracking_number = trim($_POST["tracking_number"] ?? "");

    $allowed_payment_status = [
        "Menunggu Pembayaran",
        "Menunggu Konfirmasi",
        "Dibayar",
        "Selesai"
    ];

    $allowed_order_status = [
        "Menunggu Pembayaran",
        "Diproses",
        "Dikirim",
        "Selesai",
        "Dibatalkan"
    ];

    $allowed_courier = [
        "",
        "JNE",
        "J&T Express",
        "SiCepat",
        "AnterAja",
        "POS Indonesia",
        "Gojek",
        "Grab"
    ];

    if (!in_array($payment_status, $allowed_payment_status, true)) {

        $error = "Status pembayaran tidak valid.";

    } elseif (!in_array($order_status, $allowed_order_status, true)) {

        $error = "Status pesanan tidak valid.";

    } elseif (!in_array($courier, $allowed_courier, true)) {

        $error = "Kurir tidak valid.";

    } elseif ($order_status === "Dikirim" && $tracking_number === "") {

        $error = "Nomor resi wajib diisi jika pesanan dikirim.";

    } else {

        $stmt = $conn->prepare("
            UPDATE orders
            SET
                payment_status = ?,
                order_status = ?,
                courier = ?,
                tracking_number = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssssi",
            $payment_status,
            $order_status,
            $courier,
            $tracking_number,
            $order_id
        );

        if ($stmt->execute()) {

            $success = "Data pesanan berhasil diperbarui.";

        } else {

            $error = "Gagal memperbarui data pesanan.";

        }

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA PESANAN
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        orders.*,
        users.name AS customer_name,
        users.username AS customer_username,
        users.email AS customer_email,
        addresses.recipient_name,
        addresses.phone,
        addresses.address,
        addresses.province,
        addresses.city,
        addresses.postal_code
    FROM orders
    INNER JOIN users
        ON orders.user_id = users.id
    INNER JOIN addresses
        ON orders.address_id = addresses.id
    WHERE orders.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();
$order = $result->fetch_assoc();

$stmt->close();

if (!$order) {
    header("Location: pesanan.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AMBIL ITEM PESANAN
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        order_items.*,
        products.image
    FROM order_items
    LEFT JOIN products
        ON order_items.product_id = products.id
    WHERE order_items.order_id = ?
    ORDER BY order_items.id ASC
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$items_result = $stmt->get_result();

$items = [];

while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Detail Pesanan - SecondSpace Admin</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
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

            box-shadow:
                0 3px 15px rgba(0, 0, 0, 0.08);

            animation:
                navbarDown 0.5s ease-out;
        }


        @keyframes navbarDown {

            from {
                opacity: 0;
                transform: translateY(-25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }


        /* ==================================================
           LOGO
        ================================================== */

        .logo {
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

            animation:
                logoSlide 0.6s ease-out;
        }


        .logo-second {
            color: #ffffff;
        }


        .logo-space {
            color: #9ff3d0;
        }


        .logo:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }


        @keyframes logoSlide {

            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }

        }


        /* ==================================================
           NAV MENU
        ================================================== */

        .nav-menu {
            display: flex;

            align-items: center;

            gap: 24px;

            margin-left: auto;

            animation:
                menuFade 0.7s ease-out;
        }


        @keyframes menuFade {

            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }

        }


        .nav-menu a {
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
           UNDERLINE
        ================================================== */

        .nav-menu a::after {

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

            transition:
                transform 0.35s ease;
        }


        .nav-menu a.active {
            color: #ffffff;
        }


        .nav-menu a.active::after {
            transform: scaleX(1);
        }


        .nav-menu a:hover {
            color: #9ff3d0;

            transform: translateY(-2px);
        }


        .nav-menu a:hover::after {
            transform: scaleX(1);
        }


        /* ==================================================
           LOGOUT
        ================================================== */

        .nav-menu a.logout {

            background: #374151;

            color: #ffffff;

            padding: 9px 20px;

            border-radius: 10px;

            transition:
                background 0.25s ease,
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }


        .nav-menu a.logout::after {
            display: none;
        }


        .nav-menu a.logout:hover {

            background: #4b5563;

            color: #ffffff;

            transform: translateY(-2px);

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.18);
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
           BACK BUTTON
        ================================================== */

        .back {

            display: inline-block;

            margin-bottom: 22px;

            color: #374151;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            transition:
                color 0.2s ease,
                transform 0.2s ease;
        }


        .back:hover {

            color: #111827;

            transform: translateX(-3px);
        }


        /* ==================================================
           TITLE
        ================================================== */

        .title {

            margin-bottom: 28px;

            animation:
                contentFade 0.6s ease-out;
        }


        @keyframes contentFade {

            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }


        .title h1 {

            font-size: 30px;

            margin-bottom: 7px;
        }


        .title p {

            color: #6b7280;

            font-size: 14px;
        }


        /* ==================================================
           ALERT
        ================================================== */

        .alert {

            padding: 14px 17px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        .alert.success {

            background: #ecfdf5;

            color: #047857;

            border:
                1px solid #a7f3d0;
        }


        .alert.error {

            background: #fef2f2;

            color: #b91c1c;

            border:
                1px solid #fecaca;
        }


        /* ==================================================
           GRID
        ================================================== */

        .grid {

            display: grid;

            grid-template-columns:
                1.5fr 1fr;

            gap: 22px;

            align-items: start;
        }


        /* ==================================================
           CARD
        ================================================== */

        .card {

            background: #ffffff;

            border-radius: 16px;

            padding: 24px;

            box-shadow:
                0 8px 30px rgba(15, 23, 42, 0.06);

            margin-bottom: 22px;

            animation:
                cardFade 0.7s ease-out;
        }


        @keyframes cardFade {

            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }


        .card h2 {

            font-size: 18px;

            margin-bottom: 20px;
        }


        /* ==================================================
           CUSTOMER
        ================================================== */

        .info-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 11px 0;

            border-bottom:
                1px solid #eef0f3;
        }


        .info-row:last-child {
            border-bottom: none;
        }


        .info-label {

            color: #6b7280;

            font-size: 14px;
        }


        .info-value {

            text-align: right;

            font-size: 14px;

            font-weight: 600;

            max-width: 65%;
        }


        /* ==================================================
           PRODUCTS
        ================================================== */

        .product-item {

            display: flex;

            gap: 15px;

            padding: 15px 0;

            border-bottom:
                1px solid #eef0f3;
        }


        .product-item:first-child {
            padding-top: 0;
        }


        .product-item:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }


        .product-image {

            width: 72px;

            height: 72px;

            border-radius: 10px;

            overflow: hidden;

            background: #f3f4f6;

            flex-shrink: 0;
        }


        .product-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;
        }


        .no-image {

            width: 100%;

            height: 100%;

            display: flex;

            justify-content: center;

            align-items: center;

            color: #9ca3af;

            font-size: 12px;
        }


        .product-info {
            flex: 1;
        }


        .product-name {

            font-weight: 700;

            margin-bottom: 6px;
        }


        .product-meta {

            color: #6b7280;

            font-size: 13px;

            line-height: 1.6;
        }


        .product-subtotal {

            font-weight: 700;

            white-space: nowrap;
        }


        /* ==================================================
           TOTAL
        ================================================== */

        .total-box {

            margin-top: 20px;

            padding-top: 18px;

            border-top:
                1px solid #e5e7eb;
        }


        .total-row {

            display: flex;

            justify-content: space-between;

            margin-bottom: 10px;

            font-size: 14px;
        }


        .total-row.grand {

            margin-top: 14px;

            padding-top: 14px;

            border-top:
                1px solid #e5e7eb;

            font-size: 18px;

            font-weight: 700;
        }


        /* ==================================================
           FORM
        ================================================== */

        .form-group {

            margin-bottom: 18px;
        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: 600;
        }


        select,
        input {

            width: 100%;

            padding: 12px 13px;

            border:
                1px solid #d1d5db;

            border-radius: 9px;

            font-size: 14px;

            outline: none;

            background: #ffffff;

            transition:
                border 0.2s ease,
                box-shadow 0.2s ease;
        }


        select:focus,
        input:focus {

            border-color: #111827;

            box-shadow:
                0 0 0 3px rgba(17,24,39,0.08);
        }


        .form-note {

            font-size: 12px;

            color: #6b7280;

            margin-top: 6px;

            line-height: 1.5;
        }


        .btn-update {

            width: 100%;

            border: none;

            background: #111827;

            color: #ffffff;

            padding: 13px 18px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .btn-update:hover {

            background: #374151;

            transform: translateY(-2px);

            box-shadow:
                0 6px 15px rgba(0,0,0,0.12);
        }


        .btn-back {

            display: block;

            text-align: center;

            margin-top: 12px;

            padding: 12px;

            border-radius: 9px;

            border:
                1px solid #d1d5db;

            text-decoration: none;

            color: #374151;

            font-size: 14px;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        .btn-back:hover {

            background: #f9fafb;

            transform: translateY(-1px);
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 900px) {

            .navbar {

                padding: 16px 20px;
            }


            .nav-menu {

                gap: 13px;
            }


            .nav-menu a {

                font-size: 12px;
            }


            .grid {

                grid-template-columns: 1fr;
            }


            .title h1 {

                font-size: 25px;
            }

        }


        @media (max-width: 650px) {

            .navbar {

                flex-direction: column;

                align-items: center;

                gap: 14px;
            }


            .navbar .logo {

                margin-right: 0;
            }


            .nav-menu {

                width: 100%;

                justify-content: center;

                flex-wrap: wrap;

                gap: 12px;

                margin-left: 0;
            }


            .nav-menu a {

                white-space: nowrap;
            }

        }


        @media (max-width: 550px) {

            .container {

                margin: 25px auto;

                padding: 0 15px;
            }


            .card {

                padding: 18px;
            }


            .info-row {

                display: block;
            }


            .info-value {

                text-align: left;

                max-width: 100%;

                margin-top: 5px;
            }


            .product-subtotal {

                font-size: 13px;
            }

        }


        @media (max-width: 400px) {

            .nav-menu {

                gap: 10px;
            }


            .nav-menu a {

                font-size: 11px;
            }


            .nav-menu a.logout {

                padding: 8px 14px;
            }

        }

    </style>

</head>


<body>


    <!-- ==================================================
         NAVBAR
    ================================================== -->

    <nav class="navbar">

        <a
            href="index.php"
            class="logo"
        >
            <span class="logo-second">Second</span><span class="logo-space">Space</span>
        </a>


        <div class="nav-menu">

            <a href="index.php">
                Dashboard
            </a>

            <a href="produk.php">
                Produk
            </a>

            <a href="pesanan.php" class="active">
                Pesanan
            </a>

            <a href="contact.php">
                Contact
            </a>

            <a
                href="../logout.php"
                class="logout"
            >
                Logout
            </a>

        </div>

    </nav>


    <!-- ==================================================
         CONTENT
    ================================================== -->

    <main class="container">


        <a
            href="pesanan.php"
            class="back"
        >
            ← Kembali ke Pesanan
        </a>


        <div class="title">

            <h1>
                Detail Pesanan
            </h1>

            <p>
                Kelola informasi dan status pesanan customer.
            </p>

        </div>


        <?php if ($success): ?>

            <div class="alert success">

                <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <div class="grid">


            <!-- ==================================================
                 KOLOM KIRI
            ================================================== -->

            <div>


                <!-- INFORMASI CUSTOMER -->

                <div class="card">

                    <h2>
                        Informasi Customer
                    </h2>


                    <div class="info-row">

                        <div class="info-label">
                            Nama
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["customer_name"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-label">
                            Username
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["customer_username"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-label">
                            Email
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["customer_email"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-label">
                            Penerima
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["recipient_name"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-label">
                            No. Telepon
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["phone"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-label">
                            Alamat
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["address"]
                            ) ?><br>

                            <?= htmlspecialchars(
                                $order["city"]
                            ) ?>,

                            <?= htmlspecialchars(
                                $order["province"]
                            ) ?><br>

                            <?= htmlspecialchars(
                                $order["postal_code"]
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     PRODUK
                ================================================== -->

                <div class="card">

                    <h2>
                        Produk yang Dibeli
                    </h2>


                    <?php foreach ($items as $item): ?>

                        <div class="product-item">


                            <div class="product-image">

                                <?php

                                $imagePath =
                                    "../images/" .
                                    ($item["image"] ?? "");

                                if (
                                    !empty($item["image"]) &&
                                    file_exists($imagePath)
                                ):

                                ?>

                                    <img
                                        src="<?= htmlspecialchars($imagePath) ?>"
                                        alt="<?= htmlspecialchars($item["product_name"]) ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-image">
                                        No Image
                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="product-info">

                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $item["product_name"]
                                    ) ?>

                                </div>


                                <div class="product-meta">

                                    Rp <?= number_format(
                                        $item["price"],
                                        0,
                                        ",",
                                        "."
                                    ) ?>

                                    ×

                                    <?= (int) $item["quantity"] ?>

                                </div>

                            </div>


                            <div class="product-subtotal">

                                Rp <?= number_format(
                                    $item["subtotal"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </div>

                        </div>

                    <?php endforeach; ?>


                    <div class="total-box">


                        <div class="total-row">

                            <span>
                                Subtotal
                            </span>

                            <span>

                                Rp <?= number_format(
                                    $order["subtotal"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </span>

                        </div>


                        <div class="total-row">

                            <span>
                                Ongkir
                            </span>

                            <span>

                                Rp <?= number_format(
                                    $order["shipping"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </span>

                        </div>


                        <div class="total-row grand">

                            <span>
                                Total
                            </span>

                            <span>

                                Rp <?= number_format(
                                    $order["total"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </span>

                        </div>


                    </div>

                </div>


                <!-- ==================================================
                     PEMBAYARAN
                ================================================== -->

                <div class="card">

                    <h2>
                        Informasi Pembayaran
                    </h2>


                    <div class="info-row">

                        <div class="info-label">
                            Metode Pembayaran
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["payment_method"] ?? "-"
                            ) ?>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-label">
                            Status Pembayaran
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["payment_status"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-label">
                            Tanggal Pesanan
                        </div>

                        <div class="info-value">

                            <?= date(
                                "d M Y, H:i",
                                strtotime(
                                    $order["created_at"]
                                )
                            ) ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 KOLOM KANAN
            ================================================== -->

            <div>


                <div class="card">

                    <h2>
                        Update Status Pesanan
                    </h2>


                    <form method="POST">


                        <div class="form-group">

                            <label for="payment_status">
                                Status Pembayaran
                            </label>


                            <select
                                name="payment_status"
                                id="payment_status"
                                required
                            >

                                <option
                                    value="Menunggu Pembayaran"
                                    <?= $order["payment_status"] === "Menunggu Pembayaran" ? "selected" : "" ?>
                                >
                                    Menunggu Pembayaran
                                </option>


                                <option
                                    value="Menunggu Konfirmasi"
                                    <?= $order["payment_status"] === "Menunggu Konfirmasi" ? "selected" : "" ?>
                                >
                                    Menunggu Konfirmasi
                                </option>


                                <option
                                    value="Dibayar"
                                    <?= $order["payment_status"] === "Dibayar" ? "selected" : "" ?>
                                >
                                    Dibayar
                                </option>


                                <option
                                    value="Selesai"
                                    <?= $order["payment_status"] === "Selesai" ? "selected" : "" ?>
                                >
                                    Selesai
                                </option>

                            </select>

                        </div>


                        <div class="form-group">

                            <label for="order_status">
                                Status Pesanan
                            </label>


                            <select
                                name="order_status"
                                id="order_status"
                                required
                            >

                                <option
                                    value="Menunggu Pembayaran"
                                    <?= $order["order_status"] === "Menunggu Pembayaran" ? "selected" : "" ?>
                                >
                                    Menunggu Pembayaran
                                </option>


                                <option
                                    value="Diproses"
                                    <?= $order["order_status"] === "Diproses" ? "selected" : "" ?>
                                >
                                    Diproses
                                </option>


                                <option
                                    value="Dikirim"
                                    <?= $order["order_status"] === "Dikirim" ? "selected" : "" ?>
                                >
                                    Dikirim
                                </option>


                                <option
                                    value="Selesai"
                                    <?= $order["order_status"] === "Selesai" ? "selected" : "" ?>
                                >
                                    Selesai
                                </option>


                                <option
                                    value="Dibatalkan"
                                    <?= $order["order_status"] === "Dibatalkan" ? "selected" : "" ?>
                                >
                                    Dibatalkan
                                </option>

                            </select>

                        </div>


                        <div class="form-group">

                            <label for="courier">
                                Kurir
                            </label>


                            <select
                                name="courier"
                                id="courier"
                            >

                                <option value="">
                                    -- Pilih Kurir --
                                </option>


                                <option
                                    value="JNE"
                                    <?= $order["courier"] === "JNE" ? "selected" : "" ?>
                                >
                                    JNE
                                </option>


                                <option
                                    value="J&T Express"
                                    <?= $order["courier"] === "J&T Express" ? "selected" : "" ?>
                                >
                                    J&T Express
                                </option>


                                <option
                                    value="SiCepat"
                                    <?= $order["courier"] === "SiCepat" ? "selected" : "" ?>
                                >
                                    SiCepat
                                </option>


                                <option
                                    value="AnterAja"
                                    <?= $order["courier"] === "AnterAja" ? "selected" : "" ?>
                                >
                                    AnterAja
                                </option>


                                <option
                                    value="POS Indonesia"
                                    <?= $order["courier"] === "POS Indonesia" ? "selected" : "" ?>
                                >
                                    POS Indonesia
                                </option>


                                <option
                                    value="Gojek"
                                    <?= $order["courier"] === "Gojek" ? "selected" : "" ?>
                                >
                                    Gojek
                                </option>


                                <option
                                    value="Grab"
                                    <?= $order["courier"] === "Grab" ? "selected" : "" ?>
                                >
                                    Grab
                                </option>

                            </select>

                        </div>


                        <div class="form-group">

                            <label for="tracking_number">
                                Nomor Resi
                            </label>


                            <input
                                type="text"
                                name="tracking_number"
                                id="tracking_number"
                                value="<?= htmlspecialchars($order["tracking_number"] ?? "") ?>"
                                placeholder="Masukkan nomor resi"
                            >


                            <div class="form-note">

                                Nomor resi wajib diisi jika status
                                pesanan adalah Dikirim.

                            </div>

                        </div>


                        <button
                            type="submit"
                            class="btn-update"
                        >
                            Simpan Perubahan
                        </button>

                    </form>


                    <a
                        href="pesanan.php"
                        class="btn-back"
                    >
                        Kembali ke Daftar Pesanan
                    </a>

                </div>

            </div>

        </div>

    </main>


</body>

</html>
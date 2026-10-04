<?php

require_once "auth.php";
require_once "db.php";

// ======================================================
// AMBIL ID PESANAN
// ======================================================
$order_id = (int)($_GET["id"] ?? 0);

if ($order_id <= 0) {
    die("ID pesanan tidak valid.");
}

// ======================================================
// AMBIL DATA PESANAN
// ======================================================
$stmt = $conn->prepare("
    SELECT
        o.*,
        a.recipient_name,
        a.phone,
        a.address,
        a.province,
        a.city,
        a.postal_code
    FROM orders o
    INNER JOIN addresses a
        ON o.address_id = a.id
    WHERE o.id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $order_id
);

$stmt->execute();

$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// ======================================================
// AMBIL ITEM PESANAN
// ======================================================
$stmt_items = $conn->prepare("
    SELECT
        oi.product_name,
        oi.price,
        oi.quantity,
        oi.subtotal,
        p.image
    FROM order_items oi
    LEFT JOIN products p
        ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");

$stmt_items->bind_param(
    "i",
    $order_id
);

$stmt_items->execute();

$items = $stmt_items->get_result();

// ======================================================
// FORMAT RUPIAH
// ======================================================
function rupiah($angka)
{
    return "Rp " . number_format(
        $angka,
        0,
        ",",
        "."
    );
}

// ======================================================
// CLASS STATUS
// ======================================================
$statusClass = "";

switch ($order["order_status"]) {

    case "Selesai":
        $statusClass = "status-selesai";
        break;

    case "Dikirim":
        $statusClass = "status-dikirim";
        break;

    case "Diproses":
        $statusClass = "status-diproses";
        break;

    case "Dibatalkan":
        $statusClass = "status-dibatalkan";
        break;

    default:
        $statusClass = "status-menunggu";
        break;
}

// ======================================================
// STATUS TIMELINE
// ======================================================
$currentStatus = $order["order_status"];

$statusSteps = [
    "Menunggu Pembayaran",
    "Diproses",
    "Dikirim",
    "Selesai"
];

$currentIndex = array_search(
    $currentStatus,
    $statusSteps
);

if ($currentIndex === false) {
    $currentIndex = 0;
}

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
        Detail Pesanan - SecondSpace
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

            animation:
                navbarDown 0.5s ease-out;
        }

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
            transform:
                translateY(-2px);

            opacity: 0.9;
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
           MENU
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

                transform:
                    translateX(20px);
            }

            to {
                opacity: 1;

                transform:
                    translateX(0);
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
           GARIS MENU
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

            transform:
                scaleX(0);

            transform-origin: left;

            transition:
                transform 0.35s ease;
        }

        /* ==================================================
           MENU AKTIF
        ================================================== */

        .nav-menu a.active {
            color: #ffffff;
        }

        .nav-menu a.active::after {
            transform: scaleX(1);
        }

        /* ==================================================
           HOVER
        ================================================== */

        .nav-menu a:hover::after {
            transform: scaleX(1);
        }

        .nav-menu a:hover {
            color: #9ff3d0;

            transform:
                translateY(-2px);
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
            width: 90%;

            max-width: 1000px;

            margin: 35px auto;
        }

        /* ==================================================
           BACK
        ================================================== */

        .back {
            display: inline-block;

            margin-bottom: 20px;

            color: #222;

            text-decoration: none;

            font-size: 14px;

            transition:
                color 0.2s ease,
                transform 0.2s ease;
        }

        .back:hover {
            color: #555;

            transform:
                translateX(-3px);
        }

        /* ==================================================
           HEADER
        ================================================== */

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin:
                0 0 8px;

            font-size: 30px;
        }

        .header p {
            margin: 0;

            color: #777;
        }

        /* ==================================================
           CARD
        ================================================== */

        .card {
            background: #ffffff;

            border-radius: 16px;

            padding: 24px;

            margin-bottom: 20px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.06);
        }

        /* ==================================================
           ORDER HEADER
        ================================================== */

        .order-header {
            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 20px;
        }

        .order-code {
            font-size: 20px;

            font-weight: bold;

            margin-bottom: 6px;
        }

        .order-date {
            color: #777;

            font-size: 14px;
        }

        /* ==================================================
           STATUS BADGE
        ================================================== */

        .status {
            display: inline-block;

            padding:
                8px 14px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;
        }

        .status-menunggu {
            background: #fff3cd;

            color: #856404;
        }

        .status-diproses {
            background: #dbeafe;

            color: #1d4ed8;
        }

        .status-dikirim {
            background: #dcfce7;

            color: #15803d;
        }

        .status-selesai {
            background: #d1fae5;

            color: #065f46;
        }

        .status-dibatalkan {
            background: #fee2e2;

            color: #b91c1c;
        }

        /* ==================================================
           TIMELINE
        ================================================== */

        .tracking-card {
            background: #ffffff;

            border-radius: 16px;

            padding: 24px;

            margin-bottom: 20px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.06);
        }

        .tracking-title {
            margin:
                0 0 25px;

            font-size: 20px;
        }

        .timeline {
            display: flex;

            justify-content:
                space-between;

            position: relative;

            padding: 0 15px;
        }

        .timeline::before {
            content: "";

            position: absolute;

            top: 18px;

            left: 7%;

            width: 86%;

            height: 3px;

            background: #e5e7eb;

            z-index: 0;
        }

        .timeline-progress {
            position: absolute;

            top: 18px;

            left: 7%;

            height: 3px;

            background: #222;

            z-index: 1;

            transition:
                0.3s ease;
        }

        .timeline-step {
            position: relative;

            z-index: 2;

            width: 25%;

            text-align: center;
        }

        .timeline-icon {
            width: 38px;

            height: 38px;

            margin:
                0 auto 10px;

            border-radius: 50%;

            background: #e5e7eb;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 17px;

            border:
                3px solid #ffffff;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.08);
        }

        .timeline-step.active .timeline-icon {
            background: #222;

            color: #ffffff;
        }

        .timeline-step.current .timeline-icon {
            transform:
                scale(1.08);
        }

        .timeline-label {
            font-size: 12px;

            color: #888;

            line-height: 1.4;
        }

        .timeline-step.active .timeline-label {
            color: #222;

            font-weight: bold;
        }

        /* ==================================================
           CANCELED
        ================================================== */

        .cancelled-box {
            background: #fff1f2;

            border:
                1px solid #fecdd3;

            color: #be123c;

            padding:
                15px 18px;

            border-radius: 10px;

            margin-top: 20px;

            font-size: 14px;
        }

        /* ==================================================
           PAYMENT
        ================================================== */

        .payment-info {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

            margin-top: 20px;
        }

        .info-box {
            background: #f8f9fa;

            padding: 15px;

            border-radius: 9px;
        }

        .info-label {
            color: #777;

            font-size: 13px;

            margin-bottom: 6px;
        }

        .info-value {
            font-size: 15px;

            font-weight: bold;
        }

        /* ==================================================
           SHIPPING
        ================================================== */

        .shipping-box {
            background: #f8f9fa;

            border-radius: 12px;

            padding: 18px;

            margin-top: 20px;
        }

        .shipping-title {
            font-size: 17px;

            font-weight: bold;

            margin-bottom: 15px;
        }

        .shipping-info {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;
        }

        .shipping-item {
            background: #ffffff;

            border:
                1px solid #eeeeee;

            border-radius: 9px;

            padding: 14px;
        }

        .shipping-label {
            color: #777;

            font-size: 13px;

            margin-bottom: 6px;
        }

        .shipping-value {
            font-size: 15px;

            font-weight: bold;

            word-break: break-word;
        }

        .shipping-empty {
            color: #999;

            font-weight: normal;
        }

        /* ==================================================
           RESI
        ================================================== */

        .tracking-number {
            color: #111;

            letter-spacing: 0.4px;
        }

        /* ==================================================
           PRODUCT
        ================================================== */

        .section-title {
            margin:
                0 0 18px;

            font-size: 20px;
        }

        .product-item {
            display: grid;

            grid-template-columns:
                70px 1fr auto;

            gap: 15px;

            align-items: center;

            padding: 15px 0;

            border-bottom:
                1px solid #eee;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 70px;

            height: 70px;

            border-radius: 10px;

            overflow: hidden;

            background: #f1f1f1;

            display: flex;

            align-items: center;

            justify-content: center;
        }

        .product-image img {
            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;
        }

        .no-image {
            font-size: 28px;
        }

        .product-name {
            font-weight: bold;

            margin-bottom: 5px;
        }

        .product-price {
            color: #777;

            font-size: 14px;
        }

        .product-total {
            font-weight: bold;

            text-align: right;
        }

        /* ==================================================
           ADDRESS
        ================================================== */

        .address-box {
            background: #f8f9fa;

            padding: 18px;

            border-radius: 10px;

            line-height: 1.7;

            color: #555;
        }

        .recipient {
            font-weight: bold;

            color: #222;

            margin-bottom: 5px;
        }

        /* ==================================================
           TOTAL
        ================================================== */

        .total-box {
            max-width: 400px;

            margin-left: auto;

            margin-top: 20px;
        }

        .total-row {
            display: flex;

            justify-content:
                space-between;

            padding: 8px 0;

            font-size: 15px;
        }

        .grand-total {
            border-top:
                2px solid #222;

            margin-top: 8px;

            padding-top: 13px;

            font-size: 18px;

            font-weight: bold;
        }

        /* ==================================================
           BUTTON
        ================================================== */

        .btn {
            display: inline-block;

            padding:
                12px 18px;

            border-radius: 9px;

            text-decoration: none;

            background: #222;

            color: #ffffff;

            font-size: 14px;

            transition:
                0.2s ease;
        }

        .btn:hover {
            background: #444;

            transform:
                translateY(-1px);
        }

        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 850px) {

            .navbar {
                padding:
                    16px 20px;
            }

            .nav-menu {
                gap: 13px;
            }

            .nav-menu a {
                font-size: 12px;
            }

        }

        @media (max-width: 700px) {

            .navbar {
                flex-direction: column;

                gap: 12px;

                align-items: center;
            }

            .navbar .logo {
                margin-right: 0;
            }

            .nav-menu {
                flex-wrap: wrap;

                justify-content: center;

                margin-left: 0;
            }

            .payment-info,
            .shipping-info {
                grid-template-columns: 1fr;
            }

            .order-header {
                flex-direction: column;

                align-items: flex-start;
            }

            .timeline {
                padding: 0;
            }

            .timeline::before {
                left: 7%;

                width: 86%;
            }

            .timeline-progress {
                left: 7%;
            }

            .timeline-label {
                font-size: 10px;
            }

            .timeline-icon {
                width: 34px;

                height: 34px;

                font-size: 15px;
            }

            .product-item {
                grid-template-columns:
                    60px 1fr;
            }

            .product-image {
                width: 60px;

                height: 60px;
            }

            .product-total {
                grid-column: 2;

                text-align: left;
            }

            .total-box {
                max-width: none;
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
                padding:
                    8px 14px;
            }

        }

    </style>

</head>

<body>

<!-- ======================================================
     NAVBAR
====================================================== -->

<nav class="navbar">

    <a
        href="index.php"
        class="logo"
    >

        <span class="logo-second">
            Second
        </span><span class="logo-space">Space</span>

    </a>

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

        <a
            href="pesanan.php"
            class="active"
        >
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

</nav>

<div class="container">

    <!-- ==================================================
         BACK
    ================================================== -->

    <a
        href="pesanan.php"
        class="back"
    >
        ← Kembali ke Pesanan
    </a>

    <!-- ==================================================
         HEADER
    ================================================== -->

    <div class="header">

        <h1>
            Detail Pesanan
        </h1>

        <p>
            Informasi lengkap pesanan kamu.
        </p>

    </div>

    <!-- ==================================================
         INFORMASI PESANAN
    ================================================== -->

    <div class="card">

        <div class="order-header">

            <div>

                <div class="order-code">

                    <?= htmlspecialchars(
                        $order["order_code"]
                    ) ?>

                </div>

                <div class="order-date">

                    <?= date(
                        "d M Y, H:i",
                        strtotime(
                            $order["created_at"]
                        )
                    ) ?>

                </div>

            </div>

            <span
                class="status <?= $statusClass ?>"
            >

                <?= htmlspecialchars(
                    $order["order_status"]
                ) ?>

            </span>

        </div>

        <!-- ==================================================
             TIMELINE
        ================================================== -->

        <div class="tracking-card">

            <h2 class="tracking-title">
                📦 Perjalanan Pesanan
            </h2>

            <?php if (
                $currentStatus === "Dibatalkan"
            ): ?>

                <div class="cancelled-box">

                    ❌ Pesanan ini telah dibatalkan.

                </div>

            <?php else: ?>

                <?php

                $progressWidth = 0;

                if ($currentIndex === 1) {

                    $progressWidth = 29;

                } elseif ($currentIndex === 2) {

                    $progressWidth = 58;

                } elseif ($currentIndex === 3) {

                    $progressWidth = 86;

                }

                ?>

                <div class="timeline">

                    <div
                        class="timeline-progress"
                        style="
                            width:
                            <?= $progressWidth ?>%;
                        "
                    ></div>

                    <?php

                    $icons = [
                        "💳",
                        "📦",
                        "🚚",
                        "✓"
                    ];

                    foreach (
                        $statusSteps
                        as $index => $step
                    ):

                        $isActive =
                            $index <= $currentIndex;

                        $isCurrent =
                            $index === $currentIndex;

                    ?>

                        <div
                            class="
                                timeline-step
                                <?= $isActive
                                    ? "active"
                                    : "" ?>
                                <?= $isCurrent
                                    ? "current"
                                    : "" ?>
                            "
                        >

                            <div class="timeline-icon">

                                <?= $icons[$index] ?>

                            </div>

                            <div class="timeline-label">

                                <?= htmlspecialchars(
                                    $step
                                ) ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

        <!-- ==================================================
             PEMBAYARAN
        ================================================== -->

        <div class="payment-info">

            <div class="info-box">

                <div class="info-label">
                    Metode Pembayaran
                </div>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $order["payment_method"] ?? "-"
                    ) ?>

                </div>

            </div>

            <div class="info-box">

                <div class="info-label">
                    Status Pembayaran
                </div>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $order["payment_status"]
                    ) ?>

                </div>

            </div>

        </div>

        <!-- ==================================================
             INFORMASI PENGIRIMAN
        ================================================== -->

        <div class="shipping-box">

            <div class="shipping-title">

                🚚 Informasi Pengiriman

            </div>

            <div class="shipping-info">

                <div class="shipping-item">

                    <div class="shipping-label">
                        Kurir
                    </div>

                    <div class="shipping-value">

                        <?php if (
                            !empty($order["courier"])
                        ): ?>

                            <?= htmlspecialchars(
                                $order["courier"]
                            ) ?>

                        <?php else: ?>

                            <span class="shipping-empty">

                                Belum ditentukan

                            </span>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="shipping-item">

                    <div class="shipping-label">
                        Nomor Resi
                    </div>

                    <div
                        class="
                            shipping-value
                            tracking-number
                        "
                    >

                        <?php if (
                            !empty(
                                $order["tracking_number"]
                            )
                        ): ?>

                            <?= htmlspecialchars(
                                $order["tracking_number"]
                            ) ?>

                        <?php else: ?>

                            <span class="shipping-empty">

                                Belum tersedia

                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- ==================================================
         PRODUK
    ================================================== -->

    <div class="card">

        <h2 class="section-title">

            🛍️ Produk Pesanan

        </h2>

        <?php while (
            $item = $items->fetch_assoc()
        ): ?>

            <div class="product-item">

                <div class="product-image">

                    <?php if (
                        !empty($item["image"])
                    ): ?>

                        <img
                            src="images/<?= htmlspecialchars(
                                $item["image"]
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $item["product_name"]
                            ) ?>"
                        >

                    <?php else: ?>

                        <div class="no-image">

                            🛍️

                        </div>

                    <?php endif; ?>

                </div>

                <div>

                    <div class="product-name">

                        <?= htmlspecialchars(
                            $item["product_name"]
                        ) ?>

                    </div>

                    <div class="product-price">

                        <?= rupiah(
                            $item["price"]
                        ) ?>

                        ×

                        <?= (int)$item["quantity"] ?>

                    </div>

                </div>

                <div class="product-total">

                    <?= rupiah(
                        $item["subtotal"]
                    ) ?>

                </div>

            </div>

        <?php endwhile; ?>

        <!-- ==================================================
             TOTAL
        ================================================== -->

        <div class="total-box">

            <div class="total-row">

                <span>
                    Subtotal
                </span>

                <strong>

                    <?= rupiah(
                        $order["subtotal"]
                    ) ?>

                </strong>

            </div>

            <div class="total-row">

                <span>
                    Ongkir
                </span>

                <strong>

                    <?= rupiah(
                        $order["shipping"]
                    ) ?>

                </strong>

            </div>

            <div
                class="
                    total-row
                    grand-total
                "
            >

                <span>
                    Total
                </span>

                <strong>

                    <?= rupiah(
                        $order["total"]
                    ) ?>

                </strong>

            </div>

        </div>

    </div>

    <!-- ==================================================
         ALAMAT
    ================================================== -->

    <div class="card">

        <h2 class="section-title">

            📍 Alamat Pengiriman

        </h2>

        <div class="address-box">

            <div class="recipient">

                <?= htmlspecialchars(
                    $order["recipient_name"]
                ) ?>

            </div>

            <?= htmlspecialchars(
                $order["phone"]
            ) ?>

            <br>

            <?= nl2br(
                htmlspecialchars(
                    $order["address"]
                )
            ) ?>

            <br>

            <?= htmlspecialchars(
                $order["city"]
            ) ?>,

            <?= htmlspecialchars(
                $order["province"]
            ) ?>

            <br>

            <?= htmlspecialchars(
                $order["postal_code"]
            ) ?>

        </div>

    </div>

    <!-- ==================================================
         BUTTON
    ================================================== -->

    <a
        href="pesanan.php"
        class="btn"
    >
        ← Kembali ke Daftar Pesanan
    </a>

</div>

</body>

</html>
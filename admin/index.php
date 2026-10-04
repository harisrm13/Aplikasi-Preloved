<?php

require_once "auth_admin.php";
require_once "../db.php";


/* ==================================================
   DATA DASHBOARD
================================================== */

// Total produk
$resultProducts = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
");

$totalProducts = $resultProducts
    ? $resultProducts->fetch_assoc()["total"]
    : 0;


// Total pesanan
$resultOrders = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
");

$totalOrders = $resultOrders
    ? $resultOrders->fetch_assoc()["total"]
    : 0;


// Total pesan masuk
$resultContacts = $conn->query("
    SELECT COUNT(*) AS total
    FROM contacts
");

$totalContacts = $resultContacts
    ? $resultContacts->fetch_assoc()["total"]
    : 0;


// Total pengguna
$resultUsers = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
");

$totalUsers = $resultUsers
    ? $resultUsers->fetch_assoc()["total"]
    : 0;


/* ==================================================
   PESANAN TERBARU
================================================== */

$recentOrders = $conn->query("
    SELECT
        o.id,
        o.order_code,
        o.total,
        o.payment_status,
        o.order_status,
        o.created_at,
        u.name,
        u.email
    FROM orders o
    LEFT JOIN users u
        ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");


/* ==================================================
   NAMA ADMIN
================================================== */

$adminName = "Admin SecondSpace";

if (isset($_SESSION["user_id"])) {

    $adminId = (int) $_SESSION["user_id"];

    $adminStmt = $conn->prepare("
        SELECT name
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $adminStmt->bind_param(
        "i",
        $adminId
    );

    $adminStmt->execute();

    $adminResult = $adminStmt->get_result();

    if ($adminResult && $adminResult->num_rows > 0) {

        $adminData = $adminResult->fetch_assoc();

        if (!empty($adminData["name"])) {
            $adminName = $adminData["name"];
        }
    }

    $adminStmt->close();
}


/* ==================================================
   STATUS PEMBAYARAN
================================================== */

function paymentStatusClass($status)
{
    switch ($status) {

        case "Menunggu Pembayaran":
            return "payment-waiting";

        case "Menunggu Konfirmasi":
            return "payment-confirmation";

        case "Dibayar":
            return "payment-paid";

        case "Selesai":
            return "payment-finished";

        default:
            return "payment-default";
    }
}


/* ==================================================
   STATUS PESANAN
================================================== */

function orderStatusClass($status)
{
    switch ($status) {

        case "Menunggu Pembayaran":
            return "order-waiting";

        case "Diproses":
            return "order-processing";

        case "Dikirim":
            return "order-shipping";

        case "Selesai":
            return "order-finished";

        case "Dibatalkan":
            return "order-cancelled";

        default:
            return "order-default";
    }
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
        Dashboard Admin - SecondSpace
    </title>


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
    font-family:
        Arial,
        Helvetica,
        sans-serif;

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
        0 3px 15px
        rgba(0, 0, 0, 0.08);

    animation:
        navbarDown 0.6s ease-out;
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

    animation:
        logoSlide 0.7s ease-out;

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


/* ==================================================
   NAV MENU
================================================== */

.navbar .nav-menu {
    display: flex;

    align-items: center;

    gap: 24px;

    margin-left: auto;
}


/* ==================================================
   MENU
================================================== */

.navbar .nav-menu a {
    color: #ffffff;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    position: relative;

    padding: 5px 0;

    opacity: 0;

    animation:
        menuFade 0.5s ease-out
        forwards;

    transition:
        color 0.25s ease,
        transform 0.25s ease;
}


/* ==================================================
   MENU ANIMATION SATU PER SATU
================================================== */

.navbar .nav-menu a:nth-child(1) {
    animation-delay: 0.25s;
}

.navbar .nav-menu a:nth-child(2) {
    animation-delay: 0.35s;
}

.navbar .nav-menu a:nth-child(3) {
    animation-delay: 0.45s;
}

.navbar .nav-menu a:nth-child(4) {
    animation-delay: 0.55s;
}

.navbar .nav-menu a:nth-child(5) {
    animation-delay: 0.65s;
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

    transform:
        scaleX(0);

    transform-origin: left;

    transition:
        transform 0.35s ease;
}


/* ==================================================
   ACTIVE DASHBOARD
================================================== */

.navbar .nav-menu a.active {
    color: #ffffff;
}

.navbar .nav-menu a.active::after {
    transform:
        scaleX(1);
}


/* ==================================================
   HOVER MENU
================================================== */

.navbar .nav-menu a:hover::after {
    transform:
        scaleX(1);
}

.navbar .nav-menu a:hover {
    color: #9ff3d0;

    transform:
        translateY(-2px);
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
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.navbar .nav-menu a.logout::after {
    display: none;
}

.navbar .nav-menu a.logout:hover {
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
    max-width: 1250px;

    margin: 0 auto;

    padding:
        35px 20px 50px;

    animation:
        contentFade 0.7s ease-out;
}


/* ==================================================
   HEADER
================================================== */

.header {
    margin-bottom: 30px;
}

.header h1 {
    font-size: 30px;

    color: #111827;

    margin-bottom: 8px;
}

.header p {
    color: #64748b;

    font-size: 16px;
}


/* ==================================================
   STATISTICS
================================================== */

.stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 28px;

    margin-bottom: 50px;
}


/* ==================================================
   STAT CARD
================================================== */

.stat-card {
    background: #ffffff;

    border:
        1px solid #e5e7eb;

    border-radius: 18px;

    padding:
        28px 30px;

    min-height: 190px;

    box-shadow:
        0 6px 20px
        rgba(0, 0, 0, 0.05);

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

.stat-card:nth-child(3) {
    animation-delay: 0.3s;
}

.stat-card:nth-child(4) {
    animation-delay: 0.4s;
}

.stat-card:hover {
    transform:
        translateY(-5px);

    box-shadow:
        0 12px 25px
        rgba(0, 0, 0, 0.08);
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

    transform:
        translateY(-3px);
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
   ORDER SECTION
================================================== */

.order-section {
    background: #ffffff;

    border:
        1px solid #e5e7eb;

    border-radius: 18px;

    padding:
        32px 35px;

    box-shadow:
        0 6px 20px
        rgba(0, 0, 0, 0.05);

    animation:
        sectionUp 0.7s ease-out;
}


/* ==================================================
   ORDER HEADER
================================================== */

.order-header {
    display: flex;

    align-items: center;

    justify-content:
        space-between;

    margin-bottom: 28px;
}

.order-header h2 {
    font-size: 28px;

    color: #111827;
}

.view-all {
    color: #111827;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    transition:
        color 0.25s ease,
        transform 0.25s ease;
}

.view-all:hover {
    color: #64748b;

    transform:
        translateX(3px);
}


/* ==================================================
   TABLE
================================================== */

.table-wrapper {
    width: 100%;

    overflow-x: auto;
}

table {
    width: 100%;

    border-collapse: collapse;

    min-width: 1000px;
}

th {
    background: #111111;

    color: #ffffff;

    padding:
        16px 18px;

    text-align: left;

    font-size: 14px;

    font-weight: 600;

    border-bottom:
        1px solid #e5e7eb;
}

th:first-child {
    border-radius:
        12px 0 0 0;
}

th:last-child {
    border-radius:
        0 12px 0 0;
}

td {
    padding: 18px;

    border-bottom:
        1px solid #eeeeee;

    vertical-align: middle;

    font-size: 14px;

    color: #1e293b;
}

tbody tr {
    transition:
        background 0.2s ease;
}

tbody tr:hover {
    background: #fafafa;
}

tbody tr:last-child td {
    border-bottom: none;
}


/* ==================================================
   ORDER CODE
================================================== */

.order-code {
    font-weight: 600;

    color: #111827;

    white-space: nowrap;
}


/* ==================================================
   CUSTOMER
================================================== */

.customer-name {
    font-weight: 600;

    color: #1e293b;

    margin-bottom: 5px;
}

.email {
    color: #94a3b8;

    font-size: 12px;
}


/* ==================================================
   TOTAL
================================================== */

.total {
    font-weight: 600;

    color: #111827;

    white-space: nowrap;
}


/* ==================================================
   BADGE
================================================== */

.badge {
    display: inline-block;

    padding:
        8px 12px;

    border-radius: 8px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

    transition:
        transform 0.2s ease;
}

.badge:hover {
    transform:
        translateY(-1px);
}


/* ==================================================
   PAYMENT STATUS
================================================== */

.payment-waiting {
    background: #fff3cd;

    color: #946200;

    border:
        1px solid #ffe08a;
}

.payment-confirmation {
    background: #ffe5cc;

    color: #a64b00;

    border:
        1px solid #ffcc99;
}

.payment-paid {
    background: #dbeafe;

    color: #1d4ed8;

    border:
        1px solid #bfdbfe;
}

.payment-finished {
    background: #dcfce7;

    color: #15803d;

    border:
        1px solid #bbf7d0;
}

.payment-default {
    background: #f1f5f9;

    color: #475569;

    border:
        1px solid #e2e8f0;
}


/* ==================================================
   ORDER STATUS
================================================== */

.order-waiting {
    background: #f1f5f9;

    color: #475569;

    border:
        1px solid #cbd5e1;
}

.order-processing {
    background: #dbeafe;

    color: #1d4ed8;

    border:
        1px solid #bfdbfe;
}

.order-shipping {
    background: #ede9fe;

    color: #6d28d9;

    border:
        1px solid #ddd6fe;
}

.order-finished {
    background: #dcfce7;

    color: #15803d;

    border:
        1px solid #bbf7d0;
}

.order-cancelled {
    background: #fee2e2;

    color: #b91c1c;

    border:
        1px solid #fecaca;
}

.order-default {
    background: #f1f5f9;

    color: #475569;

    border:
        1px solid #e2e8f0;
}


/* ==================================================
   DATE
================================================== */

.date {
    color: #475569;

    white-space: nowrap;
}


/* ==================================================
   DETAIL BUTTON
================================================== */

.btn-detail {
    display: inline-block;

    background: #111827;

    color: #ffffff;

    text-decoration: none;

    padding:
        10px 16px;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 600;

    transition:
        background 0.25s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.btn-detail:hover {
    background: #263244;

    transform:
        translateY(-2px);

    box-shadow:
        0 6px 15px
        rgba(0, 0, 0, 0.12);
}


/* ==================================================
   EMPTY
================================================== */

.empty {
    text-align: center;

    padding:
        50px 20px;

    color: #777;
}

.empty h3 {
    color: #333;

    margin-bottom: 8px;
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
            translateX(-35px);
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
            translateX(25px);
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

    .stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media (max-width: 768px) {

    .navbar {
        padding:
            15px 20px;

        flex-wrap: wrap;

        gap: 15px;
    }

    .navbar .logo {
        font-size: 20px;

        margin-right: 0;
    }

    .navbar .nav-menu {
        width: 100%;

        justify-content: center;

        gap: 15px;

        flex-wrap: wrap;
    }

    .navbar .nav-menu a {
        font-size: 13px;
    }

    .container {
        padding:
            25px 15px 40px;
    }

    .header h1 {
        font-size: 25px;
    }

    .header p {
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

    .order-section {
        padding:
            25px 20px;
    }

    .order-header h2 {
        font-size: 23px;
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
        padding:
            8px 14px;
    }

}

    </style>

</head>


<body>


<!-- ==================================================
     NAVBAR ADMIN
================================================== -->

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

        <a
            href="index.php"
            class="active"
        >
            Dashboard
        </a>

        <a href="produk.php">
            Produk
        </a>

        <a href="pesanan.php">
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

<div class="container">


    <!-- ==================================================
         HEADER
    ================================================== -->

    <div class="header">

        <h1>
            Dashboard Admin
        </h1>

        <p>

            Selamat datang,
            <?= htmlspecialchars($adminName); ?>.

            Kelola SecondSpace PreLoved dari sini.

        </p>

    </div>


    <!-- ==================================================
         STATISTICS
    ================================================== -->

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


        <!-- TOTAL PESANAN -->

        <div class="stat-card">

            <div class="stat-icon">
                📦
            </div>

            <h3>
                Total Pesanan
            </h3>

            <div class="stat-number">
                <?= $totalOrders; ?>
            </div>

        </div>


        <!-- PESAN MASUK -->

        <div class="stat-card">

            <div class="stat-icon">
                💬
            </div>

            <h3>
                Pesan Masuk
            </h3>

            <div class="stat-number">
                <?= $totalContacts; ?>
            </div>

        </div>


        <!-- TOTAL PENGGUNA -->

        <div class="stat-card">

            <div class="stat-icon">
                👤
            </div>

            <h3>
                Total Pengguna
            </h3>

            <div class="stat-number">
                <?= $totalUsers; ?>
            </div>

        </div>


    </div>


    <!-- ==================================================
         PESANAN TERBARU
    ================================================== -->

    <div class="order-section">


        <div class="order-header">

            <h2>
                Pesanan Terbaru
            </h2>

            <a
                href="pesanan.php"
                class="view-all"
            >
                Lihat Semua →
            </a>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            Kode Pesanan
                        </th>

                        <th>
                            Pelanggan
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Pembayaran
                        </th>

                        <th>
                            Status Pesanan
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (
                    $recentOrders &&
                    $recentOrders->num_rows > 0
                ): ?>


                    <?php while (
                        $order =
                        $recentOrders->fetch_assoc()
                    ): ?>


                        <tr>


                            <!-- KODE PESANAN -->

                            <td>

                                <div class="order-code">

                                    <?= htmlspecialchars(
                                        $order["order_code"]
                                    ); ?>

                                </div>

                            </td>


                            <!-- PELANGGAN -->

                            <td>

                                <div class="customer-name">

                                    <?= htmlspecialchars(
                                        $order["name"] ?? "-"
                                    ); ?>

                                </div>

                                <div class="email">

                                    <?= htmlspecialchars(
                                        $order["email"] ?? "-"
                                    ); ?>

                                </div>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <div class="total">

                                    Rp <?= number_format(
                                        $order["total"],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </div>

                            </td>


                            <!-- PEMBAYARAN -->

                            <td>

                                <span
                                    class="
                                        badge
                                        <?= paymentStatusClass(
                                            $order["payment_status"]
                                        ); ?>
                                    "
                                >

                                    <?= htmlspecialchars(
                                        $order["payment_status"]
                                    ); ?>

                                </span>

                            </td>


                            <!-- STATUS PESANAN -->

                            <td>

                                <span
                                    class="
                                        badge
                                        <?= orderStatusClass(
                                            $order["order_status"]
                                        ); ?>
                                    "
                                >

                                    <?= htmlspecialchars(
                                        $order["order_status"]
                                    ); ?>

                                </span>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <div class="date">

                                    <?= date(
                                        "d/m/Y H:i",
                                        strtotime(
                                            $order["created_at"]
                                        )
                                    ); ?>

                                </div>

                            </td>


                            <!-- DETAIL -->

                            <td>

                                <a
                                    href="
                                        detail_pesanan.php?id=
                                        <?= (int) $order["id"]; ?>
                                    "
                                    class="btn-detail"
                                >
                                    Detail
                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td colspan="7">

                            <div class="empty">

                                <h3>
                                    Belum ada pesanan
                                </h3>

                                <p>
                                    Pesanan terbaru akan muncul di sini.
                                </p>

                            </div>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>


</div>


</body>

</html>
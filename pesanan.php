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

$userId = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Ambil Semua Pesanan User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        id,
        order_code,
        subtotal,
        shipping,
        total,
        payment_method,
        payment_status,
        order_status,
        created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

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
        Pesanan - SecondSpace PreLoved
    </title>

    <style>

        * {
            box-sizing: border-box;

            margin: 0;

            padding: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        body {
            background: #f4f4f2;

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
            transform:
                scaleX(1);
        }


        /* ==================================================
           HOVER
        ================================================== */

        .nav-menu a:hover::after {
            transform:
                scaleX(1);
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
            max-width: 1100px;

            margin:
                45px auto;

            padding:
                0 20px 60px;
        }


        /* ==================================================
           HEADER
        ================================================== */

        .header {
            margin-bottom: 30px;
        }


        .header h1 {
            font-size: 34px;

            margin-bottom: 8px;
        }


        .header p {
            color: #777;

            font-size: 15px;
        }


        /* ==================================================
           ORDER CARD
        ================================================== */

        .order-card {
            background: #ffffff;

            border-radius: 20px;

            padding: 25px;

            margin-bottom: 20px;

            border: 1px solid #eee;

            box-shadow:
                0 8px 28px
                rgba(0, 0, 0, 0.055);

            transition: 0.25s;
        }


        .order-card:hover {
            transform:
                translateY(-3px);

            box-shadow:
                0 14px 35px
                rgba(0, 0, 0, 0.09);
        }


        /* ==================================================
           ORDER TOP
        ================================================== */

        .order-top {
            display: flex;

            justify-content:
                space-between;

            align-items: center;

            padding-bottom: 20px;

            border-bottom:
                1px solid #eee;

            margin-bottom: 20px;
        }


        .order-code {
            font-size: 18px;

            font-weight: bold;

            letter-spacing: 0.3px;
        }


        .order-date {
            color: #888;

            font-size: 13px;

            margin-top: 7px;
        }


        /* ==================================================
           STATUS
        ================================================== */

        .status {
            display: inline-flex;

            align-items: center;

            padding:
                8px 13px;

            border-radius: 30px;

            font-size: 12px;

            font-weight: bold;

            white-space: nowrap;
        }


        .waiting {
            background: #fff4d6;

            color: #916b00;
        }


        .process {
            background: #e0ecff;

            color: #2457a6;
        }


        .shipping {
            background: #e0f5ff;

            color: #08749e;
        }


        .success {
            background: #dcf7e5;

            color: #24713b;
        }


        .cancel {
            background: #fee3e3;

            color: #a12626;
        }


        /* ==================================================
           ORDER INFO
        ================================================== */

        .order-info {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 20px;
        }


        .info-box {
            background: #f7f7f5;

            border:
                1px solid #ecece9;

            padding: 16px;

            border-radius: 13px;
        }


        .info-label {
            color: #888;

            font-size: 12px;

            margin-bottom: 8px;
        }


        .info-value {
            font-size: 14px;

            font-weight: bold;

            line-height: 1.5;
        }


        /* ==================================================
           PAYMENT STATUS
        ================================================== */

        .payment-status {
            display: inline-block;

            font-size: 12px;

            line-height: 1.4;
        }


        /* ==================================================
           ORDER BOTTOM
        ================================================== */

        .order-bottom {
            display: flex;

            justify-content: flex-end;

            align-items: center;

            padding-top: 5px;
        }


        .btn {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 6px;

            padding:
                12px 19px;

            background: #1c1c1c;

            color: #ffffff;

            text-decoration: none;

            border-radius: 11px;

            font-size: 13px;

            font-weight: bold;

            transition: 0.25s;
        }


        .btn:hover {
            background: #3a3a3a;

            transform:
                translateY(-2px);

            box-shadow:
                0 7px 16px
                rgba(0, 0, 0, 0.13);
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty {
            background: #ffffff;

            border-radius: 22px;

            padding:
                65px 30px;

            text-align: center;

            border:
                1px solid #eee;

            box-shadow:
                0 8px 28px
                rgba(0, 0, 0, 0.055);
        }


        .empty-icon {
            width: 70px;

            height: 70px;

            margin:
                0 auto 18px;

            background: #f1f1ef;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;
        }


        .empty h2 {
            font-size: 22px;

            margin-bottom: 10px;
        }


        .empty p {
            color: #777;

            font-size: 14px;

            margin-bottom: 24px;
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


            .order-info {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;

                gap: 12px;
            }


            .logo {
                margin-right: 0;
            }


            .nav-menu {
                flex-wrap: wrap;

                justify-content: center;
            }


            .container {
                margin-top: 30px;
            }


            .header h1 {
                font-size: 28px;
            }


            .order-card {
                padding: 20px;
            }


            .order-top {
                flex-direction: column;

                align-items: flex-start;

                gap: 14px;
            }


            .order-info {
                grid-template-columns: 1fr;
            }


            .order-bottom {
                justify-content: stretch;
            }


            .order-bottom .btn {
                width: 100%;
            }

        }


    </style>

</head>

<body>


<!-- ==================================================
     NAVBAR
================================================== -->

<nav class="navbar">

    <a href="index.php" class="logo">

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


<!-- ==================================================
     MAIN
================================================== -->

<div class="container">


    <div class="header">

        <h1>
            Pesanan Saya
        </h1>


        <p>
            Lihat riwayat dan status pesanan kamu.
        </p>

    </div>


    <?php if ($result->num_rows > 0): ?>


        <?php while ($order = $result->fetch_assoc()): ?>


            <?php

            /*
            |--------------------------------------------------------------------------
            | Tentukan Class Status
            |--------------------------------------------------------------------------
            */

            $statusClass = "waiting";


            if (
                $order["order_status"]
                === "Diproses"
            ) {

                $statusClass = "process";

            } elseif (
                $order["order_status"]
                === "Dikirim"
            ) {

                $statusClass = "shipping";

            } elseif (
                $order["order_status"]
                === "Selesai"
            ) {

                $statusClass = "success";

            } elseif (
                $order["order_status"]
                === "Dibatalkan"
            ) {

                $statusClass = "cancel";

            }

            ?>


            <div class="order-card">


                <!-- ==================================================
                     ORDER TOP
                ================================================== -->

                <div class="order-top">

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
                     ORDER INFO
                ================================================== -->

                <div class="order-info">


                    <div class="info-box">

                        <div class="info-label">
                            Metode Pembayaran
                        </div>


                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order["payment_method"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-box">

                        <div class="info-label">
                            Status Pembayaran
                        </div>


                        <div class="info-value payment-status">

                            <?= htmlspecialchars(
                                $order["payment_status"]
                            ) ?>

                        </div>

                    </div>


                    <div class="info-box">

                        <div class="info-label">
                            Total Pesanan
                        </div>


                        <div class="info-value">

                            Rp
                            <?= number_format(
                                $order["total"],
                                0,
                                ",",
                                "."
                            ) ?>

                        </div>

                    </div>


                    <div class="info-box">

                        <div class="info-label">
                            Ongkos Kirim
                        </div>


                        <div class="info-value">

                            Rp
                            <?= number_format(
                                $order["shipping"],
                                0,
                                ",",
                                "."
                            ) ?>

                        </div>

                    </div>


                </div>


                <!-- ==================================================
                     BUTTON
                ================================================== -->

                <div class="order-bottom">

                    <a
                        href="detail_pesanan.php?id=<?= $order["id"] ?>"
                        class="btn"
                    >
                        Lihat Detail →
                    </a>

                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <!-- ==================================================
             EMPTY STATE
        ================================================== -->

        <div class="empty">

            <div class="empty-icon">
                📦
            </div>


            <h2>
                Belum Ada Pesanan
            </h2>


            <p>
                Kamu belum memiliki pesanan.
                Yuk mulai belanja barang preloved favoritmu.
            </p>


            <a
                href="produk.php"
                class="btn"
            >
                🛍️ Mulai Belanja
            </a>

        </div>


    <?php endif; ?>


</div>

</body>

</html>
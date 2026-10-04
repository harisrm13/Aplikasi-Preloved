<?php

session_start();

require_once "auth_admin.php";
require_once "../db.php";

// Hanya admin
if (($_SESSION["username"] ?? "") !== "admin") {
    header("Location: ../index.php");
    exit;
}

// Ambil semua pesanan
$result = $conn->query("
    SELECT
        orders.*,
        users.name AS customer_name,
        users.email AS customer_email
    FROM orders
    INNER JOIN users
        ON orders.user_id = users.id
    ORDER BY orders.id DESC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Kelola Pesanan - Admin</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
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

            animation: navbarDown 0.5s ease-out;
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

            animation: logoSlide 0.6s ease-out;
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

            animation: menuFade 0.7s ease-out;
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


        /* ACTIVE */

        .nav-menu a.active {
            color: #ffffff;
        }


        .nav-menu a.active::after {
            transform: scaleX(1);
        }


        /* HOVER */

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

            max-width: 1250px;

            margin: 40px auto;

            padding: 0 20px;
        }


        /* ==================================================
           HEADER
        ================================================== */

        .header {
            margin-bottom: 25px;

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


        .header h1 {

            font-size: 30px;

            margin-bottom: 8px;
        }


        .header p {
            color: #777;
        }


        /* ==================================================
           TABLE
        ================================================== */

        .table-wrapper {

            background: #ffffff;

            border-radius: 18px;

            overflow-x: auto;

            box-shadow:
                0 6px 20px rgba(0, 0, 0, 0.06);

            border:
                1px solid #eeeeee;

            animation:
                tableFade 0.7s ease-out;
        }


        @keyframes tableFade {

            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1100px;
        }


        th {

            background: #111827;

            color: #ffffff;

            padding: 16px 15px;

            text-align: left;

            font-size: 14px;

            font-weight: 600;

            white-space: nowrap;
        }


        th:first-child {
            border-radius: 12px 0 0 0;
        }


        th:last-child {
            border-radius: 0 12px 0 0;
        }


        td {

            padding: 16px 15px;

            border-bottom:
                1px solid #eeeeee;

            vertical-align: middle;

            font-size: 14px;
        }


        tbody tr {

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        tbody tr:hover {
            background: #fafafa;
        }


        tr:last-child td {
            border-bottom: none;
        }


        /* ==================================================
           ORDER
        ================================================== */

        .order-code {

            font-weight: bold;

            color: #111;

            white-space: nowrap;
        }


        .customer-name {

            font-weight: bold;

            margin-bottom: 5px;
        }


        .email {

            color: #777;

            font-size: 13px;
        }


        .total {

            font-weight: bold;

            color: #111;

            white-space: nowrap;
        }


        /* ==================================================
           BADGE STATUS
        ================================================== */

        .badge {

            display: inline-block;

            padding: 7px 11px;

            border-radius: 8px;

            font-size: 12px;

            font-weight: bold;

            white-space: nowrap;
        }


        .waiting {

            background: #fff1cc;

            color: #8a6500;
        }


        .paid {

            background: #dff6e5;

            color: #16753a;
        }


        .processing {

            background: #e5efff;

            color: #245da8;
        }


        .shipping {

            background: #eee5ff;

            color: #6842a5;
        }


        .finished {

            background: #dff6e5;

            color: #16753a;
        }


        .cancelled {

            background: #ffe5e5;

            color: #b00020;
        }


        /* ==================================================
           PAYMENT METHOD
        ================================================== */

        .payment-method {

            display: inline-block;

            margin-top: 7px;

            color: #777;

            font-size: 12px;
        }


        /* ==================================================
           DETAIL BUTTON
        ================================================== */

        .btn-detail {

            display: inline-block;

            background: #111827;

            color: #ffffff;

            text-decoration: none;

            padding: 9px 14px;

            border-radius: 8px;

            font-size: 13px;

            font-weight: bold;

            transition:
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .btn-detail:hover {

            background: #374151;

            transform: translateY(-2px);

            box-shadow:
                0 6px 15px rgba(0, 0, 0, 0.12);
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty {

            text-align: center;

            padding: 60px 20px;

            color: #777;
        }


        .empty h3 {

            color: #333;

            margin-bottom: 8px;
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


        @media (max-width: 768px) {

            .container {

                margin-top: 25px;

                padding: 0 15px;
            }


            .header h1 {
                font-size: 25px;
            }


            .header p {
                font-size: 14px;
            }


            .table-wrapper {
                border-radius: 14px;
            }


            th,
            td {

                padding: 12px;

                font-size: 13px;
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

    <div class="container">


        <div class="header">

            <h1>
                Kelola Pesanan
            </h1>

            <p>
                Lihat dan kelola seluruh pesanan pelanggan.
            </p>

        </div>


        <!-- ==================================================
             TABLE
        ================================================== -->

        <div class="table-wrapper">

            <?php if ($result->num_rows > 0): ?>

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
                                Tanggal
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
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while ($order = $result->fetch_assoc()): ?>

                            <tr>


                                <!-- KODE PESANAN -->

                                <td>

                                    <div class="order-code">

                                        <?= htmlspecialchars(
                                            $order["order_code"]
                                        ) ?>

                                    </div>

                                </td>


                                <!-- PELANGGAN -->

                                <td>

                                    <div class="customer-name">

                                        <?= htmlspecialchars(
                                            $order["customer_name"]
                                        ) ?>

                                    </div>

                                    <div class="email">

                                        <?= htmlspecialchars(
                                            $order["customer_email"]
                                        ) ?>

                                    </div>

                                </td>


                                <!-- TANGGAL -->

                                <td>

                                    <?= date(
                                        "d/m/Y H:i",
                                        strtotime(
                                            $order["created_at"]
                                        )
                                    ) ?>

                                </td>


                                <!-- TOTAL -->

                                <td class="total">

                                    Rp <?= number_format(
                                        $order["total"],
                                        0,
                                        ",",
                                        "."
                                    ) ?>

                                </td>


                                <!-- PEMBAYARAN -->

                                <td>

                                    <?php

                                    $paymentClass = "waiting";

                                    if (
                                        $order["payment_status"] ===
                                        "Dibayar"
                                    ) {

                                        $paymentClass = "paid";

                                    }

                                    if (
                                        $order["payment_status"] ===
                                        "Selesai"
                                    ) {

                                        $paymentClass = "finished";

                                    }

                                    ?>

                                    <span
                                        class="badge <?= $paymentClass ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $order["payment_status"]
                                        ) ?>

                                    </span>

                                    <br>

                                    <span class="payment-method">

                                        <?= htmlspecialchars(
                                            $order["payment_method"] ??
                                            "-"
                                        ) ?>

                                    </span>

                                </td>


                                <!-- STATUS PESANAN -->

                                <td>

                                    <?php

                                    $statusClass = "waiting";

                                    switch (
                                        $order["order_status"]
                                    ) {

                                        case "Diproses":

                                            $statusClass =
                                                "processing";

                                            break;

                                        case "Dikirim":

                                            $statusClass =
                                                "shipping";

                                            break;

                                        case "Selesai":

                                            $statusClass =
                                                "finished";

                                            break;

                                        case "Dibatalkan":

                                            $statusClass =
                                                "cancelled";

                                            break;

                                    }

                                    ?>

                                    <span
                                        class="badge <?= $statusClass ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $order["order_status"]
                                        ) ?>

                                    </span>

                                </td>


                                <!-- DETAIL -->

                                <td>

                                    <a
                                        href="detail_pesanan.php?id=<?= (int) $order["id"] ?>"
                                        class="btn-detail"
                                    >
                                        Detail
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <div class="empty">

                    <h3>
                        Belum ada pesanan
                    </h3>

                    <p>
                        Pesanan pelanggan akan muncul di sini.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>


</body>

</html>
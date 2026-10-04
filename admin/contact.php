<?php

require_once "auth_admin.php";
require_once "../db.php";

// Cek admin
if (!isset($_SESSION["user_id"]) || $_SESSION["username"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Ambil semua pesan contact
$result = $conn->query("
    SELECT *
    FROM contacts
    ORDER BY created_at DESC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pesan Contact - SecondSpace Admin</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6f8;
            color: #222;
        }

        /* =========================
           NAVBAR
        ========================= */

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

        /* =========================
           LOGO
        ========================= */

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

        /* =========================
           NAV MENU
        ========================= */

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

        /* Garis bawah */
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

            transition: transform 0.35s ease;
        }

        /* Menu aktif */
        .nav-menu a.active {
            color: #ffffff;
        }

        .nav-menu a.active::after {
            transform: scaleX(1);
        }

        /* Hover */
        .nav-menu a:hover::after {
            transform: scaleX(1);
        }

        .nav-menu a:hover {
            color: #9ff3d0;
            transform: translateY(-2px);
        }

        /* =========================
           LOGOUT
        ========================= */

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

        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 92%;
            max-width: 1150px;
            margin: 35px auto;

            animation: contentFade 0.6s ease-out;
        }

        @keyframes contentFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================
           HEADER
        ========================= */

        .top {
            margin-bottom: 25px;
        }

        .top h1 {
            margin: 0;
            font-size: 28px;
            color: #111827;
            font-weight: 800;
        }

        .top p {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        /* =========================
           CARD
        ========================= */

        .card {
            background: #ffffff;
            border-radius: 14px;
            padding: 22px;

            box-shadow:
                0 4px 18px rgba(0, 0, 0, 0.06);

            animation: cardFade 0.7s ease-out;
        }

        @keyframes cardFade {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================
           TABLE WRAPPER
        ========================= */

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        /* =========================
           TABLE
        ========================= */

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background: #111827;
            color: #ffffff;
            text-align: left;
            padding: 14px;
            font-size: 14px;
            vertical-align: top;
        }

        th:first-child {
            border-radius: 12px 0 0 0;
        }

        th:last-child {
            border-radius: 0 12px 0 0;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eeeeee;
            font-size: 14px;
            vertical-align: top;
            color: #374151;
        }

        tbody tr {
            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        /* =========================
           NOMOR
        ========================= */

        td:first-child {
            color: #6b7280;
            font-weight: 600;
            width: 60px;
        }

        /* =========================
           NAMA
        ========================= */

        td:nth-child(2) {
            font-weight: 600;
            color: #111827;
        }

        /* =========================
           EMAIL
        ========================= */

        td:nth-child(3) {
            color: #4b5563;
        }

        /* =========================
           PESAN
        ========================= */

        .message {
            max-width: 400px;
            line-height: 1.6;
            white-space: normal;
            word-break: break-word;
            color: #374151;
        }

        /* =========================
           TANGGAL
        ========================= */

        .date {
            color: #6b7280;
            white-space: nowrap;
        }

        /* =========================
           PESAN KOSONG
        ========================= */

        .empty {
            text-align: center;
            padding: 50px 20px;
            color: #777777;
        }

        /* =========================
           RESPONSIVE
        ========================= */

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

            .container {
                width: 94%;
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

            .container {
                margin: 25px auto;
            }

            .top h1 {
                font-size: 24px;
            }

            .card {
                padding: 15px;
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

            .top h1 {
                font-size: 22px;
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

            <a href="index.php">
                Dashboard
            </a>

            <a href="produk.php">
                Produk
            </a>

            <a href="pesanan.php">
                Pesanan
            </a>

            <a href="contact.php" class="active">
                Contact
            </a>

            <a href="../logout.php" class="logout">
                Logout
            </a>

        </div>

    </nav>


    <!-- =========================
         CONTENT
    ========================= -->

    <div class="container">

        <div class="top">

            <h1>
                Pesan Contact Us
            </h1>

            <p>
                Kelola dan lihat pesan yang dikirim oleh pengguna.
            </p>

        </div>


        <div class="card">

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Pesan</th>
                            <th>Tanggal</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($result && $result->num_rows > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($row = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?= $no++ ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["name"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["email"]) ?>
                                    </td>

                                    <td>

                                        <div class="message">
                                            <?= htmlspecialchars($row["message"]) ?>
                                        </div>

                                    </td>

                                    <td class="date">

                                        <?= date(
                                            "d-m-Y H:i",
                                            strtotime($row["created_at"])
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="5" class="empty">
                                    Belum ada pesan masuk.
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
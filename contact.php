<?php

session_start();

require_once "db.php";

$success = "";
$error = "";

$name = "";
$email = "";
$message = "";

/*
|--------------------------------------------------------------------------
| Proses Form Contact
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $message === "") {

        $error = "Semua data wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO contacts
            (name, email, message)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $name,
            $email,
            $message
        );

        if ($stmt->execute()) {

            $success =
                "Pesan berhasil dikirim. Terima kasih sudah menghubungi kami.";

            $name = "";
            $email = "";
            $message = "";

        } else {

            $error =
                "Pesan gagal dikirim. Silakan coba lagi.";
        }
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
        Contact - SecondSpace PreLoved
    </title>

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


        /* =========================
           LOGO
        ========================= */

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

            animation:
                logoSlide 0.6s ease-out;
        }

        .navbar .logo-second {
            color: #ffffff;
        }

        .navbar .logo-space {
            color: #9ff3d0;
        }

        .navbar .logo:hover {

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


        /* =========================
           UNDERLINE
        ========================= */

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


        /* ACTIVE CONTACT */

        .nav-menu a.active {

            color: #ffff;
        }

        .nav-menu a.active::after {

            transform: scaleX(1);
        }


        /* HOVER */

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

            max-width: 1100px;

            margin: 45px auto;

            padding: 0 20px 60px;
        }


        /* =========================
           HEADER
        ========================= */

        .header {

            text-align: center;

            margin-bottom: 35px;
        }

        .header h1 {

            font-size: 34px;

            margin-bottom: 10px;
        }

        .header p {

            color: #777;

            font-size: 15px;

            line-height: 1.6;
        }


        /* =========================
           GRID
        ========================= */

        .contact-grid {

            display: grid;

            grid-template-columns:
                0.85fr 1.15fr;

            gap: 25px;

            align-items: start;
        }


        /* =========================
           CARD
        ========================= */

        .card {

            background: white;

            border-radius: 20px;

            padding: 30px;

            border: 1px solid #eee;

            box-shadow:
                0 8px 28px rgba(0, 0, 0, 0.055);

            transition:
                box-shadow 0.25s ease,
                transform 0.25s ease;
        }

        .card:hover {

            box-shadow:
                0 14px 35px rgba(0, 0, 0, 0.08);

            transform: translateY(-2px);
        }

        .card h2 {

            font-size: 21px;

            margin-bottom: 25px;
        }


        /* =========================
           INFO
        ========================= */

        .contact-info {

            display: flex;

            gap: 14px;

            padding: 17px 0;

            border-bottom: 1px solid #eee;
        }

        .contact-info:first-of-type {

            padding-top: 0;
        }

        .contact-info:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }

        .contact-icon {

            flex-shrink: 0;

            width: 43px;

            height: 43px;

            background: #f3f3f1;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;
        }

        .contact-content {

            flex: 1;
        }

        .contact-title {

            font-weight: bold;

            font-size: 14px;

            margin-bottom: 5px;
        }

        .contact-text {

            color: #777;

            font-size: 13px;

            line-height: 1.6;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {

            margin-bottom: 19px;
        }

        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {

            width: 100%;

            padding: 13px 14px;

            border: 1px solid #ddd;

            border-radius: 11px;

            font-size: 14px;

            background: #fafafa;

            color: #222;

            outline: none;

            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {

            background: white;

            border-color: #222;

            box-shadow:
                0 0 0 3px rgba(0, 0, 0, 0.05);
        }

        .form-group textarea {

            min-height: 155px;

            resize: vertical;

            line-height: 1.6;
        }


        /* =========================
           ALERT
        ========================= */

        .success,
        .error {

            padding: 13px 15px;

            border-radius: 11px;

            margin-bottom: 20px;

            font-size: 13px;

            line-height: 1.5;
        }

        .success {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;
        }

        .error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fecaca;
        }


        /* =========================
           BUTTON
        ========================= */

        .btn {

            width: 100%;

            border: none;

            padding: 14px 18px;

            background: #1c1c1c;

            color: white;

            border-radius: 11px;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;

            transition:
                background 0.25s ease,
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .btn:hover {

            background: #3a3a3a;

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(0, 0, 0, 0.13);
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 850px) {

            .navbar {

                padding: 16px 20px;
            }

            .nav-menu {

                gap: 13px;
            }

            .nav-menu a {

                font-size: 12px;
            }

            .contact-grid {

                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 650px) {

            .navbar {

                padding: 14px 5%;
            }

            .navbar .logo {

                font-size: 18px;
            }

            .nav-menu {

                gap: 12px;
            }

            .nav-menu a {

                font-size: 12px;
            }

        }


        @media (max-width: 600px) {

            .navbar {

                flex-direction: column;

                gap: 12px;
            }

            .navbar .logo {

                margin-right: 0;
            }

            .nav-menu {

                flex-wrap: wrap;

                justify-content: center;

                margin-left: 0;
            }

            .container {

                margin-top: 30px;
            }

            .header h1 {

                font-size: 28px;
            }

            .card {

                padding: 22px;
            }

        }

    </style>

</head>

<body>


    <!-- =========================
         NAVBAR
    ========================== -->

    <nav class="navbar">

        <a href="index.php" class="logo">

            <span class="logo-second">
                Second
            </span><span class="logo-space">
                Space
            </span>

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

            <a href="pesanan.php">
                Pesanan
            </a>

            <a href="alamat.php">
                Alamat
            </a>

            <a
                href="contact.php"
                class="active"
            >
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


    <!-- =========================
         MAIN
    ========================= -->

    <div class="container">


        <!-- HEADER -->

        <div class="header">

            <h1>
                Hubungi Kami
            </h1>

            <p>
                Punya pertanyaan atau ingin mengetahui lebih lanjut?
                Silakan hubungi SecondSpace.
            </p>

        </div>


        <!-- CONTACT GRID -->

        <div class="contact-grid">


            <!-- =========================
                 INFORMASI
            ========================== -->

            <div class="card">

                <h2>
                    SecondSpace
                </h2>


                <div class="contact-info">

                    <div class="contact-icon">
                        📍
                    </div>

                    <div class="contact-content">

                        <div class="contact-title">
                            Lokasi
                        </div>

                        <div class="contact-text">
                            Jakarta, Indonesia
                        </div>

                    </div>

                </div>


                <div class="contact-info">

                    <div class="contact-icon">
                        📧
                    </div>

                    <div class="contact-content">

                        <div class="contact-title">
                            Email
                        </div>

                        <div class="contact-text">
                            secondspace@gmail.com
                        </div>

                    </div>

                </div>


                <div class="contact-info">

                    <div class="contact-icon">
                        📱
                    </div>

                    <div class="contact-content">

                        <div class="contact-title">
                            WhatsApp
                        </div>

                        <div class="contact-text">
                            08xxxxxxxxxx
                        </div>

                    </div>

                </div>


                <div class="contact-info">

                    <div class="contact-icon">
                        🛍️
                    </div>

                    <div class="contact-content">

                        <div class="contact-title">
                            Tentang SecondSpace
                        </div>

                        <div class="contact-text">
                            SecondSpace adalah toko preloved
                            yang menyediakan berbagai macam
                            pakaian dan fashion berkualitas
                            dengan harga terjangkau.
                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================
                 FORM
            ========================== -->

            <div class="card">

                <h2>
                    Kirim Pesan
                </h2>


                <?php if ($success !== ""): ?>

                    <div class="success">

                        <?= htmlspecialchars(
                            $success
                        ) ?>

                    </div>

                <?php endif; ?>


                <?php if ($error !== ""): ?>

                    <div class="error">

                        <?= htmlspecialchars(
                            $error
                        ) ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action="contact.php"
                >


                    <div class="form-group">

                        <label>
                            Nama
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars(
                                $name
                            ) ?>"
                            placeholder="Masukkan nama"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars(
                                $email
                            ) ?>"
                            placeholder="Masukkan email"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Pesan
                        </label>

                        <textarea
                            name="message"
                            placeholder="Tulis pesan kamu..."
                            required
                        ><?= htmlspecialchars(
                            $message
                        ) ?></textarea>

                    </div>


                    <button
                        type="submit"
                        class="btn"
                    >
                        Kirim Pesan
                    </button>


                </form>

            </div>


        </div>


    </div>

</body>

</html>
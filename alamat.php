<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$message = "";
$error = "";

/* =========================
   SIMPAN ALAMAT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $recipientName = trim($_POST["recipient_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $province = trim($_POST["province"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $postalCode = trim($_POST["postal_code"] ?? "");

    if (
        $recipientName === "" ||
        $phone === "" ||
        $address === "" ||
        $province === "" ||
        $city === "" ||
        $postalCode === ""
    ) {
        $error = "Semua data alamat wajib diisi.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO addresses
            (user_id, recipient_name, phone, address, province, city, postal_code)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issssss",
            $userId,
            $recipientName,
            $phone,
            $address,
            $province,
            $city,
            $postalCode
        );

        if ($stmt->execute()) {
            $message = "Alamat berhasil disimpan.";
        } else {
            $error = "Alamat gagal disimpan.";
        }
    }
}

/* =========================
   AMBIL ALAMAT USER
========================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM addresses
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$addresses = $stmt->get_result();

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
    Alamat - SecondSpace PreLoved
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

    background: #f5f5f5;

    color: #111;
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

.nav-links {
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


.nav-links a {
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

.nav-links a::after {
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

.nav-links a.active {
    color: #ffffff;
}


.nav-links a.active::after {
    transform:
        scaleX(1);
}


/* ==================================================
   HOVER
================================================== */

.nav-links a:hover::after {
    transform:
        scaleX(1);
}


.nav-links a:hover {
    color: #9ff3d0;

    transform:
        translateY(-2px);
}


/* ==================================================
   LOGOUT
================================================== */

.nav-links a.logout {
    background: #374151;

    color: #ffffff;

    padding: 9px 20px;

    border-radius: 10px;

    transition:
        background 0.25s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease;
}


.nav-links a.logout::after {
    display: none;
}


.nav-links a.logout:hover {
    background: #4b5563;

    color: #ffffff;

    transform:
        translateY(-2px);

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.18);
}


/* =========================
   CONTAINER
========================= */

.container {
    max-width: 1150px;

    margin: 0 auto;

    padding:
        50px 24px 70px;
}


/* =========================
   HEADER
========================= */

.page-header {
    margin-bottom: 30px;
}


.page-header .eyebrow {
    display: inline-block;

    font-size: 12px;

    font-weight: 700;

    color: #666;

    text-transform: uppercase;

    letter-spacing: 1.5px;

    margin-bottom: 10px;
}


.page-header h1 {
    margin: 0;

    font-size: 36px;

    line-height: 1.15;

    letter-spacing: -1px;
}


.page-header p {
    margin: 10px 0 0;

    color: #777;

    font-size: 15px;

    line-height: 1.6;
}


/* =========================
   ALERT
========================= */

.alert {
    border-radius: 12px;

    padding:
        14px 16px;

    margin-bottom: 22px;

    font-size: 14px;

    font-weight: 600;
}


.success {
    background: #eaf8ef;

    color: #16713b;

    border:
        1px solid #ccebd7;
}


.error {
    background: #fff0f0;

    color: #b42318;

    border:
        1px solid #f4caca;
}


/* =========================
   MAIN GRID
========================= */

.address-grid {
    display: grid;

    grid-template-columns:
        0.95fr 1.05fr;

    gap: 25px;

    align-items: start;
}


/* =========================
   CARD
========================= */

.card {
    background: #ffffff;

    border:
        1px solid #e9e9e9;

    border-radius: 18px;

    padding: 28px;

    box-shadow:
        0 8px 30px
        rgba(0, 0, 0, 0.05);

    transition:
        0.25s ease;
}


.card:hover {
    box-shadow:
        0 14px 38px
        rgba(0, 0, 0, 0.08);
}


.card-header {
    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 24px;
}


.card-header h2 {
    margin: 0;

    font-size: 20px;
}


.card-header p {
    margin: 5px 0 0;

    color: #888;

    font-size: 13px;
}


.card-badge {
    white-space: nowrap;

    background: #f1f1f1;

    color: #555;

    padding:
        7px 10px;

    border-radius: 999px;

    font-size: 11px;

    font-weight: 700;
}


/* =========================
   FORM
========================= */

.form-group {
    margin-bottom: 17px;
}


.form-group label {
    display: block;

    margin-bottom: 8px;

    font-size: 13px;

    font-weight: 700;

    color: #333;
}


.form-group input,
.form-group textarea {
    width: 100%;

    padding:
        13px 14px;

    border:
        1px solid #ddd;

    border-radius: 10px;

    background: #fafafa;

    color: #111;

    font-family: inherit;

    font-size: 14px;

    outline: none;

    transition:
        0.2s ease;
}


.form-group input:focus,
.form-group textarea:focus {
    background: #ffffff;

    border-color: #111;

    box-shadow:
        0 0 0 3px
        rgba(0, 0, 0, 0.06);
}


.form-group textarea {
    min-height: 105px;

    resize: vertical;
}


.form-row {
    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 14px;
}


/* =========================
   BUTTON
========================= */

.btn-save {
    width: 100%;

    border: none;

    border-radius: 10px;

    padding:
        14px 18px;

    background: #111;

    color: #ffffff;

    font-size: 14px;

    font-weight: 700;

    cursor: pointer;

    transition:
        0.2s ease;
}


.btn-save:hover {
    background: #292929;

    transform:
        translateY(-1px);

    box-shadow:
        0 7px 18px
        rgba(0, 0, 0, 0.15);
}


/* =========================
   ADDRESS LIST
========================= */

.address-list {
    display: flex;

    flex-direction: column;

    gap: 14px;
}


.address-item {
    position: relative;

    border:
        1px solid #e8e8e8;

    border-radius: 14px;

    padding: 18px;

    background: #fafafa;

    transition:
        0.2s ease;
}


.address-item:hover {
    background: #ffffff;

    border-color: #d7d7d7;

    transform:
        translateY(-2px);

    box-shadow:
        0 8px 20px
        rgba(0, 0, 0, 0.05);
}


.address-top {
    display: flex;

    justify-content:
        space-between;

    align-items: flex-start;

    gap: 15px;

    margin-bottom: 12px;
}


.address-name {
    margin: 0;

    font-size: 16px;

    font-weight: 800;
}


.address-label {
    display: inline-block;

    margin-top: 5px;

    font-size: 11px;

    font-weight: 700;

    color: #777;

    text-transform: uppercase;

    letter-spacing: 0.8px;
}


.address-phone {
    color: #555;

    font-size: 13px;

    margin-bottom: 12px;
}


.address-phone span {
    margin-right: 5px;
}


.address-text {
    color: #555;

    font-size: 13px;

    line-height: 1.65;

    margin:
        0 0 8px;
}


.address-location {
    color: #333;

    font-size: 13px;

    font-weight: 600;

    line-height: 1.5;

    margin: 0;
}


.address-number {
    min-width: 30px;

    height: 30px;

    display: flex;

    justify-content: center;

    align-items: center;

    border-radius: 50%;

    background: #111;

    color: #ffffff;

    font-size: 12px;

    font-weight: 700;
}


/* =========================
   EMPTY
========================= */

.empty {
    text-align: center;

    padding:
        45px 20px;

    border:
        1px dashed #d8d8d8;

    border-radius: 14px;

    color: #777;

    background: #fafafa;
}


.empty-icon {
    font-size: 32px;

    margin-bottom: 10px;
}


.empty h3 {
    margin:
        0 0 7px;

    font-size: 16px;

    color: #333;
}


.empty p {
    margin: 0;

    font-size: 13px;

    line-height: 1.5;
}


/* =========================
   FOOTER INFO
========================= */

.info-box {
    margin-top: 20px;

    padding:
        14px 16px;

    border-radius: 12px;

    background: #f5f5f5;

    color: #666;

    font-size: 12px;

    line-height: 1.6;
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 900px) {

    .navbar {
        padding:
            16px 20px;
    }

    .nav-links {
        gap: 13px;
    }

    .nav-links a {
        font-size: 12px;
    }

    .address-grid {
        grid-template-columns: 1fr;
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


    .nav-links {
        width: 100%;

        justify-content: center;

        flex-wrap: wrap;

        gap: 12px;

        margin-left: 0;
    }


    .nav-links a {
        white-space: nowrap;
    }


    .container {
        padding:
            35px 16px 50px;
    }


    .page-header h1 {
        font-size: 30px;
    }


    .card {
        padding: 20px;

        border-radius: 15px;
    }


    .form-row {
        grid-template-columns: 1fr;

        gap: 0;
    }


    .card-header {
        align-items: flex-start;

        flex-direction: column;
    }

}


@media (max-width: 400px) {

    .nav-links {
        gap: 10px;
    }


    .nav-links a {
        font-size: 11px;
    }


    .nav-links a.logout {
        padding:
            8px 14px;
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

        <span class="logo-second">
            Second
        </span><span class="logo-space">Space</span>

    </a>


    <div class="nav-links">

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


        <a
            href="alamat.php"
            class="active"
        >
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
     CONTENT
================================================== -->

<main class="container">


    <div class="page-header">

        <span class="eyebrow">
            Account Settings
        </span>


        <h1>
            Alamat Pengiriman
        </h1>


        <p>
            Kelola alamat yang dapat digunakan untuk
            pengiriman pesananmu.
        </p>

    </div>


    <?php if ($message !== ""): ?>

        <div class="alert success">

            ✓ <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="alert error">

            ⚠ <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <div class="address-grid">


        <!-- ==================================================
             FORM TAMBAH ALAMAT
        ================================================== -->

        <div class="card">


            <div class="card-header">

                <div>

                    <h2>
                        Tambah Alamat
                    </h2>


                    <p>
                        Masukkan alamat untuk kebutuhan pengiriman.
                    </p>

                </div>


                <div class="card-badge">
                    ALAMAT BARU
                </div>

            </div>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Nama Penerima
                    </label>


                    <input
                        type="text"
                        name="recipient_name"
                        placeholder="Masukkan nama penerima"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        No. Telepon
                    </label>


                    <input
                        type="text"
                        name="phone"
                        placeholder="08xxxxxxxxxx"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Alamat Lengkap
                    </label>


                    <textarea
                        name="address"
                        placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, dll."
                        required
                    ></textarea>

                </div>


                <div class="form-row">


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
                            Kota/Kabupaten
                        </label>


                        <input
                            type="text"
                            name="city"
                            placeholder="Contoh: Jakarta Selatan"
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
                        placeholder="Contoh: 12345"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn-save"
                >
                    Simpan Alamat
                </button>


            </form>


        </div>


        <!-- ==================================================
             DAFTAR ALAMAT
        ================================================== -->

        <div class="card">


            <div class="card-header">


                <div>

                    <h2>
                        Alamat Saya
                    </h2>


                    <p>
                        Daftar alamat yang tersimpan.
                    </p>

                </div>


                <div class="card-badge">

                    <?= $addresses->num_rows ?>

                    ALAMAT

                </div>


            </div>


            <?php if ($addresses->num_rows > 0): ?>


                <div class="address-list">


                    <?php
                    $number = 1;
                    ?>


                    <?php while (
                        $address =
                        $addresses->fetch_assoc()
                    ): ?>


                        <div class="address-item">


                            <div class="address-top">


                                <div>


                                    <h3 class="address-name">

                                        <?= htmlspecialchars(
                                            $address["recipient_name"]
                                        ) ?>

                                    </h3>


                                    <div class="address-label">
                                        Penerima
                                    </div>


                                </div>


                                <div class="address-number">

                                    <?= $number ?>

                                </div>


                            </div>


                            <div class="address-phone">

                                <span>📞</span>

                                <?= htmlspecialchars(
                                    $address["phone"]
                                ) ?>

                            </div>


                            <p class="address-text">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $address["address"]
                                    )
                                ) ?>

                            </p>


                            <p class="address-location">

                                <?= htmlspecialchars(
                                    $address["city"]
                                ) ?>,

                                <?= htmlspecialchars(
                                    $address["province"]
                                ) ?>

                                <?= htmlspecialchars(
                                    $address["postal_code"]
                                ) ?>

                            </p>


                        </div>


                        <?php
                        $number++;
                        ?>


                    <?php endwhile; ?>


                </div>


            <?php else: ?>


                <div class="empty">


                    <div class="empty-icon">
                        📍
                    </div>


                    <h3>
                        Belum Ada Alamat
                    </h3>


                    <p>
                        Tambahkan alamat pengiriman terlebih dahulu
                        agar proses checkout lebih mudah.
                    </p>


                </div>


            <?php endif; ?>


            <div class="info-box">

                💡 <strong>Tips:</strong>

                Pastikan nama penerima, nomor telepon,
                alamat, kota, provinsi, dan kode pos sudah
                benar sebelum digunakan untuk pesanan.

            </div>


        </div>


    </div>


</main>

</body>

</html>
<?php

require_once "auth_admin.php";
require_once "../db.php";

$error = "";
$success = "";

$name = "";
$category = "";
$price = "";
$condition_text = "";
$description = "";
$stock = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $condition_text = trim($_POST["condition_text"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $stock = trim($_POST["stock"] ?? "");

    $allowedCategories = [
        "Baju",
        "Kemeja",
        "Celana",
        "Sepatu"
    ];

    if (
        $name === "" ||
        $category === "" ||
        $price === "" ||
        $condition_text === "" ||
        $stock === ""
    ) {

        $error = "Nama, kategori, harga, kondisi, dan stok wajib diisi.";

    } elseif (!in_array($category, $allowedCategories, true)) {

        $error = "Kategori produk tidak valid.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Harga produk tidak valid.";

    } elseif (!ctype_digit($stock) || (int)$stock < 0) {

        $error = "Stok produk tidak valid.";

    } else {

        $imageName = null;

        /* ==============================
           UPLOAD GAMBAR
        ============================== */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $error = "Gagal mengupload gambar.";

            } elseif (
                $_FILES["image"]["size"] > 5 * 1024 * 1024
            ) {

                $error = "Ukuran gambar maksimal 5 MB.";

            } else {

                $tmpName = $_FILES["image"]["tmp_name"];

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmpName);

                $allowedMime = [
                    "image/jpeg" => "jpg",
                    "image/png"  => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($allowedMime[$mime])) {

                    $error = "Format gambar harus JPG, PNG, atau WEBP.";

                } else {

                    $uploadDir = "../images/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    try {

                        $randomName = bin2hex(
                            random_bytes(8)
                        );

                    } catch (Exception $e) {

                        $randomName = uniqid();

                    }

                    $imageName =
                        time() .
                        "_" .
                        $randomName .
                        "." .
                        $allowedMime[$mime];

                    $destination =
                        $uploadDir .
                        $imageName;

                    if (
                        !move_uploaded_file(
                            $tmpName,
                            $destination
                        )
                    ) {

                        $error = "Gambar gagal disimpan.";

                    }
                }
            }
        }


        /* ==============================
           SIMPAN PRODUK
        ============================== */

        if ($error === "") {

            $priceValue = (float)$price;
            $stockValue = (int)$stock;

            $stmt = $conn->prepare("
                INSERT INTO products
                (
                    name,
                    category,
                    price,
                    condition_text,
                    description,
                    image,
                    stock
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {

                $error = "Query database gagal.";

                if (
                    $imageName &&
                    file_exists("../images/" . $imageName)
                ) {

                    unlink(
                        "../images/" . $imageName
                    );
                }

            } else {

                $stmt->bind_param(
                    "ssdsssi",
                    $name,
                    $category,
                    $priceValue,
                    $condition_text,
                    $description,
                    $imageName,
                    $stockValue
                );

                if ($stmt->execute()) {

                    $success =
                        "Produk berhasil ditambahkan.";

                    $name = "";
                    $category = "";
                    $price = "";
                    $condition_text = "";
                    $description = "";
                    $stock = "";

                } else {

                    $error =
                        "Produk gagal ditambahkan.";

                    if (
                        $imageName &&
                        file_exists(
                            "../images/" . $imageName
                        )
                    ) {

                        unlink(
                            "../images/" . $imageName
                        );
                    }
                }

                $stmt->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tambah Produk - SecondSpace Admin</title>

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


/* UNDERLINE */

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

.nav-menu a:hover::after {
    transform: scaleX(1);
}

.nav-menu a:hover {
    color: #9ff3d0;

    transform: translateY(-2px);
}


/* LOGOUT */

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

    max-width: 1050px;

    margin: 0 auto;

    padding:
        42px 20px 60px;
}


/* ==================================================
   HEADER
================================================== */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: flex-end;

    gap: 20px;

    margin-bottom: 30px;
}

.page-header h1 {

    font-size: 32px;

    letter-spacing: -1px;

    margin-bottom: 8px;
}

.page-header p {

    color: #6b7280;

    font-size: 15px;
}


/* ==================================================
   BACK BUTTON
================================================== */

.back-btn {

    text-decoration: none;

    color: #374151;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    padding: 11px 17px;

    border-radius: 10px;

    font-weight: 700;

    font-size: 14px;

    transition:
        0.2s;
}

.back-btn:hover {

    background: #f9fafb;

    transform:
        translateY(-1px);
}


/* ==================================================
   ALERT
================================================== */

.alert {

    padding:
        15px 18px;

    border-radius: 12px;

    margin-bottom: 22px;

    font-size: 14px;

    font-weight: 600;
}

.alert-success {

    background: #ecfdf5;

    color: #047857;

    border:
        1px solid #a7f3d0;
}

.alert-error {

    background: #fef2f2;

    color: #b91c1c;

    border:
        1px solid #fecaca;
}


/* ==================================================
   FORM CARD
================================================== */

.form-card {

    background: #ffffff;

    border:
        1px solid #e5e7eb;

    border-radius: 20px;

    padding: 32px;

    box-shadow:
        0 10px 35px rgba(0, 0, 0, 0.05);
}


/* ==================================================
   SECTION
================================================== */

.section {

    margin-bottom: 30px;
}

.section:last-child {
    margin-bottom: 0;
}

.section-title {

    font-size: 18px;

    font-weight: 800;

    margin-bottom: 18px;
}

.section-subtitle {

    color: #6b7280;

    font-size: 13px;

    margin-top: -10px;

    margin-bottom: 20px;
}


/* ==================================================
   FORM GRID
================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 20px;
}

.form-group {

    display: flex;

    flex-direction: column;
}

.form-group.full {

    grid-column:
        1 / -1;
}


/* ==================================================
   FORM
================================================== */

label {

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 8px;

    color: #374151;
}

input,
select,
textarea {

    width: 100%;

    border:
        1px solid #d1d5db;

    border-radius: 11px;

    padding:
        13px 14px;

    font-size: 14px;

    font-family: inherit;

    outline: none;

    background: #ffffff;

    transition:
        0.2s;
}

input:focus,
select:focus,
textarea:focus {

    border-color:
        #111827;

    box-shadow:
        0 0 0 3px
        rgba(17, 24, 39, 0.08);
}

textarea {

    min-height: 130px;

    resize: vertical;
}


/* ==================================================
   UPLOAD
================================================== */

.upload-box {

    border:
        2px dashed #d1d5db;

    border-radius: 15px;

    padding: 30px;

    text-align: center;

    background: #fafafa;

    transition:
        0.2s;
}

.upload-box:hover {

    border-color:
        #111827;

    background:
        #f9fafb;
}

.upload-icon {

    width: 50px;
    height: 50px;

    margin:
        0 auto 12px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 14px;

    background: #111827;

    color: #ffffff;

    font-size: 22px;
}

.upload-box strong {

    display: block;

    font-size: 15px;

    margin-bottom: 5px;
}

.upload-box p {

    font-size: 12px;

    color: #6b7280;

    margin-bottom: 15px;
}

.file-input {

    max-width: 350px;

    margin:
        0 auto;
}


/* ==================================================
   ACTION
================================================== */

.form-actions {

    display: flex;

    justify-content: flex-end;

    gap: 12px;

    padding-top: 25px;

    border-top:
        1px solid #eeeeee;
}

.btn-cancel {

    text-decoration: none;

    padding:
        13px 20px;

    border-radius: 10px;

    border:
        1px solid #d1d5db;

    color: #374151;

    font-size: 14px;

    font-weight: 700;

    transition:
        0.2s;
}

.btn-cancel:hover {

    background: #f9fafb;

    transform:
        translateY(-1px);
}

.btn-save {

    border: none;

    padding:
        13px 22px;

    border-radius: 10px;

    background: #111827;

    color: #ffffff;

    font-size: 14px;

    font-weight: 800;

    cursor: pointer;

    transition:
        0.2s;
}

.btn-save:hover {

    background: #000000;

    transform:
        translateY(-1px);
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 900px) {

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

        padding:
            30px 15px 45px;
    }

    .page-header {

        align-items:
            flex-start;

        flex-direction:
            column;
    }

    .page-header h1 {

        font-size: 27px;
    }

    .form-card {

        padding: 22px;

        border-radius: 16px;
    }

    .form-grid {

        grid-template-columns:
            1fr;
    }

    .form-group.full {

        grid-column:
            auto;
    }

    .form-actions {

        flex-direction:
            column-reverse;
    }

    .btn-save,
    .btn-cancel {

        width: 100%;

        text-align: center;
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
        </span>

        <span class="logo-space">
            Space
        </span>

    </a>


    <div class="nav-menu">

        <a
            href="index.php"
        >
            Dashboard
        </a>

        <a
            href="produk.php"
            class="active"
        >
            Produk
        </a>

        <a
            href="pesanan.php"
        >
            Pesanan
        </a>

        <a
            href="contact.php"
        >
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


    <div class="page-header">

        <div>

            <h1>
                Tambah Produk
            </h1>

            <p>
                Tambahkan produk preloved baru ke katalog SecondSpace.
            </p>

        </div>


        <a
            href="produk.php"
            class="back-btn"
        >
            ← Kembali ke Produk
        </a>

    </div>


    <!-- ALERT SUCCESS -->

    <?php if ($success !== ""): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <!-- ALERT ERROR -->

    <?php if ($error !== ""): ?>

        <div class="alert alert-error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- FORM -->

    <form
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >


        <!-- INFORMASI PRODUK -->

        <div class="section">

            <div class="section-title">
                Informasi Produk
            </div>

            <div class="section-subtitle">
                Isi informasi dasar produk yang akan ditampilkan.
            </div>


            <div class="form-grid">


                <!-- NAMA -->

                <div class="form-group full">

                    <label for="name">
                        Nama Produk
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($name) ?>"
                        placeholder="Contoh: Kaos Oversize Hitam"
                        required
                    >

                </div>


                <!-- KATEGORI -->

                <div class="form-group">

                    <label for="category">
                        Kategori
                    </label>

                    <select
                        id="category"
                        name="category"
                        required
                    >

                        <option value="">
                            Pilih kategori
                        </option>

                        <option
                            value="Baju"
                            <?= $category === "Baju" ? "selected" : "" ?>
                        >
                            Baju
                        </option>

                        <option
                            value="Kemeja"
                            <?= $category === "Kemeja" ? "selected" : "" ?>
                        >
                            Kemeja
                        </option>

                        <option
                            value="Celana"
                            <?= $category === "Celana" ? "selected" : "" ?>
                        >
                            Celana
                        </option>

                        <option
                            value="Sepatu"
                            <?= $category === "Sepatu" ? "selected" : "" ?>
                        >
                            Sepatu
                        </option>

                    </select>

                </div>


                <!-- KONDISI -->

                <div class="form-group">

                    <label for="condition_text">
                        Kondisi
                    </label>

                    <input
                        type="text"
                        id="condition_text"
                        name="condition_text"
                        value="<?= htmlspecialchars($condition_text) ?>"
                        placeholder="Contoh: Sangat Baik"
                        required
                    >

                </div>


                <!-- HARGA -->

                <div class="form-group">

                    <label for="price">
                        Harga
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="<?= htmlspecialchars($price) ?>"
                        placeholder="60000"
                        min="0"
                        step="1000"
                        required
                    >

                </div>


                <!-- STOK -->

                <div class="form-group">

                    <label for="stock">
                        Stok
                    </label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        value="<?= htmlspecialchars($stock) ?>"
                        placeholder="1"
                        min="0"
                        required
                    >

                </div>


                <!-- DESKRIPSI -->

                <div class="form-group full">

                    <label for="description">
                        Deskripsi Produk
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Tuliskan deskripsi produk..."
                    ><?= htmlspecialchars($description) ?></textarea>

                </div>

            </div>

        </div>


        <!-- FOTO PRODUK -->

        <div class="section">

            <div class="section-title">
                Foto Produk
            </div>

            <div class="section-subtitle">
                Gunakan foto yang jelas agar produk terlihat menarik.
            </div>


            <div class="upload-box">

                <div class="upload-icon">
                    ↑
                </div>

                <strong>
                    Upload Foto Produk
                </strong>

                <p>
                    JPG, PNG, atau WEBP · Maksimal 5 MB
                </p>

                <input
                    type="file"
                    name="image"
                    id="image"
                    class="file-input"
                    accept=".jpg,.jpeg,.png,.webp"
                >

            </div>

        </div>


        <!-- ACTION -->

        <div class="form-actions">

            <a
                href="produk.php"
                class="btn-cancel"
            >
                Batal
            </a>

            <button
                type="submit"
                class="btn-save"
            >
                + Simpan Produk
            </button>

        </div>

    </form>

</main>

</body>

</html>
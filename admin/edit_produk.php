<?php

require_once "auth_admin.php";
require_once "../db.php";

$error = "";
$success = "";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: produk.php");
    exit;
}


/* ==================================================
   AMBIL DATA PRODUK
================================================== */

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        category,
        price,
        condition_text,
        description,
        image,
        stock
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();


if (!$product) {
    header("Location: produk.php");
    exit;
}


/* ==================================================
   NILAI AWAL FORM
================================================== */

$name = $product["name"];
$category = $product["category"];
$price = $product["price"];
$condition_text = $product["condition_text"];
$description = $product["description"];
$stock = $product["stock"];
$currentImage = $product["image"];


/* ==================================================
   PROSES UPDATE
================================================== */

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


    /* ==================================================
       VALIDASI
    ================================================== */

    if (
        $name === "" ||
        $category === "" ||
        $price === "" ||
        $condition_text === "" ||
        $stock === ""
    ) {

        $error =
            "Nama, kategori, harga, kondisi, dan stok wajib diisi.";

    } elseif (
        !in_array(
            $category,
            $allowedCategories,
            true
        )
    ) {

        $error =
            "Kategori produk tidak valid.";

    } elseif (
        !is_numeric($price) ||
        $price < 0
    ) {

        $error =
            "Harga produk tidak valid.";

    } elseif (
        !ctype_digit($stock) ||
        (int) $stock < 0
    ) {

        $error =
            "Stok produk tidak valid.";

    } else {

        $newImage = $currentImage;

        $uploadedNewImage = false;


        /* ==================================================
           UPLOAD GAMBAR BARU
        ================================================== */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES["image"]["error"] !==
                UPLOAD_ERR_OK
            ) {

                $error =
                    "Gagal mengupload gambar.";

            } elseif (
                $_FILES["image"]["size"] >
                5 * 1024 * 1024
            ) {

                $error =
                    "Ukuran gambar maksimal 5 MB.";

            } else {

                $tmpName =
                    $_FILES["image"]["tmp_name"];

                $finfo =
                    new finfo(FILEINFO_MIME_TYPE);

                $mime =
                    $finfo->file($tmpName);

                $allowedMime = [
                    "image/jpeg" => "jpg",
                    "image/png"  => "png",
                    "image/webp" => "webp"
                ];


                if (
                    !isset($allowedMime[$mime])
                ) {

                    $error =
                        "Format gambar harus JPG, PNG, atau WEBP.";

                } else {

                    $uploadDir =
                        "../images/";


                    if (!is_dir($uploadDir)) {

                        mkdir(
                            $uploadDir,
                            0777,
                            true
                        );
                    }


                    try {

                        $randomName =
                            bin2hex(
                                random_bytes(8)
                            );

                    } catch (Exception $e) {

                        $randomName =
                            uniqid();
                    }


                    $newImage =
                        time() .
                        "_" .
                        $randomName .
                        "." .
                        $allowedMime[$mime];


                    $destination =
                        $uploadDir .
                        $newImage;


                    if (
                        !move_uploaded_file(
                            $tmpName,
                            $destination
                        )
                    ) {

                        $error =
                            "Gambar baru gagal disimpan.";

                    } else {

                        $uploadedNewImage = true;
                    }
                }
            }
        }


        /* ==================================================
           UPDATE DATABASE
        ================================================== */

        if ($error === "") {

            $priceValue =
                (float) $price;

            $stockValue =
                (int) $stock;


            $stmt = $conn->prepare("
                UPDATE products
                SET
                    name = ?,
                    category = ?,
                    price = ?,
                    condition_text = ?,
                    description = ?,
                    image = ?,
                    stock = ?
                WHERE id = ?
            ");


            if (!$stmt) {

                $error =
                    "Query database gagal.";


                if (
                    $uploadedNewImage &&
                    file_exists(
                        "../images/" . $newImage
                    )
                ) {

                    unlink(
                        "../images/" . $newImage
                    );
                }

            } else {

                $stmt->bind_param(
                    "ssdsssii",
                    $name,
                    $category,
                    $priceValue,
                    $condition_text,
                    $description,
                    $newImage,
                    $stockValue,
                    $id
                );


                if ($stmt->execute()) {


                    /* ==================================================
                       HAPUS GAMBAR LAMA
                    ================================================== */

                    if (
                        $uploadedNewImage &&
                        !empty($currentImage) &&
                        $currentImage !== $newImage &&
                        file_exists(
                            "../images/" .
                            $currentImage
                        )
                    ) {

                        unlink(
                            "../images/" .
                            $currentImage
                        );
                    }


                    $currentImage =
                        $newImage;


                    $success =
                        "Produk berhasil diperbarui.";

                } else {

                    $error =
                        "Produk gagal diperbarui.";


                    if (
                        $uploadedNewImage &&
                        file_exists(
                            "../images/" .
                            $newImage
                        )
                    ) {

                        unlink(
                            "../images/" .
                            $newImage
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Edit Produk - SecondSpace Admin
    </title>


    <style>

/* ==================================================
   GLOBAL
================================================== */

* {
    box-sizing: border-box;

    margin: 0;

    padding: 0;
}

body {
    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f5f6f8;

    color: #111827;
}

a {
    text-decoration: none;
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
        logoSlide 0.7s ease-out;
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
   NAV MENU
================================================== */

.nav-menu {
    display: flex;

    align-items: center;

    gap: 24px;

    margin-left: auto;
}


/* ==================================================
   NAV MENU LINK
================================================== */

.nav-menu a {
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
   MENU ANIMATION
================================================== */

.nav-menu a:nth-child(1) {
    animation-delay: 0.25s;
}

.nav-menu a:nth-child(2) {
    animation-delay: 0.35s;
}

.nav-menu a:nth-child(3) {
    animation-delay: 0.45s;
}

.nav-menu a:nth-child(4) {
    animation-delay: 0.55s;
}

.nav-menu a:nth-child(5) {
    animation-delay: 0.65s;
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

    transform:
        scaleX(0);

    transform-origin: left;

    transition:
        transform 0.35s ease;
}


/* ==================================================
   ACTIVE PRODUK
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

    padding:
        9px 20px;

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
    max-width: 1050px;

    margin:
        40px auto;

    padding:
        0 20px;

    animation:
        contentFade 0.7s ease-out;
}


/* ==================================================
   PAGE HEADER
================================================== */

.page-header {
    display: flex;

    justify-content:
        space-between;

    align-items: flex-end;

    gap: 20px;

    margin-bottom: 30px;
}

.eyebrow {
    color: #737373;

    font-size: 12px;

    font-weight: 700;

    letter-spacing: 1.6px;

    text-transform: uppercase;

    margin-bottom: 9px;
}

.page-header h1 {
    font-size: 30px;

    letter-spacing: -0.5px;

    margin-bottom: 8px;
}

.page-header p {
    color: #777777;

    font-size: 14px;
}


/* ==================================================
   BACK BUTTON
================================================== */

.back-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding:
        11px 17px;

    background: #ffffff;

    color: #374151;

    border:
        1px solid #e5e7eb;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

    transition:
        background 0.25s ease,
        color 0.25s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.back-btn:hover {
    background: #111827;

    color: #ffffff;

    transform:
        translateY(-2px);

    box-shadow:
        0 6px 15px
        rgba(0, 0, 0, 0.12);
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
        1px solid #eeeeee;

    border-radius: 18px;

    padding: 32px;

    box-shadow:
        0 6px 20px
        rgba(0, 0, 0, 0.06);
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

    margin-bottom: 8px;
}

.section-subtitle {
    color: #777777;

    font-size: 13px;

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
   LABEL
================================================== */

label {
    font-size: 13px;

    font-weight: 700;

    margin-bottom: 8px;

    color: #374151;
}


/* ==================================================
   INPUT
================================================== */

input,
select,
textarea {
    width: 100%;

    border:
        1px solid #d1d5db;

    border-radius: 10px;

    padding:
        13px 14px;

    font-size: 14px;

    font-family: inherit;

    outline: none;

    background: #ffffff;

    color: #111827;

    transition:
        border-color 0.25s ease,
        box-shadow 0.25s ease;
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
   CURRENT IMAGE
================================================== */

.current-image-box {
    display: flex;

    align-items: center;

    gap: 18px;

    padding: 16px;

    border:
        1px solid #e5e7eb;

    border-radius: 15px;

    background: #fafafa;

    margin-bottom: 18px;
}

.current-image {
    width: 100px;

    height: 100px;

    object-fit: cover;

    border-radius: 12px;

    border:
        1px solid #e5e7eb;

    background: #f3f4f6;

    flex-shrink: 0;
}

.no-image {
    width: 100px;

    height: 100px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #e5e7eb;

    color: #6b7280;

    font-size: 12px;

    text-align: center;

    flex-shrink: 0;
}

.current-image-info strong {
    display: block;

    margin-bottom: 6px;

    font-size: 14px;
}

.current-image-info span {
    color: #6b7280;

    font-size: 12px;

    word-break: break-all;
}


/* ==================================================
   UPLOAD
================================================== */

.upload-box {
    border:
        2px dashed #d1d5db;

    border-radius: 15px;

    padding: 28px;

    text-align: center;

    background: #fafafa;

    transition:
        border-color 0.25s ease,
        background 0.25s ease;
}

.upload-box:hover {
    border-color: #111827;

    background: #f9fafb;
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

    margin: 0 auto;

    display: block;
}


/* ==================================================
   FORM ACTIONS
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
    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding:
        13px 20px;

    border-radius: 10px;

    border:
        1px solid #d1d5db;

    color: #374151;

    background: #ffffff;

    font-size: 14px;

    font-weight: 700;

    transition:
        background 0.25s ease,
        color 0.25s ease,
        transform 0.25s ease;
}

.btn-cancel:hover {
    background: #f3f4f6;

    color: #111827;

    transform:
        translateY(-2px);
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
        background 0.25s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.btn-save:hover {
    background: #374151;

    transform:
        translateY(-2px);

    box-shadow:
        0 7px 16px
        rgba(0, 0, 0, 0.15);
}


/* ==================================================
   ANIMATION
================================================== */

@keyframes navbarDown {

    from {
        opacity: 0;

        transform:
            translateY(-15px);
    }

    to {
        opacity: 1;

        transform:
            translateY(0);
    }

}

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
   RESPONSIVE
================================================== */

@media (max-width: 900px) {

    .navbar {
        padding:
            16px 20px;
    }

    .nav-menu {
        gap: 15px;
    }

    .nav-menu a {
        font-size: 13px;
    }

}


@media (max-width: 768px) {

    .navbar {
        flex-direction: column;

        gap: 14px;

        align-items: center;
    }

    .navbar .logo {
        margin-right: 0;
    }

    .nav-menu {
        width: 100%;

        justify-content: center;

        flex-wrap: wrap;

        gap: 13px;

        margin-left: 0;
    }

    .nav-menu a {
        font-size: 12px;
    }

    .container {
        margin-top: 25px;

        padding:
            0 15px;
    }

    .page-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .page-header h1 {
        font-size: 25px;
    }

    .page-header p {
        font-size: 14px;
    }

    .back-btn {
        width: 100%;
    }

    .form-card {
        padding: 22px;

        border-radius: 14px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: auto;
    }

    .current-image-box {
        align-items: flex-start;

        flex-direction: column;
    }

    .form-actions {
        flex-direction: column-reverse;
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
        </span><span class="logo-space">Space</span>

    </a>


    <div class="nav-menu">

        <a href="index.php">
            Dashboard
        </a>

        <a
            href="produk.php"
            class="active"
        >
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

<main class="container">


    <!-- ==================================================
         HEADER
    ================================================== -->

    <div class="page-header">

        <div>

            <div class="eyebrow">
                SecondSpace PreLoved
            </div>

            <h1>
                Edit Produk
            </h1>

            <p>
                Perbarui informasi produk yang sudah ada.
            </p>

        </div>


        <a
            href="produk.php"
            class="back-btn"
        >
            ← Kembali ke Produk
        </a>

    </div>


    <!-- ==================================================
         ALERT
    ================================================== -->

    <?php if ($success !== ""): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="alert alert-error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- ==================================================
         FORM
    ================================================== -->

    <form
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >


        <!-- ==================================================
             INFORMASI PRODUK
        ================================================== -->

        <div class="section">

            <div class="section-title">
                Informasi Produk
            </div>

            <div class="section-subtitle">
                Ubah informasi produk sesuai kondisi terbaru.
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

                        <option
                            value="Baju"
                            <?= $category === "Baju"
                                ? "selected"
                                : "" ?>
                        >
                            Baju
                        </option>

                        <option
                            value="Kemeja"
                            <?= $category === "Kemeja"
                                ? "selected"
                                : "" ?>
                        >
                            Kemeja
                        </option>

                        <option
                            value="Celana"
                            <?= $category === "Celana"
                                ? "selected"
                                : "" ?>
                        >
                            Celana
                        </option>

                        <option
                            value="Sepatu"
                            <?= $category === "Sepatu"
                                ? "selected"
                                : "" ?>
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
                        value="<?= htmlspecialchars(
                            $condition_text
                        ) ?>"
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
                    ><?= htmlspecialchars($description) ?></textarea>

                </div>

            </div>

        </div>


        <!-- ==================================================
             FOTO PRODUK
        ================================================== -->

        <div class="section">

            <div class="section-title">
                Foto Produk
            </div>

            <div class="section-subtitle">
                Foto lama tetap digunakan jika kamu tidak memilih foto baru.
            </div>


            <!-- FOTO SAAT INI -->

            <div class="current-image-box">

                <?php

                $imagePath =
                    "../images/" .
                    $currentImage;

                if (
                    !empty($currentImage) &&
                    file_exists($imagePath)
                ):

                ?>

                    <img
                        src="<?= htmlspecialchars(
                            $imagePath
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $name
                        ) ?>"
                        class="current-image"
                    >

                <?php else: ?>

                    <div class="no-image">

                        Tidak ada gambar

                    </div>

                <?php endif; ?>


                <div class="current-image-info">

                    <strong>
                        Foto Saat Ini
                    </strong>

                    <span>

                        <?= !empty($currentImage)
                            ? htmlspecialchars(
                                $currentImage
                            )
                            : "Belum ada gambar"
                        ?>

                    </span>

                </div>

            </div>


            <!-- UPLOAD FOTO -->

            <div class="upload-box">

                <div class="upload-icon">
                    ↑
                </div>

                <strong>
                    Ganti Foto Produk
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


        <!-- ==================================================
             ACTION
        ================================================== -->

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
                Simpan Perubahan
            </button>

        </div>


    </form>


</main>


</body>

</html>
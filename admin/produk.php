<?php

require_once "auth_admin.php";
require_once "../db.php";


/* ==================================================
   AMBIL DATA PRODUK
================================================== */

$result = $conn->query("
    SELECT
        id,
        name,
        category,
        price,
        condition_text,
        image,
        stock,
        created_at
    FROM products
    ORDER BY id DESC
");


/* ==================================================
   FORMAT RUPIAH
================================================== */

function formatRupiah($number)
{
    return "Rp " . number_format(
        (float) $number,
        0,
        ",",
        "."
    );
}


/* ==================================================
   CLASS STOK
================================================== */

function stockClass($stock)
{
    if ((int) $stock <= 0) {
        return "empty";
    }

    if ((int) $stock <= 2) {
        return "low";
    }

    return "available";
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
        Kelola Produk - SecondSpace Admin
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
   MENU
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
   GARIS BAWAH
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
    max-width: 1250px;

    margin:
        40px auto;

    padding:
        0 20px;

    animation:
        contentFade 0.7s ease-out;
}


/* ==================================================
   HEADER
================================================== */

.page-header {
    display: flex;

    align-items: flex-end;

    justify-content:
        space-between;

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

    margin-bottom: 8px;

    letter-spacing: -0.5px;
}

.page-header p {
    color: #777;

    font-size: 14px;
}


/* ==================================================
   ADD BUTTON
================================================== */

.add-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding:
        13px 18px;

    background: #111827;

    color: #ffffff;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

    transition:
        background 0.25s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.add-btn:hover {
    background: #374151;

    color: #ffffff;

    transform:
        translateY(-2px);

    box-shadow:
        0 8px 18px
        rgba(0, 0, 0, 0.15);
}


/* ==================================================
   PRODUCT CARD
================================================== */

.products-card {
    background: #ffffff;

    border:
        1px solid #eeeeee;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 6px 20px
        rgba(0, 0, 0, 0.06);
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

    min-width: 900px;
}

th {
    background: #111111;

    color: #ffffff;

    padding:
        16px 15px;

    text-align: left;

    font-size: 14px;

    font-weight: 600;

    white-space: nowrap;
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
    padding:
        16px 15px;

    border-bottom:
        1px solid #eeeeee;

    vertical-align: middle;

    font-size: 14px;
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
   PRODUCT
================================================== */

.product-info {
    display: flex;

    align-items: center;

    gap: 14px;

    min-width: 260px;
}

.product-image {
    width: 64px;

    height: 64px;

    border-radius: 13px;

    object-fit: cover;

    background: #f2f2f2;

    border:
        1px solid #e7e7e7;

    flex-shrink: 0;
}

.product-image.empty-image {
    display: flex;

    align-items: center;

    justify-content: center;

    color: #aaa;

    font-size: 11px;
}

.product-name {
    font-weight: 700;

    font-size: 14px;

    margin-bottom: 5px;
}

.product-id {
    color: #999;

    font-size: 11px;
}


/* ==================================================
   BADGES
================================================== */

.badge {
    display: inline-flex;

    align-items: center;

    padding:
        7px 11px;

    border-radius: 8px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;
}

.category {
    background: #f1f1f1;

    color: #444;
}

.condition {
    background: #eef8f1;

    color: #247143;
}

.stock {
    background: #eef8f1;

    color: #247143;
}

.stock.low {
    background: #fff7df;

    color: #916b00;
}

.stock.empty {
    background: #ffeaea;

    color: #b52d2d;
}


/* ==================================================
   PRICE
================================================== */

.price {
    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;
}


/* ==================================================
   ACTION
================================================== */

.actions {
    display: flex;

    align-items: center;

    gap: 8px;
}

.edit-btn,
.delete-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding:
        9px 14px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 700;

    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.edit-btn {
    background: #111111;

    color: #ffffff;
}

.edit-btn:hover {
    background: #333333;

    color: #ffffff;

    transform:
        translateY(-2px);

    box-shadow:
        0 6px 15px
        rgba(0, 0, 0, 0.12);
}

.delete-btn {
    background: #fff0f0;

    color: #b52d2d;
}

.delete-btn:hover {
    background: #b52d2d;

    color: #ffffff;

    transform:
        translateY(-2px);
}


/* ==================================================
   EMPTY STATE
================================================== */

.empty-state {
    padding:
        80px 20px;

    text-align: center;
}

.empty-icon {
    width: 64px;

    height: 64px;

    margin:
        0 auto 18px;

    border-radius: 18px;

    background: #f1f1f1;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 28px;
}

.empty-state h3 {
    font-size: 18px;

    margin-bottom: 7px;
}

.empty-state p {
    color: #888;

    font-size: 13px;

    margin-bottom: 20px;
}


/* ==================================================
   ANIMASI NAVBAR
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

    .add-btn {
        width: 100%;
    }

    .products-card {
        border-radius: 14px;
    }

    .table-wrapper {
        border-radius: 14px;
    }

    th,
    td {
        padding:
            12px;

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
                Kelola Produk
            </h1>

            <p>
                Kelola semua produk preloved yang tersedia di toko.
            </p>

        </div>


        <a
            href="tambah_produk.php"
            class="add-btn"
        >

            <span>＋</span>

            Tambah Produk

        </a>

    </div>


    <!-- ==================================================
         PRODUCT LIST
    ================================================== -->

    <div class="products-card">

        <?php if (
            $result &&
            $result->num_rows > 0
        ): ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Produk
                            </th>

                            <th>
                                Kategori
                            </th>

                            <th>
                                Harga
                            </th>

                            <th>
                                Kondisi
                            </th>

                            <th>
                                Stok
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while (
                            $product =
                            $result->fetch_assoc()
                        ): ?>

                            <tr>


                                <!-- PRODUK -->

                                <td>

                                    <div class="product-info">

                                        <?php

                                        $imageFile =
                                            trim(
                                                $product["image"] ?? ""
                                            );

                                        $imagePath =
                                            "../images/" .
                                            $imageFile;

                                        ?>

                                        <?php if (
                                            $imageFile !== "" &&
                                            file_exists($imagePath)
                                        ): ?>

                                            <img
                                                src="<?= htmlspecialchars(
                                                    $imagePath
                                                ); ?>"
                                                alt="<?= htmlspecialchars(
                                                    $product["name"]
                                                ); ?>"
                                                class="product-image"
                                            >

                                        <?php else: ?>

                                            <div
                                                class="
                                                    product-image
                                                    empty-image
                                                "
                                            >
                                                No Image
                                            </div>

                                        <?php endif; ?>


                                        <div>

                                            <div class="product-name">

                                                <?= htmlspecialchars(
                                                    $product["name"]
                                                ); ?>

                                            </div>

                                            <div class="product-id">

                                                ID Produk
                                                #<?= (int) $product["id"]; ?>

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- KATEGORI -->

                                <td>

                                    <span class="badge category">

                                        <?= htmlspecialchars(
                                            $product["category"]
                                        ); ?>

                                    </span>

                                </td>


                                <!-- HARGA -->

                                <td>

                                    <div class="price">

                                        <?= formatRupiah(
                                            $product["price"]
                                        ); ?>

                                    </div>

                                </td>


                                <!-- KONDISI -->

                                <td>

                                    <span class="badge condition">

                                        <?= htmlspecialchars(
                                            $product["condition_text"]
                                        ); ?>

                                    </span>

                                </td>


                                <!-- STOK -->

                                <td>

                                    <span
                                        class="
                                            badge
                                            stock
                                            <?= stockClass(
                                                $product["stock"]
                                            ); ?>
                                        "
                                    >

                                        <?php if (
                                            (int) $product["stock"] <= 0
                                        ): ?>

                                            Habis

                                        <?php else: ?>

                                            <?= (int) $product["stock"]; ?>
                                            pcs

                                        <?php endif; ?>

                                    </span>

                                </td>


                                <!-- AKSI -->

                                <td>

                                    <div class="actions">

                                        <a
                                            href="edit_produk.php?id=<?= (int) $product["id"]; ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="hapus_produk.php?id=<?= (int) $product["id"]; ?>"
                                            class="delete-btn"
                                            onclick="
                                                return confirm(
                                                    'Yakin ingin menghapus produk ini?'
                                                );
                                            "
                                        >
                                            Hapus
                                        </a>

                                    </div>

                                </td>


                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <!-- ==================================================
                 EMPTY STATE
            ================================================== -->

            <div class="empty-state">

                <div class="empty-icon">
                    🛍
                </div>

                <h3>
                    Belum Ada Produk
                </h3>

                <p>
                    Tambahkan produk pertama untuk mulai mengisi toko.
                </p>

                <a
                    href="tambah_produk.php"
                    class="add-btn"
                >
                    ＋ Tambah Produk
                </a>

            </div>


        <?php endif; ?>

    </div>


</main>


</body>

</html>
<?php

session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

/*
|--------------------------------------------------------------------------
| TAMBAH PRODUK
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "add") {

        $product_id = intval($_POST["product_id"] ?? 0);
        $quantity = intval($_POST["quantity"] ?? 1);

        if ($product_id <= 0) {
            header("Location: produk.php");
            exit;
        }

        if ($quantity < 1) {
            $quantity = 1;
        }

        $stmt = $conn->prepare("
            SELECT id, name, price, image, stock
            FROM products
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if ($product && (int)$product["stock"] > 0) {

            $stock = (int)$product["stock"];

            if ($quantity > $stock) {
                $quantity = $stock;
            }

            if (isset($_SESSION["cart"][$product_id])) {

                $newQuantity =
                    $_SESSION["cart"][$product_id]["quantity"] + $quantity;

                if ($newQuantity > $stock) {
                    $newQuantity = $stock;
                }

                $_SESSION["cart"][$product_id]["quantity"] = $newQuantity;

            } else {

                $_SESSION["cart"][$product_id] = [
                    "id"       => (int)$product["id"],
                    "name"     => $product["name"],
                    "price"    => (float)$product["price"],
                    "image"    => $product["image"],
                    "quantity" => $quantity,
                    "stock"    => $stock
                ];
            }
        }

        header("Location: keranjang.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE JUMLAH
    |--------------------------------------------------------------------------
    */

    if ($action === "update") {

        $product_id = intval($_POST["product_id"] ?? 0);
        $quantity = intval($_POST["quantity"] ?? 1);

        if (isset($_SESSION["cart"][$product_id])) {

            $stmt = $conn->prepare("
                SELECT stock
                FROM products
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if ($product) {

                $stock = (int)$product["stock"];

                if ($quantity < 1) {
                    unset($_SESSION["cart"][$product_id]);
                } else {

                    if ($quantity > $stock) {
                        $quantity = $stock;
                    }

                    $_SESSION["cart"][$product_id]["quantity"] = $quantity;
                    $_SESSION["cart"][$product_id]["stock"] = $stock;
                }

            } else {

                unset($_SESSION["cart"][$product_id]);
            }
        }

        header("Location: keranjang.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS PRODUK
    |--------------------------------------------------------------------------
    */

    if ($action === "remove") {

        $product_id = intval($_POST["product_id"] ?? 0);

        if (isset($_SESSION["cart"][$product_id])) {
            unset($_SESSION["cart"][$product_id]);
        }

        header("Location: keranjang.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | KOSONGKAN KERANJANG
    |--------------------------------------------------------------------------
    */

    if ($action === "clear") {

        $_SESSION["cart"] = [];

        header("Location: keranjang.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PRODUK TERBARU DARI DATABASE
|--------------------------------------------------------------------------
*/

$cart = [];

foreach ($_SESSION["cart"] as $product_id => $item) {

    $stmt = $conn->prepare("
        SELECT id, name, price, image, stock
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        unset($_SESSION["cart"][$product_id]);
        continue;
    }

    $stock = (int)$product["stock"];

    if ($stock <= 0) {
        unset($_SESSION["cart"][$product_id]);
        continue;
    }

    $quantity = (int)$item["quantity"];

    if ($quantity > $stock) {
        $quantity = $stock;
        $_SESSION["cart"][$product_id]["quantity"] = $quantity;
    }

    $cart[] = [
        "id"       => (int)$product["id"],
        "name"     => $product["name"],
        "price"    => (float)$product["price"],
        "image"    => $product["image"],
        "quantity" => $quantity,
        "stock"    => $stock
    ];
}


/*
|--------------------------------------------------------------------------
| HITUNG TOTAL
|--------------------------------------------------------------------------
*/

$subtotal = 0;
$totalItems = 0;

foreach ($cart as $item) {

    $subtotal += $item["price"] * $item["quantity"];
    $totalItems += $item["quantity"];
}

$shipping = $subtotal > 0 ? 15000 : 0;
$total = $subtotal + $shipping;

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
        Keranjang - SecondSpace PreLoved
    </title>

    <style>

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

            background: #f6f7f9;

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
                navbarDown 0.5s ease-out;
        }

        /* ==================================================
           ANIMASI NAVBAR
        ================================================== */

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
            transform: translateY(-2px);

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

            transform: scaleX(0);

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
            transform: scaleX(0);
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
            width: 86%;

            max-width: 1200px;

            margin:
                40px auto 70px;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-header h1 {
            font-size: 30px;

            margin-bottom: 8px;
        }

        .page-header p {
            color: #6b7280;

            font-size: 14px;
        }

        /* ==================================================
           CART LAYOUT
        ================================================== */

        .cart-layout {
            display: grid;

            grid-template-columns:
                1fr 360px;

            gap: 25px;

            align-items: start;
        }

        /* ==================================================
           CART LIST
        ================================================== */

        .cart-box {
            background: #ffffff;

            border-radius: 20px;

            padding: 22px;

            box-shadow:
                0 8px 25px
                rgba(0, 0, 0, 0.05);
        }

        .cart-box-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;
        }

        .cart-box-header h2 {
            font-size: 18px;
        }

        .clear-form button {
            border: none;

            background: transparent;

            color: #dc2626;

            font-size: 13px;

            font-weight: 700;

            cursor: pointer;
        }

        .clear-form button:hover {
            text-decoration: underline;
        }

        /* ==================================================
           ITEM
        ================================================== */

        .cart-item {
            display: grid;

            grid-template-columns:
                110px 1fr auto;

            gap: 18px;

            align-items: center;

            padding: 18px 0;

            border-bottom:
                1px solid #e5e7eb;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 110px;

            height: 110px;

            border-radius: 15px;

            overflow: hidden;

            background: #f3f4f6;

            display: flex;

            align-items: center;

            justify-content: center;
        }

        .product-image img {
            width: 100%;

            height: 100%;

            object-fit: cover;
        }

        .image-placeholder {
            font-size: 35px;
        }

        .product-info h3 {
            font-size: 17px;

            margin-bottom: 8px;
        }

        .product-price {
            font-size: 14px;

            font-weight: 700;

            color: #374151;

            margin-bottom: 13px;
        }

        .stock-info {
            font-size: 12px;

            color: #6b7280;
        }

        /* ==================================================
           QUANTITY
        ================================================== */

        .quantity-wrapper {
            display: flex;

            align-items: center;

            gap: 8px;
        }

        .quantity-form {
            display: flex;

            align-items: center;

            gap: 7px;
        }

        .quantity-btn {
            width: 32px;

            height: 32px;

            border:
                1px solid #d1d5db;

            background: #ffffff;

            border-radius: 8px;

            cursor: pointer;

            font-size: 17px;

            font-weight: 700;

            transition: 0.2s;
        }

        .quantity-btn:hover {
            background: #111827;

            color: #ffffff;

            border-color: #111827;
        }

        .quantity-number {
            min-width: 30px;

            text-align: center;

            font-weight: 700;

            font-size: 14px;
        }

        .update-hidden {
            display: none;
        }

        /* ==================================================
           ITEM TOTAL
        ================================================== */

        .item-right {
            text-align: right;

            min-width: 130px;
        }

        .item-total {
            font-size: 16px;

            font-weight: 800;

            margin-bottom: 12px;
        }

        .remove-btn {
            border: none;

            background: transparent;

            color: #dc2626;

            cursor: pointer;

            font-size: 12px;

            font-weight: 700;
        }

        .remove-btn:hover {
            text-decoration: underline;
        }

        /* ==================================================
           SUMMARY
        ================================================== */

        .summary {
            background: #ffffff;

            border-radius: 20px;

            padding: 24px;

            box-shadow:
                0 8px 25px
                rgba(0, 0, 0, 0.05);

            position: sticky;

            top: 95px;
        }

        .summary h2 {
            font-size: 20px;

            margin-bottom: 22px;
        }

        .summary-row {
            display: flex;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 15px;

            font-size: 14px;
        }

        .summary-label {
            color: #6b7280;
        }

        .summary-value {
            font-weight: 700;

            text-align: right;
        }

        .summary-divider {
            height: 1px;

            background: #e5e7eb;

            margin: 20px 0;
        }

        .total-row {
            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;
        }

        .total-label {
            font-size: 15px;

            font-weight: 700;
        }

        .total-value {
            font-size: 23px;

            font-weight: 800;
        }

        .checkout-btn {
            display: block;

            width: 100%;

            margin-top: 22px;

            padding: 15px;

            border-radius: 13px;

            background: #111827;

            color: #ffffff;

            text-align: center;

            text-decoration: none;

            font-size: 14px;

            font-weight: 700;

            transition: 0.25s;
        }

        .checkout-btn:hover {
            background: #000000;

            transform:
                translateY(-2px);

            box-shadow:
                0 8px 20px
                rgba(0, 0, 0, 0.15);
        }

        .continue-btn {
            display: block;

            width: 100%;

            margin-top: 10px;

            padding: 13px;

            border-radius: 13px;

            border:
                1px solid #d1d5db;

            background: #ffffff;

            color: #111827;

            text-align: center;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            transition: 0.2s;
        }

        .continue-btn:hover {
            background: #f9fafb;
        }

        /* ==================================================
           EMPTY CART
        ================================================== */

        .empty-cart {
            background: #ffffff;

            border-radius: 22px;

            padding: 65px 25px;

            text-align: center;

            box-shadow:
                0 8px 25px
                rgba(0, 0, 0, 0.05);
        }

        .empty-icon {
            font-size: 65px;

            margin-bottom: 20px;
        }

        .empty-cart h2 {
            font-size: 23px;

            margin-bottom: 9px;
        }

        .empty-cart p {
            color: #6b7280;

            font-size: 14px;

            margin-bottom: 25px;
        }

        .shop-btn {
            display: inline-block;

            padding: 13px 22px;

            background: #111827;

            color: #ffffff;

            border-radius: 11px;

            text-decoration: none;

            font-size: 14px;

            font-weight: 700;

            transition: 0.2s;
        }

        .shop-btn:hover {
            background: #000000;

            transform:
                translateY(-2px);
        }

        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 950px) {

            .cart-layout {
                grid-template-columns: 1fr;
            }

            .summary {
                position: static;
            }

        }

        @media (max-width: 650px) {

            .navbar {
                padding:
                    14px 5%;
            }

            .logo {
                font-size: 18px;
            }

            .nav-menu {
                gap: 12px;
            }

            .nav-menu a {
                font-size: 12px;
            }

            .container {
                width: 92%;

                margin-top: 25px;
            }

            .page-header h1 {
                font-size: 26px;
            }

            .cart-box {
                padding: 16px;

                border-radius: 17px;
            }

            .cart-item {
                grid-template-columns:
                    85px 1fr;

                gap: 13px;
            }

            .product-image {
                width: 85px;

                height: 85px;
            }

            .product-info h3 {
                font-size: 14px;
            }

            .product-price {
                font-size: 13px;
            }

            .item-right {
                grid-column: 2;

                text-align: left;

                min-width: auto;

                display: flex;

                justify-content: space-between;

                align-items: center;

                padding-top: 5px;
            }

            .item-total {
                margin-bottom: 0;
            }

            .summary {
                padding: 20px;

                border-radius: 17px;
            }

        }

        @media (max-width: 400px) {

            .nav-menu a:nth-child(2) {
                display: none;
            }

            .cart-item {
                grid-template-columns:
                    75px 1fr;
            }

            .product-image {
                width: 75px;

                height: 75px;
            }

            .quantity-btn {
                width: 29px;

                height: 29px;
            }

            .total-value {
                font-size: 20px;
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

        <a
            href="keranjang.php"
            class="active"
        >
            Keranjang
        </a>

        <a href="pesanan.php">
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
     CONTENT
================================================== -->

<div class="container">

    <div class="page-header">

        <h1>
            Keranjang Belanja
        </h1>

        <p>
            Periksa kembali produk yang ingin kamu beli.
        </p>

    </div>


    <?php if (empty($cart)): ?>

        <!-- EMPTY CART -->

        <div class="empty-cart">

            <div class="empty-icon">
                🛒
            </div>

            <h2>
                Keranjang masih kosong
            </h2>

            <p>
                Yuk cari produk preloved favorit kamu.
            </p>

            <a
                href="produk.php"
                class="shop-btn"
            >
                Belanja Sekarang
            </a>

        </div>

    <?php else: ?>

        <div class="cart-layout">

            <!-- ==================================================
                 CART ITEMS
            ================================================== -->

            <div class="cart-box">

                <div class="cart-box-header">

                    <h2>
                        <?= $totalItems ?> Item
                    </h2>

                    <form
                        method="POST"
                        class="clear-form"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="clear"
                        >

                        <button type="submit">
                            Kosongkan Keranjang
                        </button>

                    </form>

                </div>


                <?php foreach ($cart as $item): ?>

                    <div class="cart-item">

                        <!-- IMAGE -->

                        <div class="product-image">

                            <?php if (!empty($item["image"])): ?>

                                <img
                                    src="images/<?= htmlspecialchars($item["image"]) ?>"
                                    alt="<?= htmlspecialchars($item["name"]) ?>"
                                >

                            <?php else: ?>

                                <div class="image-placeholder">
                                    🛍️
                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- PRODUCT -->

                        <div class="product-info">

                            <h3>
                                <?= htmlspecialchars($item["name"]) ?>
                            </h3>

                            <div class="product-price">

                                Rp <?= number_format(
                                    $item["price"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </div>

                            <div class="stock-info">

                                Stok tersedia:
                                <?= $item["stock"] ?>

                            </div>

                            <div class="quantity-wrapper">

                                <form
                                    method="POST"
                                    class="quantity-form"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="update"
                                    >

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= $item["id"] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="quantity"
                                        value="<?= max(1, $item["quantity"] - 1) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="quantity-btn"
                                        <?= $item["quantity"] <= 1 ? "disabled" : "" ?>
                                    >
                                        −
                                    </button>

                                </form>


                                <span class="quantity-number">
                                    <?= $item["quantity"] ?>
                                </span>


                                <form
                                    method="POST"
                                    class="quantity-form"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="update"
                                    >

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= $item["id"] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="quantity"
                                        value="<?= min($item["stock"], $item["quantity"] + 1) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="quantity-btn"
                                        <?= $item["quantity"] >= $item["stock"] ? "disabled" : "" ?>
                                    >
                                        +
                                    </button>

                                </form>

                            </div>

                        </div>


                        <!-- TOTAL -->

                        <div class="item-right">

                            <div class="item-total">

                                Rp <?= number_format(
                                    $item["price"] * $item["quantity"],
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </div>


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="remove"
                                >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= $item["id"] ?>"
                                >

                                <button
                                    type="submit"
                                    class="remove-btn"
                                >
                                    Hapus
                                </button>

                            </form>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


            <!-- ==================================================
                 SUMMARY
            ================================================== -->

            <div class="summary">

                <h2>
                    Ringkasan Belanja
                </h2>


                <div class="summary-row">

                    <span class="summary-label">
                        Total item
                    </span>

                    <span class="summary-value">
                        <?= $totalItems ?>
                    </span>

                </div>


                <div class="summary-row">

                    <span class="summary-label">
                        Subtotal
                    </span>

                    <span class="summary-value">

                        Rp <?= number_format(
                            $subtotal,
                            0,
                            ",",
                            "."
                        ) ?>

                    </span>

                </div>


                <div class="summary-row">

                    <span class="summary-label">
                        Ongkir
                    </span>

                    <span class="summary-value">

                        Rp <?= number_format(
                            $shipping,
                            0,
                            ",",
                            "."
                        ) ?>

                    </span>

                </div>


                <div class="summary-divider"></div>


                <div class="total-row">

                    <span class="total-label">
                        Total
                    </span>

                    <span class="total-value">

                        Rp <?= number_format(
                            $total,
                            0,
                            ",",
                            "."
                        ) ?>

                    </span>

                </div>


                <a
                    href="checkout.php"
                    class="checkout-btn"
                >
                    Lanjut ke Checkout →
                </a>


                <a
                    href="produk.php"
                    class="continue-btn"
                >
                    ← Lanjut Belanja
                </a>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>

</html>
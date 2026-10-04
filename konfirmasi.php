<?php

session_start();

require_once "db.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK KERANJANG
|--------------------------------------------------------------------------
*/
if (
    !isset($_SESSION["cart"])
    || empty($_SESSION["cart"])
) {
    header("Location: keranjang.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK DATA CHECKOUT
|--------------------------------------------------------------------------
*/
if (
    !isset($_SESSION["checkout"])
    || empty($_SESSION["checkout"])
) {
    header("Location: checkout.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK REQUEST
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pembayaran.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDASI METODE PEMBAYARAN
|--------------------------------------------------------------------------
*/
$paymentMethod = trim(
    $_POST["payment_method"] ?? ""
);

$allowedPayment = [
    "Transfer Bank",
    "E-Wallet",
    "COD"
];

if (!in_array($paymentMethod, $allowedPayment, true)) {
    die("Metode pembayaran tidak valid.");
}


/*
|--------------------------------------------------------------------------
| DATA USER
|--------------------------------------------------------------------------
*/
$userId = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| DATA CHECKOUT
|--------------------------------------------------------------------------
*/
$checkout = $_SESSION["checkout"];

$recipientName = trim(
    $checkout["recipient_name"] ?? ""
);

$phone = trim(
    $checkout["phone"] ?? ""
);

$address = trim(
    $checkout["address"] ?? ""
);

$province = trim(
    $checkout["province"] ?? ""
);

$city = trim(
    $checkout["city"] ?? ""
);

$postalCode = trim(
    $checkout["postal_code"] ?? ""
);


/*
|--------------------------------------------------------------------------
| VALIDASI ALAMAT
|--------------------------------------------------------------------------
*/
if (
    $recipientName === ""
    || $phone === ""
    || $address === ""
    || $province === ""
    || $city === ""
    || $postalCode === ""
) {
    die("Data alamat tidak lengkap.");
}


/*
|--------------------------------------------------------------------------
| GENERATE KODE PESANAN
|--------------------------------------------------------------------------
*/
$orderCode =
    "SS-" .
    date("YmdHis") .
    rand(100, 999);


/*
|--------------------------------------------------------------------------
| NILAI AWAL
|--------------------------------------------------------------------------
*/
$subtotal = 0;
$shipping = 15000;


/*
|--------------------------------------------------------------------------
| MULAI TRANSACTION
|--------------------------------------------------------------------------
*/
$conn->begin_transaction();


try {

    /*
    |--------------------------------------------------------------------------
    | 1. SIMPAN ALAMAT
    |--------------------------------------------------------------------------
    */

    $stmtAddress = $conn->prepare("
        INSERT INTO addresses
        (
            user_id,
            recipient_name,
            phone,
            address,
            province,
            city,
            postal_code
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtAddress->bind_param(
        "issssss",
        $userId,
        $recipientName,
        $phone,
        $address,
        $province,
        $city,
        $postalCode
    );

    $stmtAddress->execute();

    $addressId = $conn->insert_id;


    /*
    |--------------------------------------------------------------------------
    | 2. PREPARE ORDER ITEM
    |--------------------------------------------------------------------------
    */

    $stmtItem = $conn->prepare("
        INSERT INTO order_items
        (
            order_id,
            product_id,
            product_name,
            price,
            quantity,
            subtotal
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");


    /*
    |--------------------------------------------------------------------------
    | 3. PREPARE UPDATE STOK
    |--------------------------------------------------------------------------
    */

    $stmtStock = $conn->prepare("
        UPDATE products
        SET stock = stock - ?
        WHERE id = ?
        AND stock >= ?
    ");


    /*
    |--------------------------------------------------------------------------
    | 4. VALIDASI ULANG PRODUK DARI DATABASE
    |--------------------------------------------------------------------------
    */

    $cartItems = [];


    foreach ($_SESSION["cart"] as $item) {

        $productId = (int) ($item["id"] ?? 0);
        $quantity = (int) ($item["quantity"] ?? 0);


        if ($productId <= 0 || $quantity <= 0) {
            throw new Exception(
                "Data produk dalam keranjang tidak valid."
            );
        }


        /*
        | Ambil data terbaru dari database
        */
        $stmtProduct = $conn->prepare("
            SELECT
                id,
                name,
                price,
                stock
            FROM products
            WHERE id = ?
            FOR UPDATE
        ");

        $stmtProduct->bind_param(
            "i",
            $productId
        );

        $stmtProduct->execute();

        $resultProduct =
            $stmtProduct->get_result();

        $product =
            $resultProduct->fetch_assoc();


        /*
        | Produk tidak ditemukan
        */
        if (!$product) {

            throw new Exception(
                "Produk sudah tidak tersedia."
            );
        }


        $stock = (int) $product["stock"];

        $price = (float) $product["price"];

        $productName = $product["name"];


        /*
        | Cek stok terbaru
        */
        if ($stock < $quantity) {

            throw new Exception(
                "Stok produk \""
                . $productName
                . "\" tidak mencukupi. Stok tersedia: "
                . $stock
            );
        }


        /*
        | Hitung berdasarkan harga DATABASE
        */
        $itemSubtotal =
            $price * $quantity;


        $subtotal += $itemSubtotal;


        /*
        | Simpan data terbaru untuk order_items
        */
        $cartItems[] = [
            "id" => $productId,
            "name" => $productName,
            "price" => $price,
            "quantity" => $quantity,
            "subtotal" => $itemSubtotal
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | 5. HITUNG TOTAL TERBARU
    |--------------------------------------------------------------------------
    */

    $total =
        $subtotal + $shipping;


    /*
    |--------------------------------------------------------------------------
    | 6. SIMPAN ORDER
    |--------------------------------------------------------------------------
    */

    $paymentStatus =
        "Menunggu Pembayaran";

    $orderStatus =
        "Menunggu Pembayaran";


    $stmtOrder = $conn->prepare("
        INSERT INTO orders
        (
            order_code,
            user_id,
            address_id,
            subtotal,
            shipping,
            total,
            payment_method,
            payment_status,
            order_status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");


    $stmtOrder->bind_param(
        "siiddssss",
        $orderCode,
        $userId,
        $addressId,
        $subtotal,
        $shipping,
        $total,
        $paymentMethod,
        $paymentStatus,
        $orderStatus
    );


    $stmtOrder->execute();


    $orderId =
        $conn->insert_id;


    /*
    |--------------------------------------------------------------------------
    | 7. SIMPAN ORDER ITEMS + KURANGI STOK
    |--------------------------------------------------------------------------
    */

    foreach ($cartItems as $item) {

        $productId =
            $item["id"];

        $productName =
            $item["name"];

        $price =
            $item["price"];

        $quantity =
            $item["quantity"];

        $itemSubtotal =
            $item["subtotal"];


        /*
        | Simpan detail order
        */
        $stmtItem->bind_param(
            "iisdid",
            $orderId,
            $productId,
            $productName,
            $price,
            $quantity,
            $itemSubtotal
        );

        $stmtItem->execute();


        /*
        | Kurangi stok
        */
        $stmtStock->bind_param(
            "iii",
            $quantity,
            $productId,
            $quantity
        );

        $stmtStock->execute();


        /*
        | Pastikan stok benar-benar berkurang
        */
        if ($stmtStock->affected_rows !== 1) {

            throw new Exception(
                "Gagal mengurangi stok produk: "
                . $productName
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 8. COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | 9. SIMPAN ORDER TERAKHIR
    |--------------------------------------------------------------------------
    */

    $_SESSION["last_order_code"] =
        $orderCode;

    $_SESSION["last_order_id"] =
        $orderId;


    /*
    |--------------------------------------------------------------------------
    | 10. KOSONGKAN KERANJANG
    |--------------------------------------------------------------------------
    */

    $_SESSION["cart"] = [];

    unset($_SESSION["checkout"]);


} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    $conn->rollback();


    die(
        "Pesanan gagal dibuat: "
        . htmlspecialchars(
            $e->getMessage()
        )
    );
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
        Pesanan Berhasil - SecondSpace PreLoved
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
            min-height: 100vh;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #171717;
            color: white;

            padding: 18px 50px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            box-shadow:
                0 3px 15px rgba(0, 0, 0, 0.15);
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .brand span {
            color: #cfcfcf;
            font-weight: normal;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-menu a {
            color: #ddd;
            text-decoration: none;
            font-size: 14px;

            transition: 0.2s;
        }

        .nav-menu a:hover {
            color: white;
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 100%;
            max-width: 720px;

            margin: 55px auto;

            padding: 0 20px 60px;
        }

        /* =========================
           SUCCESS CARD
        ========================= */

        .card {
            background: white;

            border-radius: 24px;

            padding: 42px;

            text-align: center;

            box-shadow:
                0 12px 40px rgba(0, 0, 0, 0.07);

            border: 1px solid #eee;
        }

        /* =========================
           SUCCESS ICON
        ========================= */

        .success-icon {
            width: 88px;
            height: 88px;

            margin: 0 auto 22px;

            border-radius: 50%;

            background: #e8f5e9;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #2e7d32;

            font-size: 42px;
            font-weight: bold;

            box-shadow:
                0 8px 20px rgba(46, 125, 50, 0.12);
        }

        /* =========================
           TITLE
        ========================= */

        .title {
            font-size: 30px;

            margin-bottom: 10px;
        }

        .message {
            color: #777;

            font-size: 14px;

            line-height: 1.7;

            max-width: 480px;

            margin: 0 auto 28px;
        }

        /* =========================
           ORDER CODE
        ========================= */

        .order-code {
            background: #f6f6f4;

            border: 1px solid #e7e7e4;

            border-radius: 16px;

            padding: 20px;

            margin-bottom: 22px;
        }

        .order-code-label {
            display: block;

            color: #888;

            font-size: 12px;

            text-transform: uppercase;

            letter-spacing: 0.8px;

            margin-bottom: 8px;
        }

        .order-code strong {
            display: block;

            font-size: 24px;

            letter-spacing: 1px;
        }

        /* =========================
           ORDER INFO
        ========================= */

        .info {
            text-align: left;

            background: #fafafa;

            border: 1px solid #eee;

            border-radius: 16px;

            padding: 20px;

            margin-bottom: 20px;
        }

        .info-title {
            font-size: 14px;

            font-weight: bold;

            margin-bottom: 15px;
        }

        .info-row {
            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 10px 0;

            border-bottom: 1px solid #eee;

            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row:first-of-type {
            padding-top: 0;
        }

        .info-label {
            color: #888;
        }

        .info-value {
            text-align: right;

            font-weight: 600;
        }

        .status {
            color: #a66a00;

            background: #fff5d9;

            border-radius: 20px;

            padding: 5px 10px;

            font-size: 12px;
        }

        /* =========================
           ADDRESS
        ========================= */

        .address {
            text-align: left;

            background: #f6f6f4;

            border-radius: 15px;

            padding: 18px;

            margin-bottom: 25px;

            font-size: 13px;

            line-height: 1.7;

            color: #555;
        }

        .address-title {
            color: #222;

            font-weight: bold;

            margin-bottom: 7px;
        }

        /* =========================
           BUTTON
        ========================= */

        .buttons {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 12px;

            margin-top: 10px;
        }

        .btn {
            display: flex;

            align-items: center;
            justify-content: center;

            min-height: 50px;

            padding: 14px 18px;

            border-radius: 12px;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

            transition: 0.25s;
        }

        .btn-primary {
            background: #1c1c1c;

            color: white;
        }

        .btn-primary:hover {
            background: #3a3a3a;

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary {
            background: #eeeeec;

            color: #222;
        }

        .btn-secondary:hover {
            background: #dededb;

            transform: translateY(-2px);
        }

        /* =========================
           FOOTER NOTE
        ========================= */

        .note {
            color: #999;

            font-size: 12px;

            line-height: 1.6;

            margin-top: 22px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .navbar {
                padding: 16px 20px;

                flex-direction: column;

                gap: 12px;
            }

            .nav-menu {
                gap: 14px;

                flex-wrap: wrap;

                justify-content: center;
            }

            .nav-menu a {
                font-size: 12px;
            }

            .container {
                margin-top: 30px;
            }

            .card {
                padding: 28px 20px;

                border-radius: 20px;
            }

            .title {
                font-size: 26px;
            }

            .buttons {
                grid-template-columns: 1fr;
            }

            .info-row {
                flex-direction: column;

                gap: 4px;
            }

            .info-value {
                text-align: left;
            }

        }

    </style>

</head>

<body>


    <!-- =========================
         NAVBAR
    ========================== -->

    <nav class="navbar">

        <div class="brand">
            SecondSpace <span>PreLoved</span>
        </div>

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

            <a href="logout.php">
                Logout
            </a>

        </div>

    </nav>


    <!-- =========================
         MAIN
    ========================== -->

    <div class="container">

        <div class="card">


            <!-- SUCCESS -->

            <div class="success-icon">
                ✓
            </div>


            <h1 class="title">
                Pesanan Berhasil!
            </h1>


            <p class="message">
                Terima kasih telah berbelanja di
                SecondSpace PreLoved.
                Pesanan kamu berhasil dibuat dan
                sedang menunggu proses pembayaran.
            </p>


            <!-- ORDER CODE -->

            <div class="order-code">

                <span class="order-code-label">
                    Nomor Pesanan
                </span>

                <strong>
                    <?= htmlspecialchars($orderCode) ?>
                </strong>

            </div>


            <!-- INFO -->

            <div class="info">

                <div class="info-title">
                    Detail Pesanan
                </div>


                <div class="info-row">

                    <span class="info-label">
                        Metode Pembayaran
                    </span>

                    <span class="info-value">
                        <?= htmlspecialchars($paymentMethod) ?>
                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Total Pembayaran
                    </span>

                    <span class="info-value">
                        Rp <?= number_format(
                            $total,
                            0,
                            ",",
                            "."
                        ) ?>
                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Status Pembayaran
                    </span>

                    <span class="info-value">

                        <span class="status">
                            Menunggu Pembayaran
                        </span>

                    </span>

                </div>

            </div>


            <!-- ADDRESS -->

            <div class="address">

                <div class="address-title">
                    📍 Alamat Pengiriman
                </div>

                <strong>
                    <?= htmlspecialchars($recipientName) ?>
                </strong>

                <br>

                <?= htmlspecialchars($phone) ?>

                <br>

                <?= nl2br(
                    htmlspecialchars($address)
                ) ?>

                <br>

                <?= htmlspecialchars($city) ?>,
                <?= htmlspecialchars($province) ?>

                <br>

                <?= htmlspecialchars($postalCode) ?>

            </div>


            <!-- BUTTON -->

            <div class="buttons">

                <a
                    href="pesanan.php"
                    class="btn btn-primary"
                >
                    📦 Lihat Pesanan
                </a>


                <a
                    href="produk.php"
                    class="btn btn-secondary"
                >
                    🛍️ Belanja Lagi
                </a>

            </div>


            <div class="note">
                Simpan nomor pesanan kamu untuk memudahkan
                pengecekan status pesanan.
            </div>


        </div>

    </div>

</body>

</html>
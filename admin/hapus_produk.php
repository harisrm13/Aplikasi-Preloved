<?php

require_once "auth_admin.php";
require_once "../db.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: produk.php");
    exit;
}

/* Ambil data produk */
$stmt = $conn->prepare("
    SELECT id, name, image
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

$imageName = $product["image"];

/* Mulai transaction */
$conn->begin_transaction();

try {

    /* Lepaskan hubungan produk dari riwayat pesanan */
    $stmt = $conn->prepare("
        UPDATE order_items
        SET product_id = NULL
        WHERE product_id = ?
    ");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();

    /* Hapus produk */
    $stmt = $conn->prepare("
        DELETE FROM products
        WHERE id = ?
    ");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    if ($stmt->affected_rows <= 0) {
        $stmt->close();
        throw new Exception("Produk tidak berhasil dihapus.");
    }

    $stmt->close();

    /* Simpan perubahan */
    $conn->commit();

    /* Hapus gambar */
    if (
        !empty($imageName) &&
        file_exists("../images/" . $imageName)
    ) {
        unlink("../images/" . $imageName);
    }

    header(
        "Location: produk.php?success=" .
        urlencode("Produk berhasil dihapus.")
    );

    exit;

} catch (Exception $e) {

    $conn->rollback();

    header(
        "Location: produk.php?error=" .
        urlencode("Produk gagal dihapus: " . $e->getMessage())
    );

    exit;
}

?>
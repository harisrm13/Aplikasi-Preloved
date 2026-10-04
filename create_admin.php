<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["username"] !== "admin") {
    die("Akses ditolak.");
}

require_once "db.php";

// Cek apakah akun admin sudah ada
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$username = "admin";
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("
        <h2>Akun Admin Sudah Ada</h2>
        <p>Akun admin sudah dibuat sebelumnya.</p>
        <a href='admin/index.php'>Kembali ke Dashboard Admin</a>
    ");
}
?>
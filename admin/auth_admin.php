<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// Hanya akun admin dengan ID 1 yang boleh masuk
if ((int) $_SESSION["user_id"] !== 1) {
    header("Location: ../index.php");
    exit;
}

?>
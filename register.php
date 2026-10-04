<?php
session_start();

require_once "db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

$name = "";
$username = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        $name === "" ||
        $username === "" ||
        $email === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {

        $error = "Semua data wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } elseif (strlen($username) < 3) {

        $error = "Username minimal 3 karakter.";

    } elseif (strlen($password) < 6) {

        $error = "Password minimal 6 karakter.";

    } elseif ($password !== $confirmPassword) {

        $error = "Konfirmasi password tidak sesuai.";

    } else {

        /* CEK USERNAME / EMAIL */

        $stmt = $conn->prepare(
            "SELECT id
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "ss",
            $username,
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "Username atau email sudah digunakan.";

        } else {

            /* HASH PASSWORD */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            /* SIMPAN USER */

            $stmt = $conn->prepare(
                "INSERT INTO users
                (username, name, email, password)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $username,
                $name,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                $success = "Akun berhasil dibuat. Silakan login.";

                $name = "";
                $username = "";
                $email = "";

            } else {

                $error = "Akun gagal dibuat. Silakan coba lagi.";

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

<title>Daftar - SecondSpace PreLoved</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    min-height: 100vh;

    font-family: Arial, Helvetica, sans-serif;

    background:
        radial-gradient(
            circle at top left,
            #333 0%,
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #111 0%,
            #1d1d1d 50%,
            #0d0d0d 100%
        );

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 25px;
}

/* =========================
   WRAPPER
========================= */

.register-wrapper {
    width: 100%;
    max-width: 950px;

    display: grid;
    grid-template-columns: 1fr 1fr;

    background: white;

    border-radius: 24px;

    overflow: hidden;

    box-shadow:
        0 30px 80px rgba(0,0,0,0.35);
}

/* =========================
   BRAND
========================= */

.register-brand {
    position: relative;

    min-height: 650px;

    padding: 50px;

    background:
        linear-gradient(
            135deg,
            rgba(0,0,0,0.96),
            rgba(35,35,35,0.94)
        );

    color: white;

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    overflow: hidden;
}

.register-brand::before {
    content: "";

    position: absolute;

    width: 300px;
    height: 300px;

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 50%;

    right: -110px;
    top: -90px;
}

.register-brand::after {
    content: "";

    position: absolute;

    width: 230px;
    height: 230px;

    border: 1px solid rgba(255,255,255,0.06);

    border-radius: 50%;

    left: -100px;
    bottom: -80px;
}

.brand-content {
    position: relative;
    z-index: 2;
}

.logo {
    font-size: 26px;
    font-weight: 800;

    letter-spacing: -0.8px;
}

.logo span {
    color: #888;
}

.brand-tag {
    display: inline-block;

    margin-top: 70px;

    padding: 7px 11px;

    border: 1px solid rgba(255,255,255,0.2);

    border-radius: 999px;

    font-size: 11px;
    font-weight: 700;

    letter-spacing: 1.2px;

    color: #bbb;
}

.brand-content h1 {
    margin: 18px 0 15px;

    font-size: 40px;

    line-height: 1.08;

    letter-spacing: -1.5px;
}

.brand-content p {
    max-width: 350px;

    margin: 0;

    color: #aaa;

    font-size: 14px;

    line-height: 1.7;
}

.brand-footer {
    position: relative;
    z-index: 2;

    color: #777;

    font-size: 12px;

    line-height: 1.6;
}

/* =========================
   REGISTER PANEL
========================= */

.register-panel {
    padding: 45px 50px;

    display: flex;
    flex-direction: column;
    justify-content: center;

    background: white;
}

.register-header {
    margin-bottom: 25px;
}

.small-title {
    color: #888;

    font-size: 11px;
    font-weight: 700;

    letter-spacing: 1.4px;

    text-transform: uppercase;

    margin-bottom: 8px;
}

.register-header h2 {
    margin: 0;

    font-size: 30px;

    letter-spacing: -0.8px;

    color: #111;
}

.register-header p {
    margin: 8px 0 0;

    color: #888;

    font-size: 13px;

    line-height: 1.5;
}

/* =========================
   ALERT
========================= */

.alert {
    padding: 12px 14px;

    border-radius: 10px;

    margin-bottom: 18px;

    font-size: 13px;

    line-height: 1.5;
}

.error {
    background: #fff1f1;

    border: 1px solid #f2d0d0;

    color: #b42318;
}

.success {
    background: #edf9f1;

    border: 1px solid #ccebd7;

    color: #18713b;
}

/* =========================
   FORM
========================= */

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;

    margin-bottom: 7px;

    font-size: 13px;

    font-weight: 700;

    color: #333;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;

    left: 14px;
    top: 50%;

    transform: translateY(-50%);

    color: #999;

    font-size: 13px;

    pointer-events: none;
}

.form-group input {
    width: 100%;

    height: 45px;

    padding: 0 14px 0 40px;

    border: 1px solid #ddd;

    border-radius: 10px;

    background: #fafafa;

    color: #111;

    font-family: inherit;

    font-size: 14px;

    outline: none;

    transition: 0.2s ease;
}

.form-group input:focus {
    background: white;

    border-color: #111;

    box-shadow:
        0 0 0 3px rgba(0,0,0,0.06);
}

.form-group input::placeholder {
    color: #aaa;
}

/* =========================
   BUTTON
========================= */

.btn-register {
    width: 100%;

    height: 47px;

    margin-top: 4px;

    border: none;

    border-radius: 10px;

    background: #111;

    color: white;

    font-family: inherit;

    font-size: 14px;

    font-weight: 700;

    cursor: pointer;

    transition: 0.2s ease;
}

.btn-register:hover {
    background: #292929;

    transform: translateY(-1px);

    box-shadow:
        0 8px 20px rgba(0,0,0,0.18);
}

/* =========================
   LOGIN LINK
========================= */

.login-link {
    margin-top: 20px;

    text-align: center;

    color: #888;

    font-size: 13px;
}

.login-link a {
    color: #111;

    font-weight: 700;

    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

/* =========================
   INFO
========================= */

.info-box {
    display: flex;

    gap: 9px;

    margin-top: 18px;

    padding: 12px 14px;

    background: #f6f6f6;

    border-radius: 10px;

    color: #777;

    font-size: 11px;

    line-height: 1.6;
}

.info-box strong {
    color: #444;
}

/* =========================
   RESPONSIVE
========================= */

@media (max-width: 800px) {

    .register-wrapper {
        max-width: 500px;

        grid-template-columns: 1fr;
    }

    .register-brand {
        min-height: 270px;

        padding: 32px;
    }

    .brand-tag {
        margin-top: 35px;
    }

    .brand-content h1 {
        font-size: 32px;
    }

    .brand-footer {
        display: none;
    }

    .register-panel {
        padding: 40px 32px;
    }
}

@media (max-width: 480px) {

    body {
        padding: 15px;
    }

    .register-wrapper {
        border-radius: 18px;
    }

    .register-brand {
        min-height: 220px;

        padding: 25px;
    }

    .logo {
        font-size: 22px;
    }

    .brand-tag {
        margin-top: 25px;
    }

    .brand-content h1 {
        font-size: 28px;
    }

    .brand-content p {
        font-size: 12px;
    }

    .register-panel {
        padding: 30px 22px;
    }

    .register-header h2 {
        font-size: 26px;
    }
}

</style>

</head>

<body>

<div class="register-wrapper">

    <!-- BRAND -->

    <div class="register-brand">

        <div class="brand-content">

            <div class="logo">
                SecondSpace<span>.</span>
            </div>

            <div class="brand-tag">
                PRELOVED STORE
            </div>

            <h1>
                Start Your<br>
                SecondSpace.
            </h1>

            <p>
                Buat akun dan mulai menjelajahi berbagai
                pilihan fashion preloved untuk menemukan
                barang yang sesuai dengan gaya kamu.
            </p>

        </div>

        <div class="brand-footer">
            SecondSpace PreLoved<br>
            Sustainable fashion marketplace.
        </div>

    </div>


    <!-- REGISTER -->

    <div class="register-panel">

        <div class="register-header">

            <div class="small-title">
                Create Account
            </div>

            <h2>
                Buat Akun
            </h2>

            <p>
                Lengkapi data berikut untuk membuat akun
                SecondSpace.
            </p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="alert error">
                ⚠ <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="alert success">
                ✓ <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>
                    Nama Lengkap
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">
                        👤
                    </span>

                    <input
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars($name) ?>"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Username
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">
                        @
                    </span>

                    <input
                        type="text"
                        name="username"
                        value="<?= htmlspecialchars($username) ?>"
                        placeholder="Buat username"
                        autocomplete="username"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Email
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">
                        ✉
                    </span>

                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="Masukkan email"
                        autocomplete="email"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">
                        🔒
                    </span>

                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 6 karakter"
                        autocomplete="new-password"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Konfirmasi Password
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">
                        🔐
                    </span>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Masukkan ulang password"
                        autocomplete="new-password"
                        required
                    >

                </div>

            </div>


            <button
                type="submit"
                class="btn-register"
            >
                Buat Akun
            </button>

        </form>


        <div class="login-link">

            Sudah punya akun?

            <a href="login.php">
                Login di sini
            </a>

        </div>


        <div class="info-box">

            <span>💡</span>

            <div>
                <strong>Keamanan:</strong>
                Password akan disimpan dalam bentuk
                terenkripsi menggunakan sistem hashing PHP.
            </div>

        </div>

    </div>

</div>

</body>

</html>
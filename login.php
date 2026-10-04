<?php

session_start();

require_once "db.php";

/*
|--------------------------------------------------------------------------
| Jika sudah login
|--------------------------------------------------------------------------
*/

if (isset($_SESSION["user_id"])) {

    if ((int) $_SESSION["user_id"] === 1) {
        header("Location: admin/index.php");
        exit;
    }

    header("Location: index.php");
    exit;
}

$error = "";

/*
|--------------------------------------------------------------------------
| PROSES LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Username dan password wajib diisi.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                id,
                name,
                username,
                password
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | CEK USERNAME & PASSWORD
        |--------------------------------------------------------------------------
        */

        if ($user && password_verify($password, $user["password"])) {

            /*
            |--------------------------------------------------------------------------
            | SIMPAN SESSION
            |--------------------------------------------------------------------------
            */

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["name"] = $user["name"];

            /*
            |--------------------------------------------------------------------------
            | INISIALISASI KERANJANG
            |--------------------------------------------------------------------------
            */

            if (!isset($_SESSION["cart"])) {
                $_SESSION["cart"] = [];
            }

            /*
            |--------------------------------------------------------------------------
            | REDIRECT ADMIN / USER
            |--------------------------------------------------------------------------
            */

            if ((int) $user["id"] === 1) {

                header("Location: admin/index.php");
                exit;

            }

            header("Location: index.php");
            exit;

        } else {

            $error = "Username atau password salah.";

        }
    }
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SecondSpace PreLoved</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            background:
                linear-gradient(
                    135deg,
                    #111827,
                    #1f2937,
                    #374151
                );
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.25);
        }

        /*
        |--------------------------------------------------------------------------
        | LEFT SIDE
        |--------------------------------------------------------------------------
        */

        .login-left {
            position: relative;
            background:
                linear-gradient(
                    145deg,
                    #111827,
                    #1f2937
                );
            color: white;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .login-left::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(167, 243, 208, 0.08);
            top: -100px;
            right: -100px;
        }

        .login-left::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            bottom: -90px;
            left: -70px;
        }

        .brand {
            position: relative;
            z-index: 2;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .brand span {
            color: #a7f3d0;
        }

        .tagline {
            position: relative;
            z-index: 2;
            color: #d1d5db;
            font-size: 15px;
            line-height: 1.7;
            max-width: 390px;
        }

        .feature-list {
            position: relative;
            z-index: 2;
            margin-top: 38px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e5e7eb;
            font-size: 14px;
        }

        .feature-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: rgba(167, 243, 208, 0.12);
            color: #a7f3d0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | RIGHT SIDE
        |--------------------------------------------------------------------------
        */

        .login-right {
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            font-size: 30px;
            color: #111827;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 13px 15px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        /*
        |--------------------------------------------------------------------------
        | FORM
        |--------------------------------------------------------------------------
        */

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-box {
            position: relative;
        }

        .input-box span {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 15px;
        }

        .input-box input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            transition: 0.2s;
        }

        .input-box input:focus {
            border-color: #111827;
            box-shadow:
                0 0 0 3px rgba(17, 24, 39, 0.08);
        }

        /*
        |--------------------------------------------------------------------------
        | BUTTON
        |--------------------------------------------------------------------------
        */

        .login-button {
            width: 100%;
            border: none;
            background: #111827;
            color: white;
            padding: 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 5px;
        }

        .login-button:hover {
            background: #000000;
            transform: translateY(-1px);
            box-shadow:
                0 8px 20px rgba(17, 24, 39, 0.18);
        }

        /*
        |--------------------------------------------------------------------------
        | REGISTER
        |--------------------------------------------------------------------------
        */

        .register-text {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 14px;
        }

        .register-text a {
            color: #111827;
            font-weight: 700;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .copyright {
            text-align: center;
            margin-top: 35px;
            color: #9ca3af;
            font-size: 12px;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 800px) {

            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .login-left {
                padding: 35px;
                min-height: 300px;
            }

            .feature-list {
                margin-top: 25px;
            }

            .login-right {
                padding: 40px 35px;
            }

        }

        @media (max-width: 500px) {

            body {
                padding: 12px;
            }

            .login-wrapper {
                border-radius: 18px;
            }

            .login-left {
                padding: 30px 25px;
            }

            .login-right {
                padding: 30px 25px;
            }

            .brand {
                font-size: 26px;
            }

            .login-title {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>

    <div class="login-wrapper">

        <!-- LEFT -->

        <div class="login-left">

            <div class="brand">
                Second<span>Space</span>
            </div>

            <p class="tagline">
                Temukan berbagai produk preloved berkualitas
                dan berikan kesempatan kedua untuk barang favoritmu.
            </p>

            <div class="feature-list">

                <div class="feature">

                    <div class="feature-icon">
                        ✓
                    </div>

                    <span>
                        Produk preloved pilihan
                    </span>

                </div>

                <div class="feature">

                    <div class="feature-icon">
                        ✓
                    </div>

                    <span>
                        Proses belanja mudah
                    </span>

                </div>

                <div class="feature">

                    <div class="feature-icon">
                        ✓
                    </div>

                    <span>
                        Pesanan tersimpan dengan aman
                    </span>

                </div>

            </div>

        </div>


        <!-- RIGHT -->

        <div class="login-right">

            <h1 class="login-title">
                Selamat Datang
            </h1>

            <p class="login-subtitle">
                Silakan masuk ke akun SecondSpace kamu
                untuk melanjutkan.
            </p>


            <?php if ($error !== ""): ?>

                <div class="error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-box">

                        <span>👤</span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($_POST["username"] ?? "") ?>"
                            autocomplete="username"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-box">

                        <span>🔒</span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                    </div>

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Masuk
                </button>

            </form>


            <div class="register-text">

                Belum punya akun?

                <a href="register.php">
                    Daftar sekarang
                </a>

            </div>


            <div class="copyright">

                © <?= date("Y") ?> SecondSpace PreLoved

            </div>

        </div>

    </div>

</body>

</html>
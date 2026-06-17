<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = $_SESSION['error'] ?? '';
$old_email = $_SESSION['old_email'] ?? '';

unset($_SESSION['error'], $_SESSION['old_email']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskArchive - Login</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">

    <div class="bg-white rounded-2xl shadow-md w-full max-w-sm p-8">

        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-blue-600">
                TaskArchive
            </h1>

            <p class="text-lg font-semibold text-gray-800 mt-2">
                Selamat Datang Kembali
            </p>

            <p class="text-sm text-gray-500 mt-1">
                Masuk untuk mengakses arsip tugas kuliah Anda.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="auth/login_process.php" method="POST">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($old_email) ?>"
                    autocomplete="email"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kata Sandi
                </label>

                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>
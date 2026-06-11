<?php

require_once 'includes/auth_check.php';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TaskArchive</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-sm">

        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <h1 class="text-xl font-bold text-blue-600">
                TaskArchive
            </h1>

            <a
                href="auth/logout.php"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm"
            >
                Logout
            </a>

        </div>

    </nav>

    <div class="max-w-7xl mx-auto p-6">

        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Login Berhasil 🎉
            </h2>

            <p class="text-gray-600">
                Selamat datang,
                <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
            </p>

            <div class="mt-4 text-sm text-gray-500">
                Email:
                <?= htmlspecialchars($_SESSION['email']) ?>
            </div>

        </div>

    </div>

</body>
</html>
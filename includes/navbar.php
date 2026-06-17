<nav class="bg-white shadow-sm px-6 py-4 relative flex items-center justify-between">
    <span class="text-blue-600 font-bold text-lg">
        Task Archive
    </span>

    <div class="absolute left-1/2 -translate-x-1/2 flex items-center gap-6">
        <a href="/dashboard.php" class="<?= ($page == 'dashboard.php') ? 'text-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600' ?>">Dashboard</a>
        <a href="/tasks/index.php" class="<?= ($page == 'index.php') ? 'text-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600' ?>">Data Tugas</a>
    </div>

    <a href="/auth/logout.php"
        class="border border-gray-300 hover:border-red-500 hover:text-red-500 text-gray-600 text-sm font-medium px-5 py-1.5 rounded-lg transition    ">
        Logout
    </a>
</nav>

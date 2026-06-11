<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

?>

<h1>
    Selamat Datang,
    <?= $_SESSION['nama']; ?>
</h1>
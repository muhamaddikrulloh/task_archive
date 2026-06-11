<?php
session_start();
require_once '../config/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE email='$email'"
);

$user = mysqli_fetch_assoc($query);

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];

    header('Location: ../dashboard.php');
    exit;
}

header('Location: login.php');
exit;
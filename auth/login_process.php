<?php
session_start();
require_once '../includes/db.php';

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {

    $_SESSION['error'] = 'Email dan kata sandi wajib diisi.';
    $_SESSION['old_email'] = $email;

    header('Location: ../login.php');
    exit;
}

$stmt = $conn->prepare(
    "SELECT * FROM users WHERE email = ? LIMIT 1"
);

$stmt->bind_param('s', $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {

    $_SESSION['error'] = 'Email atau kata sandi salah.';
    $_SESSION['old_email'] = $email;

    header('Location: ../login.php');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['email'] = $user['email'];

header('Location: ../dashboard.php');
exit;
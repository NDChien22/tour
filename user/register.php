<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/fnCSDL.php';

ensure_session_started();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $phone === '' || $password === '') {
        $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin.';
        header('Location: index.php');
        exit;
    }

    $conn = ConnectDB();

    // Kiểm tra tồn tại username/email
    $checkStmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $checkStmt->execute([$username, $email]);

    if ($checkStmt->fetch()) {
        $_SESSION['flash_error'] = 'Người dùng đã tồn tại.';
        header('Location: index.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $insertStmt = $conn->prepare('INSERT INTO users (username, email, phone, password, user_type) VALUES (?, ?, ?, ?, "user")');

    if ($insertStmt->execute([$username, $email, $phone, $hashedPassword])) {
        $newUserId = (int) $conn->lastInsertId();
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_email'] = $email;
        $_SESSION['username'] = $username;

        $_SESSION['flash_success'] = 'Đăng ký thành công.';
        header('Location: index.php');
        exit;
    }

    $_SESSION['flash_error'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
    header('Location: index.php');
    exit;
}

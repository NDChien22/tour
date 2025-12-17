<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/fnCSDL.php';

ensure_session_started();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin đăng nhập']);
        exit;
    }

    $conn = ConnectDB();
    $sql = "SELECT user_id, username, email, password, user_type FROM users WHERE username = ? AND user_type = 'user' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && verifyPasswordAndUpgrade($password, $user['password'], (int) $user['user_id'], $conn)) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['username'] = $user['username'];

        // Cập nhật trạng thái booking quá hạn
        $update = $conn->prepare("UPDATE bookings SET booking_status = 'complete' WHERE booking_status = 'pending' AND departure_date <= NOW()");
        $update->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng']);
    }
    exit;
}

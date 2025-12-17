<?php
session_start();
include 'fnCSDL.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = ConnectDB();

// Kiểm tra xem form có được submit không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $user_id = $_SESSION['user_id'];

    // Cập nhật thông tin người dùng trong cơ sở dữ liệu
    $sql = "UPDATE users SET email = ?, phone = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email, $phone, $user_id]);

    // Hiển thị thông báo thành công
    echo "<script>alert('Thông tin đã được cập nhật thành công!'); window.location.href='profile.php';</script>";
}
?>

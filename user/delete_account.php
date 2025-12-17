<?php
session_start();
include 'fnCSDL.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = ConnectDB();
$user_id = $_SESSION['user_id'];

// Kiểm tra xem người dùng đã đặt tour hay chưa
$sql_check_booking = "SELECT COUNT(*) FROM bookings WHERE user_id = ?";
$stmt_check = $conn->prepare($sql_check_booking);
$stmt_check->execute([$user_id]);
$booking_count = $stmt_check->fetchColumn();

if ($booking_count > 0) {
    // Nếu người dùng đã đặt tour thì thông báo và không xóa tài khoản
    echo "<script>alert('Tài khoản của bạn đã đặt tour, không thể xóa!'); window.location.href='profile.php';</script>";
} else {
    // Xóa tài khoản người dùng khỏi cơ sở dữ liệu nếu chưa đặt tour
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);

    // Hủy phiên đăng nhập và chuyển hướng về trang chính
    session_destroy();
    echo "<script>alert('Tài khoản của bạn đã bị xóa thành công!'); window.location.href='index.php';</script>";
}
?>

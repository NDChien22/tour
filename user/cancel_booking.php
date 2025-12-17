<?php
session_start();
include 'fnCSDL.php';

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    // Kết nối CSDL
    $conn = ConnectDB();

    // Lấy thông tin booking
    $sql = "SELECT departure_date, booking_status FROM bookings WHERE booking_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if ($booking) {
        $departure_date = new DateTime($booking['departure_date'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $interval = $now->diff($departure_date)->days;

        if ($booking['booking_status'] === 'complete' || $interval <= 1) {
            header('Location: tour_history.php?status=error&message=Không thể hủy tour này');
            exit();
        }

        // Cập nhật trạng thái hủy tour
        $sql = "UPDATE bookings SET booking_status = 'cancel' WHERE booking_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$booking_id]);

        header('Location: tour_history.php?status=success&message=Tour đã được hủy');
    } else {
        header('Location: tour_history.php?status=error&message=Không tìm thấy booking');
    }
    exit();
}

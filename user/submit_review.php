<?php
session_start();
include 'fnCSDL.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tour_id'], $_POST['rating'], $_POST['comment'])) {
    $tour_id = $_POST['tour_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    // Kết nối CSDL
    $conn = ConnectDB();

    // Kiểm tra nếu người dùng đã đánh giá tour này rồi
    $sql = "SELECT * FROM reviews WHERE user_id = ? AND tour_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $tour_id]);

    if ($stmt->rowCount() == 0) {
        // Thêm đánh giá vào bảng reviews
        $sql = "INSERT INTO reviews (user_id, tour_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $tour_id, $rating, $comment]);

        // Thông báo đánh giá thành công
        echo "<script>alert('Đánh giá của bạn đã được gửi thành công!'); window.location.href='tour_history.php';</script>";
    } else {
        // Thông báo đã đánh giá rồi
        echo "<script>alert('Bạn đã đánh giá tour này rồi.'); window.location.href='tour_history.php';</script>";
    }
}

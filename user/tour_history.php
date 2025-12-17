<?php
session_start();
include 'fnCSDL.php';
$isLoggedIn = isset($_SESSION['user_email']);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = ConnectDB();

// Lấy lịch sử đặt tour
$sql = "SELECT b.*, t.title, t.price
        FROM bookings b
        JOIN tours t ON b.tour_id = t.tour_id
        WHERE b.user_id = ? AND b.booking_status != 'cancel'";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Lấy danh sách tour đã hủy
$sql = "SELECT b.*, t.title
        FROM bookings b
        JOIN tours t ON b.tour_id = t.tour_id
        WHERE b.user_id = ? AND b.booking_status = 'cancel'";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$cancelledTours = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/tour_history.css">
    <title>Lịch sử đặt tour</title>
</head>

<body class="p-1">
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand" href="index.php">CTTravel</a>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">Trang chủ</a>
                        </li>
                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="tour_history.php">Lịch sử đặt tour</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="support.php">Liên hệ hỗ trợ</a>
                            </li>
                        <?php else: ?>
                            <!-- Nếu chưa đăng nhập, chuyển hướng đến form đăng nhập -->
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Lịch sử đặt tour</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Liên hệ hỗ trợ</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- User Login/Logout -->
                    <form class="d-flex" role="login">
                        <?php if ($isLoggedIn): ?>
                            <p class="d-flex justify-content-center m-2">
                                <a href="profile.php" class="link-offset-2 link-underline link-underline-opacity-0"><?php echo $_SESSION['username']; ?></a>
                            </p>
                            <a href="logout.php" class="btn btn-outline-danger">Đăng xuất</a>
                        <?php else: ?>
                            <button class="btn btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <!-- Modal Login Form -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Đăng nhập</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Login Form -->
                    <div id="loginForm">
                        <form id="loginFormElement" action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div id="error-message" class="text-danger mt-2"></div>
                            </div>
                            <button type="submit" class="btn btn-primary">Đăng nhập</button>
                            <div class="mt-3">
                                <a href="#" class="text-decoration-none" onclick="showForgotPassword()">Quên mật khẩu?</a>
                            </div>
                            <div class="mt-3">
                                <p class="mb-0">Chưa có tài khoản? <a href="#" class="text-decoration-none" onclick="showRegisterForm()">Đăng ký ngay</a></p>
                            </div>
                        </form>
                    </div>

                    <!-- Register Form -->
                    <div id="registerForm" style="display: none;">
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="regUsername" class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" id="regUsername" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="regEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="regEmail" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="regPhone" class="form-label">Số điện thoại</label>
                                <input type="text" class="form-control" id="regPhone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="regPassword" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="regPassword" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Đăng ký</button>
                            <div class="mt-3">
                                <p class="mb-0">Đã có tài khoản? <a href="#" class="text-decoration-none" onclick="showLoginForm()">Đăng nhập ngay</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content d-flex flex-column">
        <div class="container-fluid history-container">
            <h2>Lịch Sử Đặt Tour</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Tên Tour</th>
                        <th scope="col">Ngày Đặt</th>
                        <th scope="col">Ngày Đi</th>
                        <th scope="col">Số Lượng Người</th>
                        <th scope="col">Tổng Tiền</th>
                        <th scope="col">Đánh Giá</th>
                        <th scope="col">Hủy Tour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                        $departure_date = new DateTime($booking['departure_date'], new DateTimeZone('Asia/Ho_Chi_Minh'));
                        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
                        $interval = $now->diff($departure_date)->days;
                        $canCancel = ($booking['booking_status'] !== 'complete' && $interval > 1);

                        // Kiểm tra nếu tour đã được đánh giá
                        $sql = "SELECT * FROM reviews WHERE user_id = ? AND tour_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user_id, $booking['tour_id']]);
                        $reviewed = $stmt->rowCount() > 0;
                        ?>
                        <tr>
                            <td><a href="tour_details.php?id=<?php echo htmlspecialchars($booking['tour_id']); ?>"><?php echo htmlspecialchars($booking['title']); ?></a></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['departure_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['quantity']); ?></td>
                            <td><?php echo number_format($booking['total_price'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td>
                                <?php if ($booking['booking_status'] === 'complete' && !$reviewed): ?>
                                    <button class="btn btn-rate btn-sm" data-bs-toggle="modal" data-bs-target="#ratingModal" data-tour-id="<?php echo htmlspecialchars($booking['tour_id']); ?>">Đánh Giá</button>
                                <?php elseif ($booking['booking_status'] === 'complete' && $reviewed): ?>
                                    <button class="btn btn-rate btn-sm" disabled>Đánh Giá</button>
                                <?php else: ?>
                                    <button class="btn btn-rate btn-sm" disabled>Đánh Giá</button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($canCancel): ?>
                                    <form action="cancel_booking.php" method="post" class="d-inline" onsubmit="return confirmCancellation()">
                                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                        <button class="btn btn-cancel btn-sm" type="submit">Hủy</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-cancel btn-sm" disabled>Hủy</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Tour Đã Hủy</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Tên Tour</th>
                        <th scope="col">Ngày Đặt</th>
                        <th scope="col">Ngày Đi</th>
                        <th scope="col">Số Lượng Người</th>
                        <th scope="col">Tổng Tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cancelledTours as $cancelledTour): ?>
                        <tr>
                            <td><a href="tour_details.php?id=<?php echo htmlspecialchars($cancelledTour['tour_id']); ?>"><?php echo htmlspecialchars($cancelledTour['title']); ?></a></td>
                            <td><?php echo htmlspecialchars($cancelledTour['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($cancelledTour['departure_date']); ?></td>
                            <td><?php echo htmlspecialchars($cancelledTour['quantity']); ?></td>
                            <td><?php echo number_format($cancelledTour['total_price'], 0, ',', '.') . ' VNĐ'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal đánh giá -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModalLabel">Đánh Giá Tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm" action="submit_review.php" method="post">
                        <input type="hidden" name="tour_id" id="modalTourId">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Xếp Hạng</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="">Chọn xếp hạng</option>
                                <option value="1">1 sao</option>
                                <option value="2">2 sao</option>
                                <option value="3">3 sao</option>
                                <option value="4">4 sao</option>
                                <option value="5">5 sao</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Nhận Xét</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Gửi Đánh Giá</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-black text-center text-light">
        <p class="mb-0 p-3">Copyright © 2022 My Website. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ratingButtons = document.querySelectorAll('.btn-rate');
            ratingButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var tourId = button.getAttribute('data-tour-id');
                    document.getElementById('modalTourId').value = tourId;
                });
            });
        });

        function confirmCancellation() {
            return confirm('Bạn có chắc chắn muốn hủy tour này?');
        }
    </script>
</body>

</html>
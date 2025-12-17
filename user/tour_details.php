<?php
session_start();
include_once 'fnCSDL.php';

$isLoggedIn = isset($_SESSION['user_email']);
// Lấy ID tour từ URL
$tour_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Kết nối đến cơ sở dữ liệu
$conn = ConnectDB();

// Lấy thông tin tour
$tourQuery = $conn->prepare("SELECT * FROM tours WHERE tour_id = ?");
$tourQuery->execute([$tour_id]);
$tour = $tourQuery->fetch(PDO::FETCH_ASSOC);

// Nếu không có tour, chuyển hướng về trang chính
if (!$tour) {
    header('Location: index.php');
    exit;
}

// Lấy đánh giá của tour
$reviewsQuery = $conn->prepare("SELECT * FROM reviews WHERE tour_id = ?");
$reviewsQuery->execute([$tour_id]);
$reviews = $reviewsQuery->fetchAll(PDO::FETCH_ASSOC);

// Tính điểm đánh giá trung bình
$ratingTotal = 0;
$ratingCount = count($reviews);
if ($ratingCount > 0) {
    foreach ($reviews as $review) {
        $ratingTotal += $review['rating'];
    }
    $averageRating = round($ratingTotal / $ratingCount);
} else {
    $averageRating = 0;
}

function formatItinerary($itinerary)
{
    // Chia lịch trình theo từng ngày
    $days = preg_split('/\n/', $itinerary);
    return $days;
}

$itineraryDays = formatItinerary($tour['itinerary']);

// Lấy thông tin đánh giá và người dùng
function getReviewsWithUserInfo($tourId)
{
    $conn = ConnectDB();
    $sql = "
        SELECT r.*, u.username
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.tour_id = :tour_id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':tour_id', $tourId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$reviews = getReviewsWithUserInfo($tour['tour_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/tour_details.css">
    <title>Chi tiết tour</title>
</head>

<body class="p-1 d-flex flex-column">
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger text-center mb-0" role="alert">
            <?php echo htmlspecialchars($_SESSION['flash_error']);
            unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success text-center mb-0" role="alert">
            <?php echo htmlspecialchars($_SESSION['flash_success']);
            unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
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


    <script>
        function showRegisterForm() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        }

        function showLoginForm() {
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        }

        document.getElementById('loginFormElement').onsubmit = function(event) {
            event.preventDefault(); // Ngăn chặn form gửi dữ liệu

            var xhr = new XMLHttpRequest();
            var formData = new FormData(this);

            xhr.open('POST', 'login.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Nếu đăng nhập thành công, reload lại trang hiện tại
                        window.location.reload();
                    } else {
                        // Hiển thị lỗi và focus vào username
                        document.getElementById('error-message').textContent = response.message;
                        document.getElementById('username').focus();
                    }
                }
            };
            xhr.send(formData);
        };
    </script>


    <div class="content">
        <div class="container bg-light">
            <!-- Nút trở lại trang trước -->
            <div class="mb-3">
                <button class="btn btn-secondary" onclick="window.history.back();">Trở lại trang trước</button>
            </div>

            <!-- Phần đầu trang -->
            <div class="tour-header">
                <h1><?php echo htmlspecialchars($tour['title']); ?></h1>
                <p>Thời gian: <?php echo htmlspecialchars($tour['duration']); ?> ngày <?php echo htmlspecialchars($tour['duration'] - 1); ?> đêm | Xuất phát từ: Hà Nội</p>
            </div>

            <!-- Hình ảnh tour -->
            <div class="tour-image">
                <img src="<?php echo htmlspecialchars($tour['url_img']); ?>" alt="<?php echo htmlspecialchars($tour['title']); ?>">
            </div>

            <!-- Mô tả tour -->
            <div class="tour-description">
                <h3>Mô Tả Tour</h3>
                <p><?php echo htmlspecialchars($tour['description']); ?></p>
            </div>

            <!-- Lịch trình tour -->
            <div class="tour-schedule">
                <h3>Lịch Trình</h3>
                <ul>
                    <?php foreach ($itineraryDays as $day): ?>
                        <li style="list-style-type: none;"><?php echo htmlspecialchars($day); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Giá cả và đánh giá -->
            <div class="row">
                <div class="col-md-6">
                    <div class="tour-price"><?php echo number_format($tour['price'], 0, ',', '.'); ?> VNĐ</div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="ratings">
                        <?php if ($ratingCount > 0): ?>
                            <?php echo str_repeat('★', $averageRating); ?>
                            <?php echo str_repeat('☆', 5 - $averageRating); ?> (<?php echo $ratingCount; ?> đánh giá)
                        <?php else: ?>
                            Chưa có đánh giá
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Phần xem đánh giá -->
            <?php if ($ratingCount > 0): ?>
                <div class="tour-reviews">
                    <h3>Đánh Giá Của Khách Hàng</h3>
                    <div class="review-list">
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-rating">
                                        <?php
                                        // Hiển thị đánh giá sao
                                        $rating = intval($review['rating']);
                                        echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                        ?>
                                    </div>
                                    <div class="review-content">
                                        <p><strong><?php echo htmlspecialchars($review['username']); ?></strong> - <?php echo htmlspecialchars(date('d-m-Y', strtotime($review['created_at']))); ?></p>
                                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Chưa có đánh giá nào cho tour này.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Nút đặt tour -->
            <div class="text-center">
                <?php if ($isLoggedIn): ?>
                    <button class="btn btn-primary btn-book" data-bs-toggle="modal" data-bs-target="#bookingModal">Đặt Tour Ngay</button>
                <?php else: ?>
                    <button class="btn btn-primary btn-book" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập để đặt tour</button>
                <?php endif; ?>
            </div>

            <!-- Modal đặt tour -->
            <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bookingModalLabel">Đặt Tour <?php echo htmlspecialchars($tour['title']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="bookingForm" action="payment.php" method="POST">
                                <input type="hidden" name="tour_id" value="<?php echo htmlspecialchars($tour_id); ?>">
                                <div class="mb-3">
                                    <label for="tourName" class="form-label">Tên Tour</label>
                                    <input type="text" class="form-control" id="tourName" name="tourName" value="<?php echo htmlspecialchars($tour['title']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="tourDate" class="form-label">Ngày Đi</label>
                                    <input type="date" class="form-control" id="tourDate" name="tourDate" required>
                                </div>
                                <div class="mb-3">
                                    <label for="numberOfPeople" class="form-label">Số Lượng Người</label>
                                    <input type="number" class="form-control" id="numberOfPeople" name="numberOfPeople" value="1" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="totalPrice" class="form-label">Tổng Tiền</label>
                                    <input type="text" class="form-control" id="totalPrice" name="totalPrice" value="<?php echo htmlspecialchars(number_format($tour['price'], 0, ',', '.')); ?> VNĐ" readonly>
                                </div>
                                <button type="submit" class="btn btn-primary">Xác Nhận</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tourDateInput = document.getElementById('tourDate');
            var numberOfPeopleInput = document.getElementById('numberOfPeople');
            var totalPriceInput = document.getElementById('totalPrice');
            var tourPrice = <?php echo $tour['price']; ?>; // Giá tour từ PHP

            // Hàm tính tổng tiền
            function calculateTotalPrice() {
                var numberOfPeople = parseInt(numberOfPeopleInput.value, 10);
                if (isNaN(numberOfPeople) || numberOfPeople <= 0) {
                    numberOfPeople = 1; // Giá trị mặc định nếu không hợp lệ
                    numberOfPeopleInput.value = 1;
                }
                var totalPrice = numberOfPeople * tourPrice;
                totalPriceInput.value = totalPrice.toLocaleString() + ' VNĐ'; // Hiển thị tổng tiền với định dạng VNĐ
            }

            // Hàm cập nhật giá trị tối thiểu cho ngày đi
            function setMinTourDate() {
                var today = new Date();
                var minDate = new Date();
                minDate.setDate(today.getDate() + 2); // Ngày tối thiểu là ngày hiện tại + 2 ngày

                var year = minDate.getFullYear();
                var month = ('0' + (minDate.getMonth() + 1)).slice(-2);
                var day = ('0' + minDate.getDate()).slice(-2);

                var minDateString = year + '-' + month + '-' + day;
                tourDateInput.setAttribute('min', minDateString);
            }

            // Cập nhật giá trị tối thiểu cho ngày đi khi trang được tải
            setMinTourDate();

            // Cập nhật tổng tiền khi số lượng người thay đổi
            numberOfPeopleInput.addEventListener('input', calculateTotalPrice);

            // Cập nhật tổng tiền khi trang được tải
            calculateTotalPrice();
        });
    </script>

    <footer class="bg-black text-center text-light mt-auto">
        <p class="mb-0 p-3">Copyright © 2022 My Website. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
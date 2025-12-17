<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/fnCSDL.php';

ensure_session_started();

$isLoggedIn = isset($_SESSION['user_email']);

// Chỉ nhận dữ liệu từ form đặt tour
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Retrieve tour_id from POST request
$tour_id = isset($_POST['tour_id']) ? intval($_POST['tour_id']) : 0;

$conn = ConnectDB();

// Fetch tour information
$tourQuery = $conn->prepare('SELECT * FROM tours WHERE tour_id = ?');
$tourQuery->execute([$tour_id]);
$tour = $tourQuery->fetch(PDO::FETCH_ASSOC);

// If no tour found, redirect to home
if (!$tour) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/payment.css">
    <title>Thanh toán</title>
</head>

<body class="p-1 d-flex flex-column">
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
                        <li class="nav-item">
                            <a class="nav-link" href="tour_history.php">Lịch sử đặt tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="support.php">Liên hệ hỗ trợ</a>
                        </li>
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

    <div class="content flex-grow-1">
        <div class="container">
            <h2 class="mb-4 text-center">Thông tin thanh toán</h2>

            <!-- Tour Information -->
            <div class="tour-info mb-4">
                <p><strong>Tên tour:</strong> <?php echo htmlspecialchars($tour['title']); ?></p>
                <p><strong>Ngày đi:</strong> <?php echo htmlspecialchars($_POST['tourDate']); ?></p>
                <p><strong>Số lượng người:</strong> <?php echo htmlspecialchars($_POST['numberOfPeople']); ?></p>
                <p><strong>Tổng tiền:</strong> <?php echo htmlspecialchars(number_format($tour['price'] * $_POST['numberOfPeople'], 0, ',', '.')); ?> VNĐ</p>
            </div>

            <!-- Customer Information Form (based on quantity) -->
            <h4>Nhập thông tin khách hàng</h4>
            <form action="process_payment.php" method="POST" id="paymentForm">
                <input type="hidden" name="tour_id" value="<?php echo htmlspecialchars($tour_id); ?>">
                <input type="hidden" name="departure_date" value="<?php echo htmlspecialchars($_POST['tourDate']); ?>">
                <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($tour['price'] * $_POST['numberOfPeople']); ?>">
                <input type="hidden" name="quantity" value="<?php echo htmlspecialchars($_POST['numberOfPeople']); ?>">
                <?php for ($i = 1; $i <= $_POST['numberOfPeople']; $i++) { ?>
                    <div class="customer-form mb-3">
                        <h5>Khách hàng <?php echo $i; ?></h5>
                        <div class="mb-3">
                            <label for="first_name_<?php echo $i; ?>" class="form-label">Họ</label>
                            <input type="text" class="form-control" id="first_name_<?php echo $i; ?>" name="customers[<?php echo $i; ?>][first_name]" placeholder="Nhập họ">
                        </div>
                        <div class="mb-3">
                            <label for="last_name_<?php echo $i; ?>" class="form-label">Tên</label>
                            <input type="text" class="form-control" id="last_name_<?php echo $i; ?>" name="customers[<?php echo $i; ?>][last_name]" placeholder="Nhập tên">
                        </div>
                        <div class="mb-3">
                            <label for="id_card_number_<?php echo $i; ?>" class="form-label">Số CMND/CCCD</label>
                            <input type="text" class="form-control" id="id_card_number_<?php echo $i; ?>" name="customers[<?php echo $i; ?>][id_card_number]" placeholder="Nhập số CMND/CCCD">
                        </div>
                        <div class="mb-3">
                            <label for="phone_<?php echo $i; ?>" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone_<?php echo $i; ?>" name="customers[<?php echo $i; ?>][phone]" placeholder="Nhập số điện thoại">
                        </div>
                    </div>
                <?php } ?>

                <!-- Payment Method Selection -->
                <div class="payment-method">
                    <h4>Chọn phương thức thanh toán</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" id="creditCard" value="credit_card">
                        <label class="form-check-label" for="creditCard">Thẻ tín dụng</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" id="paypal" value="paypal">
                        <label class="form-check-label" for="paypal">PayPal</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" id="bankTransfer" value="bank_transfer">
                        <label class="form-check-label" for="bankTransfer">Chuyển khoản ngân hàng</label>
                    </div>
                </div>

                <!-- Payment Forms -->
                <div class="payment-forms mt-4">
                    <!-- Credit Card Form -->
                    <div id="creditCardForm" class="payment-form">
                        <h4>Thông tin thẻ tín dụng</h4>
                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">Số thẻ</label>
                            <input type="text" class="form-control" id="cardNumber" placeholder="Nhập số thẻ">
                        </div>
                        <div class="mb-3">
                            <label for="cardExpiry" class="form-label">Ngày hết hạn</label>
                            <input type="text" class="form-control" id="cardExpiry" placeholder="MM/YY">
                        </div>
                        <div class="mb-3">
                            <label for="cardCVC" class="form-label">CVC</label>
                            <input type="text" class="form-control" id="cardCVC" placeholder="Nhập CVC">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Xác nhận thanh toán</button>
                    </div>

                    <!-- PayPal Form -->
                    <div id="paypalForm" class="payment-form">
                        <h4>Thanh toán qua PayPal</h4>
                        <p>Click vào nút dưới để được chuyển đến PayPal.</p>
                        <button type="submit" class="btn btn-primary w-100">Thanh toán với PayPal</button>
                    </div>

                    <!-- Bank Transfer Form -->
                    <div id="bankTransferForm" class="payment-form">
                        <h4>Thông tin chuyển khoản ngân hàng</h4>
                        <p>Chuyển khoản đến tài khoản:</p>
                        <p><strong>Ngân hàng:</strong> MBcombank</p>
                        <p><strong>Số tài khoản:</strong> 20026666888888</p>
                        <p><strong>Chủ tài khoản:</strong> Nguyen Duy Chien</p>
                        <button type="submit" class="btn btn-primary w-100">Xác nhận thanh toán</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-black text-center text-light mt-auto">
        <p class="mb-0 p-3">Copyright © 2024 My Website. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');
            const paymentForms = {
                'credit_card': document.getElementById('creditCardForm'),
                'paypal': document.getElementById('paypalForm'),
                'bank_transfer': document.getElementById('bankTransferForm')
            };

            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    // Hide all forms
                    Object.values(paymentForms).forEach(form => {
                        form.style.display = 'none';
                    });
                    // Show the selected form
                    if (paymentForms[this.value]) {
                        paymentForms[this.value].style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>

</html>
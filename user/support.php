<?php
session_start();
include_once 'fnCSDL.php'; // Kết nối qua fnCSDL.php
$isLoggedIn = isset($_SESSION['user_email']);

// Kiểm tra nếu người dùng đã đăng nhập, nếu không chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Thiết lập múi giờ mặc định
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Xử lý yêu cầu hỗ trợ khi người dùng gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue'])) {
    // Lấy thông tin từ form
    $issue = $_POST['issue'];
    $userId = $_SESSION['user_id'];
    $createdAt = date('Y-m-d H:i:s');

    // Thực hiện truy vấn chèn dữ liệu vào bảng support_requests
    $sql = "INSERT INTO messages (user_id, content, created_at) VALUES (?, ?, ?)";

    // Gọi hàm thực hiện truy vấn
    $result = executeQuery($sql, [$userId, $issue, $createdAt]);

    if ($result) {
        echo "<script>alert('Yêu cầu hỗ trợ của bạn đã được gửi thành công!');</script>";
    } else {
        echo "<script>alert('Có lỗi xảy ra. Vui lòng thử lại.');</script>";
    }
}

// Lấy danh sách các vấn đề hỗ trợ từ database để hiển thị
$sql = "SELECT content, created_at, respond FROM messages WHERE user_id = ?";
$supportRequests = getResults($sql, [$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ Hỗ Trợ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/contact.css">
</head>

<body>
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
        <div class="container">
            <h1 class="text-center mb-4">Liên Hệ Hỗ Trợ</h1>

            <!-- Form gửi yêu cầu hỗ trợ -->
            <form action="support.php" method="post" class="mb-4">
                <div class="mb-3">
                    <label for="issue" class="form-label">Vấn Đề Cần Hỗ Trợ</label>
                    <textarea class="form-control" id="issue" name="issue" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Gửi Yêu Cầu</button>
            </form>

            <!-- Bảng liệt kê các vấn đề đã gửi -->
            <h2 class="text-center mb-4">Các Vấn Đề Đã Gửi</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Vấn Đề</th>
                        <th scope="col">Ngày Gửi</th>
                        <th scope="col">Phản Hồi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($supportRequests)): ?>
                        <?php foreach ($supportRequests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['content']); ?></td>
                                <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($request['respond'] ?? 'Chưa có phản hồi'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Bạn chưa gửi yêu cầu hỗ trợ nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="bg-black text-center text-light">
        <p class="mb-0 p-3">Copyright © 2022 My Website. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
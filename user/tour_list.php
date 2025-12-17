<?php
session_start();
// Include các file cần thiết
include_once 'fnCSDL.php';

$isLoggedIn = isset($_SESSION['user_email']);
$conn = ConnectDB();

// Lấy danh sách khu vực (regions) để hiển thị trong form lọc
$sqlRegions = "SELECT * FROM regions";
$stmtRegions = $conn->prepare($sqlRegions);
$stmtRegions->execute();
$regions = $stmtRegions->fetchAll(PDO::FETCH_ASSOC);

// Thiết lập biến lọc theo khu vực và giá
$selectedRegion = isset($_GET['region']) ? $_GET['region'] : '';
$selectedPrice = isset($_GET['price']) ? $_GET['price'] : '';

// Lấy từ khóa tìm kiếm nếu có
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
// Truy vấn lọc theo khu vực và giá
$sqlTours = "SELECT tours.*, cities.name as city_name, regions.name as region_name
             FROM tours
             LEFT JOIN cities ON tours.city_id = cities.city_id
             LEFT JOIN regions ON cities.region_id = regions.region_id
             WHERE 1 = 1";

// Nếu có lọc theo khu vực
if (!empty($selectedRegion)) {
    $sqlTours .= " AND regions.region_id = :region_id";
}

// Nếu có lọc theo giá
if (!empty($selectedPrice)) {
    if ($selectedPrice == 'low') {
        $sqlTours .= " AND tours.price < 5000000"; // Dưới 5 triệu
    } elseif ($selectedPrice == 'medium') {
        $sqlTours .= " AND tours.price BETWEEN 5000000 AND 10000000"; // 5 triệu - 10 triệu
    } elseif ($selectedPrice == 'high') {
        $sqlTours .= " AND tours.price > 10000000"; // Trên 10 triệu
    }
}
// Nếu có từ khóa tìm kiếm
if (!empty($searchKeyword)) {
    $sqlTours .= " AND (tours.title LIKE :searchKeyword OR cities.name LIKE :searchKeyword OR regions.name LIKE :searchKeyword)";
    $searchKeyword = '%' . $searchKeyword . '%';
}

// Chuẩn bị truy vấn và thực thi
$stmtTours = $conn->prepare($sqlTours);

// Nếu lọc theo khu vực, bind giá trị
if (!empty($selectedRegion)) {
    $stmtTours->bindParam(':region_id', $selectedRegion, PDO::PARAM_INT);
}
if (!empty($searchKeyword)) {
    $stmtTours->bindParam(':searchKeyword', $searchKeyword, PDO::PARAM_STR);
}

$stmtTours->execute();
$tours = $stmtTours->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/tour_list.css">
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

    <div class="content ">
        <div class="container ">
            <h1 class="text-center mb-4">Danh Sách Tour</h1>

            <!-- Bộ lọc khu vực và giá -->
            <form method="GET" action="tour_list.php" class="mb-4">
                <div class="row">
                    <!-- Lọc theo khu vực -->
                    <div class="col-md-4">
                        <label for="region">Khu vực</label>
                        <select name="region" id="region" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo $region['region_id']; ?>" <?php if ($selectedRegion == $region['region_id']) echo 'selected'; ?>>
                                    <?php echo $region['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Lọc theo giá -->
                    <div class="col-md-4">
                        <label for="price">Khoảng giá</label>
                        <select name="price" id="price" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="low" <?php if ($selectedPrice == 'low') echo 'selected'; ?>>Dưới 5 triệu</option>
                            <option value="medium" <?php if ($selectedPrice == 'medium') echo 'selected'; ?>>5 triệu - 10 triệu</option>
                            <option value="high" <?php if ($selectedPrice == 'high') echo 'selected'; ?>>Trên 10 triệu</option>
                        </select>
                    </div>

                    <!-- Thêm ô tìm kiếm -->
                    <div class="col-md-3">
                        <label for="search">Từ khóa tìm kiếm</label>
                        <input type="text" name="search" id="search" class="form-control" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Tìm kiếm tour...">
                    </div>

                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </div>
                </div>
            </form>

            <!-- Nút Hiện tất cả danh sách -->
            <form class="mt-3 mb-3" method="GET" action="tour_list.php">
                <button type="submit" class="btn btn-secondary">Hiện tất cả danh sách</button>
            </form>

            <?php if (!empty($searchKeyword)): ?>
                <p>Kết quả tìm kiếm cho từ khóa: <strong><?php echo htmlspecialchars($_GET['search']); ?></strong></p>
            <?php endif; ?>

            <!-- Danh sách tour -->
            <div class="row">
                <?php foreach ($tours as $tour): ?>
                    <div class="col-md-4 mb-4">
                        <div class="tour-card">
                            <img src="<?php echo htmlspecialchars($tour['url_img']); ?>" alt="<?php echo htmlspecialchars($tour['title']); ?>" class="img-fluid">
                            <div class="tour-details">
                                <h3><?php echo htmlspecialchars($tour['title']); ?></h3>
                                <p>Thời gian: <?php echo htmlspecialchars($tour['duration']); ?> ngày <?php echo htmlspecialchars($tour['duration'] - 1); ?> đêm </p>
                                <p>Giá: <?php echo htmlspecialchars(number_format($tour['price'], 0, ',', '.')); ?> VNĐ</p>
                                <a href="tour_details.php?id=<?php echo htmlspecialchars($tour['tour_id']); ?>" class="btn btn-primary">Xem Chi Tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <footer class="bg-black text-center text-light">
        <p class="mb-0 p-3">Copyright © 2022 My Website. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
require_once __DIR__ . '/../config.php';

ensure_session_started();

// kiểm tra đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// kết nối db
$conn = db_mysqli();

// tìm kiếm và lọc
$searchUsername = isset($_POST['search_username']) ? $_POST['search_username'] : '';
$filterUserType = isset($_POST['filter_user_type']) ? $_POST['filter_user_type'] : '';

$query = "SELECT username, email, phone, user_type FROM users WHERE 1=1";
$params = [];

if ($searchUsername !== '') {
    $query .= " AND username LIKE ?";
    $params[] = "%$searchUsername%";
}

if ($filterUserType !== '') {
    $query .= " AND user_type = ?";
    $params[] = $filterUserType;
}

$userQuery = $conn->prepare($query);

if ($params) {
    $types = str_repeat('s', count($params));
    $userQuery->bind_param($types, ...$params);
}

$userQuery->execute();
$userResult = $userQuery->get_result();

// thêm mới admin
if (isset($_POST['add_admin'])) {
    $username = $_POST['admin_username'];
    $email = $_POST['admin_email'];
    $phone = $_POST['admin_phone'];
    $password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);

    $addAdminQuery = "INSERT INTO users (username, email, phone, user_type, password) 
                      VALUES (?, ?, ?, 'admin', ?)";
    $stmt = $conn->prepare($addAdminQuery);
    $stmt->bind_param("ssss", $username, $email, $phone, $password);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Thêm admin thành công!";
        $_SESSION['message_type'] = "success";
        header("Location: manage_accounts.php");
        exit();
    } else {
        $_SESSION['message'] = "Lỗi: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
}

// xóa người dùng
if (isset($_POST['delete_user'])) {
    $username = $_POST['username'];

    // Kiểm tra nếu tài khoản đang xóa là admin đang đăng nhập
    if ($username === $_SESSION['username']) {
        $_SESSION['message'] = "Bạn không thể xóa tài khoản admin đang đăng nhập!";
        $_SESSION['message_type'] = "error";
        header("Location: manage_accounts.php");
        exit();
    }

    // Lấy user_id dựa trên username
    $getUserIdQuery = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($getUserIdQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_id = $user['user_id'];

    // Kiểm tra xem người dùng có đặt tour không
    $checkBookingQuery = "SELECT COUNT(*) AS booking_count FROM bookings WHERE user_id = ?";
    $stmt = $conn->prepare($checkBookingQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if ($booking['booking_count'] > 0) {
        $_SESSION['message'] = "Người dùng này đã đặt tour, không thể xóa!";
        $_SESSION['message_type'] = "error";
        header("Location: manage_accounts.php");
        exit();
    }

    // Xóa người dùng
    $deleteUserQuery = "DELETE FROM users WHERE username = ?";
    $stmt = $conn->prepare($deleteUserQuery);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Xóa người dùng thành công!";
        $_SESSION['message_type'] = "success";
        header("Location: manage_accounts.php");
        exit();
    } else {
        $_SESSION['message'] = "Lỗi: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
}

// đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// hiện thông báo
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quản lý Tài Khoản</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css"> <!-- Link to external CSS file -->
</head>

<body>
    <!-- Header -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">Admin</span>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white mr-3" id="admin-username">
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <form method="post" class="mb-0">
                    <button class="btn btn-danger" type="submit" name="logout">Đăng xuất</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Sidebar and Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="report.php">Báo cáo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_accounts.php">Quản lý tài khoản</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_tours.php">Quản lý tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_bookings.php">Quản lý đặt tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customer_support.php">Hỗ trợ khách hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_city.php">Thêm Thành Phố</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <h2>Quản lý Tài Khoản</h2>

                <!-- hiện thông báos -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?>" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- tìm kiếm và lọc -->
                <div class="mb-3">
                    <form method="post">
                        <div class="form-row">
                            <div class="col">
                                <input type="text" class="form-control" name="search_username" placeholder="Tìm kiếm theo tên đăng nhập" value="<?php echo htmlspecialchars($searchUsername); ?>">
                            </div>
                            <div class="col">
                                <select class="form-control" name="filter_user_type">
                                    <option value="">Chọn loại người dùng</option>
                                    <option value="admin" <?php echo ($filterUserType === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="customer" <?php echo ($filterUserType === 'customer') ? 'selected' : ''; ?>>Khách hàng</option>
                                </select>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- list acc -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Tên đăng nhập</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Loại người dùng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            <?php while ($row = $userResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($row['username']); ?>">
                                            <button class="btn btn-danger btn-sm" type="submit" name="delete_user">Xóa người dùng</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <button class="btn btn-primary" id="add-admin-btn">Thêm Admin</button>

                <div id="add-admin-form" class="d-none mt-3">
                    <h3>Thêm Admin</h3>
                    <form method="post">
                        <div class="form-group">
                            <label for="admin-username">Tên đăng nhập:</label>
                            <input type="text" class="form-control" name="admin_username" id="admin-username" required>
                        </div>
                        <div class="form-group">
                            <label for="admin-email">Email:</label>
                            <input type="email" class="form-control" name="admin_email" id="admin-email" required>
                        </div>
                        <div class="form-group">
                            <label for="admin-phone">Số điện thoại:</label>
                            <input type="text" class="form-control" name="admin_phone" id="admin-phone" required>
                        </div>
                        <div class="form-group">
                            <label for="admin-password">Mật khẩu:</label>
                            <input type="password" class="form-control" name="admin_password" id="admin-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="add_admin">Thêm Admin</button>
                        <button type="button" class="btn btn-secondary" id="cancel-add-admin-btn">Hủy</button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap and JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('add-admin-btn').addEventListener('click', function() {
            document.getElementById('add-admin-form').classList.remove('d-none');
        });

        document.getElementById('cancel-add-admin-btn').addEventListener('click', function() {
            document.getElementById('add-admin-form').classList.add('d-none');
        });
    </script>
</body>

</html>
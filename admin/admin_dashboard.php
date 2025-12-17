<?php
require_once __DIR__ . '/../config.php';

ensure_session_started();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Lấy tên người dùng từ session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

// Xử lý đăng xuất khi nhấn nút
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// Kết nối CSDL
$conn = db_mysqli();

// Lấy doanh thu theo 12 tháng gần nhất
$sql_revenue = "
    SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') AS month,
        SUM(amount) AS total_revenue
    FROM payments p
    JOIN bookings b ON p.booking_id = b.booking_id
    WHERE b.booking_status = 'complete' 
      AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month
";
$result_revenue = $conn->query($sql_revenue);

// Lấy các tour được đặt nhiều nhất
$sql_top_tours = "
    SELECT 
        t.title AS tour_name,
        COUNT(b.booking_id) AS booking_count
    FROM tours t
    JOIN bookings b ON t.tour_id = b.tour_id
    WHERE b.booking_status = 'complete'
    GROUP BY t.tour_id
    ORDER BY booking_count DESC
    LIMIT 5
";
$result_top_tours = $conn->query($sql_top_tours);

// Lấy các tour có doanh thu cao nhất
$sql_top_revenue_tours = "
    SELECT 
        t.title AS tour_name,
        SUM(b.total_price) AS total_revenue
    FROM tours t
    JOIN bookings b ON t.tour_id = b.tour_id
    WHERE b.booking_status = 'complete'
    GROUP BY t.tour_id
    ORDER BY total_revenue DESC
    LIMIT 5
";
$result_top_revenue_tours = $conn->query($sql_top_revenue_tours);

// Handle search
$search_username = isset($_POST['search_username']) ? $conn->real_escape_string($_POST['search_username']) : '';
$filter_user_type = isset($_POST['filter_user_type']) ? $conn->real_escape_string($_POST['filter_user_type']) : '';

// Query to retrieve user accounts
$sql_users = "
    SELECT username, email, phone, user_type 
    FROM users
    WHERE username LIKE '%$search_username%'
    AND ('$filter_user_type' = '' OR user_type = '$filter_user_type')
";
$result_users = $conn->query($sql_users);

// Handle add admin
if (isset($_POST['add_admin'])) {
    $admin_username = $conn->real_escape_string($_POST['admin_username']);
    $admin_email = $conn->real_escape_string($_POST['admin_email']);
    $admin_phone = $conn->real_escape_string($_POST['admin_phone']);
    $admin_password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);

    $sql_add_admin = "
        INSERT INTO users (username, email, phone, user_type, password)
        VALUES ('$admin_username', '$admin_email', '$admin_phone', 'admin', '$admin_password')
    ";

    if ($conn->query($sql_add_admin) === TRUE) {
        echo "New admin added successfully";
    } else {
        echo "Error: " . $sql_add_admin . "<br>" . $conn->error;
    }
    exit(); // Ngăn không cho tiếp tục thực thi các mã khác
}

// Handle delete user
if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $username_to_delete = $conn->real_escape_string($_POST['username']);

    $sql_delete_user = "DELETE FROM users WHERE username = '$username_to_delete'";

    if ($conn->query($sql_delete_user) === TRUE) {
        echo "User deleted successfully";
    } else {
        echo "Error: " . $sql_delete_user . "<br>" . $conn->error;
    }
    exit(); // Ngăn không cho tiếp tục thực thi các mã khác
}

// Đóng kết nối
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css"> <!-- Link to external CSS file -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show the 'report' section by default
            $('#report').removeClass('d-none');
            $('main section').not('#report').addClass('d-none');

            // Toggle the display of sections
            $('.nav-link').click(function(e) {
                e.preventDefault(); // Ngăn chặn hành động mặc định của liên kết
                const target = $(this).attr('href');
                $('main section').addClass('d-none');
                $(target).removeClass('d-none');

                // Remove 'active' class from all nav links
                $('.nav-link').removeClass('active');
                // Add 'active' class to the clicked nav link
                $(this).addClass('active');
            });

            // Toggle add admin form
            $('#add-admin-btn').click(function() {
                $('#add-admin-form').toggleClass('d-none');
            });

            // Cancel add admin form
            $('#cancel-add-admin-btn').click(function() {
                $('#add-admin-form').addClass('d-none');
            });

            // Handle form submission with AJAX
            $('#search-form').submit(function(e) {
                e.preventDefault(); // Ngăn chặn hành động mặc định của form

                $.ajax({
                    url: 'admin_search.php', // Cập nhật URL đến script xử lý tìm kiếm
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        // Cập nhật nội dung bảng với dữ liệu tìm kiếm
                        $('#user-accounts-table tbody').html(response);
                    },
                    error: function() {
                        alert('Có lỗi xảy ra khi tìm kiếm.');
                    }
                });
            });
        });
    </script>
</head>

<body>
    <!-- Header -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">Admin</span>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white mr-3" id="admin-username"><?php echo htmlspecialchars($username); ?></span>
                <form method="POST" action="">
                    <button type="submit" name="logout" class="btn btn-danger">Đăng xuất</button>
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
                            <a class="nav-link active" href="#report">Báo cáo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage-accounts">Quản lý tài khoản</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage-tours">Quản lý tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage-bookings">Quản lý đặt tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#customer-support">Hỗ trợ khách hàng</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <!-- Báo cáo -->
                <section id="report">
                    <h2>Báo cáo</h2>

                    <!-- Doanh thu theo 12 tháng -->
                    <div class="container mt-4">
                        <h3>Doanh thu theo 12 tháng gần nhất</h3>
                        <canvas id="monthly-revenue-chart"></canvas>
                        <script>
                            const ctx = document.getElementById('monthly-revenue-chart').getContext('2d');
                            const chartData = {
                                labels: [<?php while ($row = $result_revenue->fetch_assoc()) {
                                                echo '"' . $row['month'] . '",';
                                            } ?>],
                                datasets: [{
                                    label: 'Doanh thu',
                                    data: [<?php $result_revenue->data_seek(0);
                                            while ($row = $result_revenue->fetch_assoc()) {
                                                echo $row['total_revenue'] . ',';
                                            } ?>],
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }]
                            };
                            new Chart(ctx, {
                                type: 'line',
                                data: chartData,
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        </script>
                    </div>

                    <!-- Các tour được đặt nhiều nhất -->
                    <div class="container mt-4">
                        <h3>Các tour được đặt nhiều nhất</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Tên Tour</th>
                                        <th>Số lượng Đặt</th>
                                    </tr>
                                </thead>
                                <tbody id="top-tours-table-body">
                                    <?php while ($row = $result_top_tours->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['tour_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['booking_count']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Các tour có doanh thu cao nhất -->
                    <div class="container mt-4">
                        <h3>Các tour có doanh thu cao nhất</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Tên Tour</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody id="top-revenue-tours-table-body">
                                    <?php while ($row = $result_top_revenue_tours->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['tour_name']); ?></td>
                                            <td><?php echo number_format($row['total_revenue'], 2); ?> VNĐ</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Quản lý Tài Khoản -->
                <section id="manage-accounts" class="d-none">
                    <h2>Quản lý Tài Khoản</h2>

                    <!-- Search and Filter -->
                    <div class="mb-3">
                        <form id="search-form">
                            <div class="form-row">
                                <div class="col">
                                    <input type="text" class="form-control" name="search_username" placeholder="Tìm kiếm theo tên đăng nhập">
                                </div>
                                <div class="col">
                                    <select class="form-control" name="filter_user_type">
                                        <option value="">Chọn loại người dùng</option>
                                        <option value="admin">Admin</option>
                                        <option value="customer">Khách hàng</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Table of User Accounts -->
                    <div class="table-responsive">
                        <table id="user-accounts-table" class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Loại người dùng</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dữ liệu người dùng sẽ được nạp bằng AJAX -->
                                <?php while ($row = $result_users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm">Xóa</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Button to Add Admin -->
                    <button class="btn btn-primary" id="add-admin-btn">Thêm Admin</button>

                    <!-- Add Admin Form -->
                    <div id="add-admin-form" class="d-none mt-3">
                        <h3>Thêm Admin</h3>
                        <form method="POST">
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
                            <button type="submit" name="add_admin" class="btn btn-primary">Thêm Admin</button>
                            <button type="button" class="btn btn-secondary" id="cancel-add-admin-btn">Hủy</button>
                        </form>
                    </div>
                </section>

                <!-- Các phần khác của nội dung chính -->
            </main>
        </div>
    </div>
</body>

</html>
<?php
require_once __DIR__ . '/../config.php';

ensure_session_started();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = db_mysqli();

// Truy vấn danh sách đặt tour và phương thức thanh toán
$sql = "SELECT b.booking_id, b.booking_date, b.departure_date, b.total_price, b.booking_status,
               u.username as user_name, t.title as tour_title, p.payment_method
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN tours t ON b.tour_id = t.tour_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id";
$result = $conn->query($sql);

// Truy vấn thông tin khách hàng
$customer_sql = "SELECT c.booking_id, CONCAT(c.first_name, ' ', c.last_name) as customer_name, c.id_card_number, c.phone
                 FROM customers c";
$customer_result = $conn->query($customer_sql);

// Tạo mảng để lưu thông tin khách hàng theo booking_id
$customers_by_booking = [];
while ($row = $customer_result->fetch_assoc()) {
    if (!isset($customers_by_booking[$row['booking_id']])) {
        $customers_by_booking[$row['booking_id']] = [];
    }
    $customers_by_booking[$row['booking_id']][] = $row;
}

$bookings = [
    'pending' => [],
    'complete' => [],
    'cancel' => []
];

while ($row = $result->fetch_assoc()) {
    $status = $row['booking_status'];
    $bookings[$status][] = $row;
}

// Thêm mã xử lý hủy tour ở đầu tập tin PHP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking_id'])) {
    $booking_id = $_POST['cancel_booking_id'];

    // Cập nhật trạng thái tour thành 'cancel'
    $update_sql = "UPDATE bookings SET booking_status = 'cancel' WHERE booking_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('i', $booking_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Tour đã được hủy thành công!</div>';
    } else {
        echo '<div class="alert alert-danger">Có lỗi xảy ra khi hủy tour. Vui lòng thử lại.</div>';
    }

    $stmt->close();
}
// đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đặt Tour - Admin Dashboard</title>
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
                            <a class="nav-link" href="manage_accounts.php">Quản lý tài khoản</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_tours.php">Quản lý tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_bookings.php">Quản lý đặt tour</a>
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
                <h2>Quản lý Đặt Tour</h2>

                <!-- Table of Pending Bookings -->
                <h3>Tour Đang Đặt</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Người đặt tour</th>
                                <th>Tên tour</th>
                                <th>Ngày đặt</th>
                                <th>Ngày đi</th>
                                <th>Thông tin khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Phương thức thanh toán</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bookings['pending'])) : ?>
                                <?php foreach ($bookings['pending'] as $row) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tour_title']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['booking_date']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['departure_date']))); ?></td>
                                        <td>
                                            <?php
                                            if (isset($customers_by_booking[$row['booking_id']])) {
                                                foreach ($customers_by_booking[$row['booking_id']] as $customer) {
                                                    echo '<p>' . htmlspecialchars($customer['customer_name']) . ' - CMND: ' . htmlspecialchars($customer['id_card_number']) . ' - SĐT: ' . htmlspecialchars($customer['phone']) . '</p>';
                                                }
                                            } else {
                                                echo 'Không có thông tin khách hàng';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo number_format($row['total_price'], 0, ',', '.') . ' VND'; ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                        <td>
                                            <form method="post" class="mb-0">
                                                <input type="hidden" name="cancel_booking_id" value="<?php echo htmlspecialchars($row['booking_id']); ?>">
                                                <button class="btn btn-danger" type="submit">Hủy</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8">Không có đơn đặt tour nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>


                <!-- Table of Completed Bookings -->
                <h3>Tour Đã Đi</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Người đặt tour</th>
                                <th>Tên tour</th>
                                <th>Ngày đặt</th>
                                <th>Ngày đi</th>
                                <th>Thông tin khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Phương thức thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bookings['complete'])) : ?>
                                <?php foreach ($bookings['complete'] as $row) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tour_title']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['booking_date']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['departure_date']))); ?></td>
                                        <td>
                                            <?php
                                            // Hiển thị thông tin khách hàng cho booking_id hiện tại
                                            if (isset($customers_by_booking[$row['booking_id']])) {
                                                foreach ($customers_by_booking[$row['booking_id']] as $customer) {
                                                    echo '<p>' . htmlspecialchars($customer['customer_name']) . ' - CMND: ' . htmlspecialchars($customer['id_card_number']) . ' - SĐT: ' . htmlspecialchars($customer['phone']) . '</p>';
                                                }
                                            } else {
                                                echo 'Không có thông tin khách hàng';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo number_format($row['total_price'], 0, ',', '.') . ' VND'; ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7">Không có đơn đặt tour nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table of Canceled Bookings -->
                <h3>Tour Đã Hủy</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Người đặt tour</th>
                                <th>Tên tour</th>
                                <th>Ngày đặt</th>
                                <th>Ngày đi</th>
                                <th>Thông tin khách hàng</th>
                                <th>Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bookings['cancel'])) : ?>
                                <?php foreach ($bookings['cancel'] as $row) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tour_title']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['booking_date']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['departure_date']))); ?></td>
                                        <td>
                                            <?php
                                            // Hiển thị thông tin khách hàng cho booking_id hiện tại
                                            if (isset($customers_by_booking[$row['booking_id']])) {
                                                foreach ($customers_by_booking[$row['booking_id']] as $customer) {
                                                    echo '<p>' . htmlspecialchars($customer['customer_name']) . ' - CMND: ' . htmlspecialchars($customer['id_card_number']) . ' - SĐT: ' . htmlspecialchars($customer['phone']) . '</p>';
                                                }
                                            } else {
                                                echo 'Không có thông tin khách hàng';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo number_format($row['total_price'], 0, ',', '.') . ' VND'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6">Không có đơn đặt tour nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
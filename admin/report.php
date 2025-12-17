<?php
require_once __DIR__ . '/../config.php';

ensure_session_started();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = db_mysqli();

// Fetch Monthly Revenue (12 months)
$monthlyRevenueQuery = "SELECT DATE_FORMAT(booking_date, '%Y-%m') AS month, SUM(total_price) AS revenue
                        FROM bookings
                        WHERE booking_status = 'complete'
                        GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
                        ORDER BY month DESC
                        LIMIT 12";
$monthlyRevenueResult = $conn->query($monthlyRevenueQuery);
$monthlyRevenueData = [];
while ($row = $monthlyRevenueResult->fetch_assoc()) {
    $monthlyRevenueData[] = $row;
}

// Fetch Most Booked Tours
$mostBookedToursQuery = "SELECT t.title, COUNT(b.booking_id) AS booking_count
                         FROM tours t
                         JOIN bookings b ON t.tour_id = b.tour_id
                         WHERE b.booking_status = 'complete'
                         GROUP BY t.title
                         ORDER BY booking_count DESC
                         LIMIT 5";
$mostBookedToursResult = $conn->query($mostBookedToursQuery);
$mostBookedTours = [];
while ($row = $mostBookedToursResult->fetch_assoc()) {
    $mostBookedTours[] = $row;
}

// Fetch Tours with Highest Revenue
$highestRevenueToursQuery = "SELECT t.title, SUM(b.total_price) AS revenue
                             FROM tours t
                             JOIN bookings b ON t.tour_id = b.tour_id
                             WHERE b.booking_status = 'complete'
                             GROUP BY t.title
                             ORDER BY revenue DESC
                             LIMIT 5";
$highestRevenueToursResult = $conn->query($highestRevenueToursQuery);
$highestRevenueTours = [];
while ($row = $highestRevenueToursResult->fetch_assoc()) {
    $highestRevenueTours[] = $row;
}

// Handle logout
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
    <title>Admin Dashboard - Báo cáo</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">

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
                <form method="post">
                    <button class="btn btn-logout" type="submit" name="logout">Đăng xuất</button>
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
                            <a class="nav-link active" href="report.php">Báo cáo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_accounts.php">Quản lý tài khoản</a>
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
                <h2>Báo cáo</h2>

                <!-- Doanh thu theo 12 tháng -->
                <div class="container mt-4">
                    <h3>Doanh thu theo 12 tháng gần nhất</h3>
                    <canvas id="monthly-revenue-chart"></canvas>
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
                                <?php foreach ($mostBookedTours as $tour): ?>
                                    <tr>
                                        <td><?php echo $tour['title']; ?></td>
                                        <td><?php echo $tour['booking_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
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
                                <?php foreach ($highestRevenueTours as $tour): ?>
                                    <tr>
                                        <td><?php echo $tour['title']; ?></td>
                                        <td><?php echo number_format($tour['revenue'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Prepare Monthly Revenue Data
        const monthlyRevenue = <?php echo json_encode($monthlyRevenueData); ?>;
        const months = monthlyRevenue.map(item => item.month);
        const revenues = monthlyRevenue.map(item => parseFloat(item.revenue));

        // Render Monthly Revenue Chart
        const ctx = document.getElementById('monthly-revenue-chart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Doanh thu',
                    data: revenues,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
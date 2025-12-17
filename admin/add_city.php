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

// Xử lý việc thêm thành phố mới
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_name = $_POST['city_name'];
    $region_id = $_POST['region_id'];

    // Kiểm tra các trường đầu vào
    if (empty($city_name) || empty($region_id)) {
        echo '<div class="alert alert-danger">Vui lòng điền đầy đủ thông tin.</div>';
    } else {
        // Thêm thành phố vào cơ sở dữ liệu
        $insert_sql = "INSERT INTO cities (name, region_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('si', $city_name, $region_id);

        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Thành phố đã được thêm thành công!</div>';
        } else {
            echo '<div class="alert alert-danger">Có lỗi xảy ra khi thêm thành phố. Vui lòng thử lại.</div>';
        }

        $stmt->close();
    }
}

// Truy vấn dữ liệu khu vực
$sql_regions = "SELECT * FROM regions";
$result_regions = $conn->query($sql_regions);

$regions = [];
if ($result_regions->num_rows > 0) {
    while ($row = $result_regions->fetch_assoc()) {
        $regions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Thành Phố - Admin Dashboard</title>
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
                            <a class="nav-link" href="manage_bookings.php">Quản lý đặt tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customer_support.php">Hỗ trợ khách hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="add_city.php">Thêm Thành Phố</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <h2>Thêm Thành Phố</h2>
                <form method="post" action="add_city.php">
                    <div class="form-group">
                        <label for="city_name">Tên Thành Phố</label>
                        <input type="text" class="form-control" id="city_name" name="city_name" placeholder="Nhập tên thành phố">
                    </div>
                    <div class="form-group">
                        <label for="region_id">Khu vực</label>
                        <select class="form-control" id="region_id" name="region_id">
                            <option value="">Chọn khu vực</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo htmlspecialchars($region['region_id']); ?>">
                                    <?php echo htmlspecialchars($region['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Thêm Thành Phố</button>
                </form>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
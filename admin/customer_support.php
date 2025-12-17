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

// Xử lý gửi phản hồi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_id']) && isset($_POST['response'])) {
    $message_id = $_POST['message_id'];
    $response = $_POST['response'];

    // Cập nhật phản hồi và di chuyển vấn đề vào phần đã giải quyết
    $update_sql = "UPDATE messages SET respond = ? WHERE message_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $response, $message_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Phản hồi đã được gửi thành công!</div>';
    } else {
        echo '<div class="alert alert-danger">Có lỗi xảy ra khi gửi phản hồi. Vui lòng thử lại.</div>';
    }

    $stmt->close();
}

// Truy vấn dữ liệu hỗ trợ khách hàng chưa giải quyết
$sql_pending = "SELECT m.message_id, u.username, m.content
                FROM messages m
                JOIN users u ON m.user_id = u.user_id
                WHERE m.respond IS NULL";
$result_pending = $conn->query($sql_pending);

$support_messages_pending = [];
if ($result_pending->num_rows > 0) {
    while ($row = $result_pending->fetch_assoc()) {
        $support_messages_pending[] = $row;
    }
}

// Truy vấn dữ liệu hỗ trợ khách hàng đã giải quyết
$sql_resolved = "SELECT m.message_id, u.username, m.content, m.respond
                 FROM messages m
                 JOIN users u ON m.user_id = u.user_id
                 WHERE m.respond IS NOT NULL";
$result_resolved = $conn->query($sql_resolved);

$support_messages_resolved = [];
if ($result_resolved->num_rows > 0) {
    while ($row = $result_resolved->fetch_assoc()) {
        $support_messages_resolved[] = $row;
    }
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
    <title>Admin Dashboard - Hỗ trợ khách hàn </title>
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
                            <a class="nav-link active" href="customer_support.php">Hỗ trợ khách hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_city.php">Thêm Thành Phố</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <h2>Hỗ trợ Khách Hàng</h2>

                <!-- Table of Unresolved Support Messages -->
                <h3>Các vấn đề chưa được giải quyết</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class='thead-dark'>
                            <tr>
                                <th>Tên người dùng</th>
                                <th>Vấn đề</th>
                                <th>Phản hồi</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($support_messages_pending)) : ?>
                                <?php foreach ($support_messages_pending as $message) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['username']); ?></td>
                                        <td><?php echo htmlspecialchars($message['content']); ?></td>
                                        <td>
                                            <form method="post" class="mb-0">
                                                <textarea name="response" class="form-control" rows="2" placeholder="Nhập phản hồi"></textarea>
                                                <input type="hidden" name="message_id" value="<?php echo htmlspecialchars($message['message_id']); ?>">
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" type="submit">Gửi phản hồi</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4">Không có yêu cầu hỗ trợ nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table of Resolved Support Messages -->
                <h3>Các vấn đề đã được giải quyết</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class='thead-dark'>
                            <tr>
                                <th>Tên người dùng</th>
                                <th>Vấn đề</th>
                                <th>Phản hồi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($support_messages_resolved)) : ?>
                                <?php foreach ($support_messages_resolved as $message) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['username']); ?></td>
                                        <td><?php echo htmlspecialchars($message['content']); ?></td>
                                        <td><?php echo htmlspecialchars($message['respond']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3">Không có yêu cầu hỗ trợ nào đã được giải quyết</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
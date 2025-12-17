<?php
session_start();
include 'fnCSDL.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
  exit();
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$conn = ConnectDB();
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
  // Xử lý khi không tìm thấy người dùng trong cơ sở dữ liệu
  header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu không tìm thấy thông tin người dùng
  exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông tin tài khoản</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/profile.css">
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
              <a class="nav-link active" href="tour_history.php">Lịch sử đặt tour</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="support.php">Liên hệ hỗ trợ</a>
            </li>
          </ul>
          <form class="d-flex" role="search">
            <p class="d-flex justify-content-center m-2">
              <a href="profile.php" class="link-offset-2 link-underline link-underline-opacity-0"><?= htmlspecialchars($user['username']); ?></a>
            </p>
            <button class="btn btn-outline-success" type="button" onclick="location.href='logout.php';">Đăng xuất</button>
          </form>
        </div>
      </div>
    </nav>
  </header>

  <div class="content d-flex flex-column ">
    <div class="container-fluid profile-container">
      <h2>Thông Tin Người Dùng</h2>
      <form method="POST" action="edit_profile.php">
        <div class="mb-3">
          <label for="username" class="form-label">Tên đăng nhập</label>
          <input type="text" class="form-control-plaintext" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" readonly>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>">
        </div>
        <div class="mb-3">
          <label for="phone" class="form-label">Số điện thoại</label>
          <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>">
        </div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">Chỉnh sửa thông tin</button>
          <button type="button" class="btn btn-danger" onclick="if(confirm('Bạn có chắc chắn muốn xóa tài khoản không?')) { location.href='delete_account.php'; }">Xóa tài khoản</button>
        </div>
      </form>
    </div>
  </div>

  <footer class="bg-black text-center text-light">
    <p class="mb-0 p-3">Copyright © 2022 My Website. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
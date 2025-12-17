<?php
require_once __DIR__ . '/../config.php';

ensure_session_started();

// kiểm tra đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = db_mysqli();

$uploadDir = __DIR__ . '/../img';
$uploadBaseUrl = '/DuAnCNTT/img/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Biến lưu trữ thông báo lỗi
$error_message = '';

// Upload ảnh an toàn, trả về URL công khai hoặc null nếu lỗi
function handleImageUpload(array $file, string $uploadDir, string $uploadBaseUrl, string &$error_message): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Lỗi upload ảnh.';
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    if ($mime === false || !in_array($mime, $allowedTypes, true)) {
        $error_message = 'Chỉ chấp nhận file ảnh (jpg, png, gif, webp).';
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safeName = uniqid('tour_', true) . '.' . $extension;
    $targetPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $error_message = 'Lỗi khi tải ảnh.';
        return null;
    }

    return $uploadBaseUrl . $safeName;
}

// Xử lý thêm mới tour
if (isset($_POST['addTour'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $itinerary = $_POST['itinerary'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $city_id = $_POST['city_id'];

    // Kiểm tra tour có cùng tên và lịch trình chưa
    $check_sql = "SELECT * FROM tours WHERE title = ? OR itinerary = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $title, $itinerary);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if ($result_check->num_rows > 0) {
        $error_message = "Tour có cùng tên hoặc lịch trình đã tồn tại!";
    } else {
        $img_url = handleImageUpload($_FILES['url_img'], $uploadDir, $uploadBaseUrl, $error_message);

        // Thêm thông tin tour nếu không có lỗi
        if (empty($error_message) && $img_url !== null) {
            $sql = "INSERT INTO tours (title, description, url_img, itinerary, duration, price, city_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdis", $title, $description, $img_url, $itinerary, $duration, $price, $city_id);
            if ($stmt->execute()) {
                echo "<script>alert('Thêm tour thành công!'); window.location.href='manage_tours.php';</script>";
            } else {
                $error_message = "Lỗi: " . $conn->error;
            }
        }
    }
}

// Xử lý cập nhật tour
if (isset($_POST['editTour'])) {
    $tour_id = $_POST['tour_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $itinerary = $_POST['itinerary'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $city_id = $_POST['city_id'];

    // Kiểm tra tour có cùng tên và lịch trình chưa
    $check_sql = "SELECT * FROM tours WHERE (title = ? OR itinerary = ?) AND tour_id != ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ssi", $title, $itinerary, $tour_id);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if ($result_check->num_rows > 0) {
        // Tour trùng tên và lịch trình
        $error_message = "Tour có cùng tên hoặc lịch trình đã tồn tại!";
    } else {
        // Cập nhật thông tin tour
        $sql = "UPDATE tours SET title=?, description=?, itinerary=?, duration=?, price=?, city_id=? WHERE tour_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdidi", $title, $description, $itinerary, $duration, $price, $city_id, $tour_id);
        if ($stmt->execute()) {
            // Xử lý upload ảnh nếu có
            if (!empty($_FILES['url_img']['name'])) {
                $img_url = handleImageUpload($_FILES['url_img'], $uploadDir, $uploadBaseUrl, $error_message);
                if ($img_url !== null && empty($error_message)) {
                    $sql = "UPDATE tours SET url_img=? WHERE tour_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $img_url, $tour_id);
                    if ($stmt->execute()) {
                        echo "<script>alert('Cập nhật ảnh tour thành công!'); window.location.href='manage_tours.php';</script>";
                    } else {
                        $error_message = "Lỗi: " . $conn->error;
                    }
                }
            }
        } else {
            $error_message = "Lỗi: " . $conn->error;
        }
    }
}

// Xử lý xóa tour
if (isset($_POST['deleteTour'])) {
    $tour_id = $_POST['tour_id'];

    // Kiểm tra xem tour có được đặt hay không
    $sql_check_booking = "SELECT COUNT(*) FROM bookings WHERE tour_id = ?";
    $stmt_check = $conn->prepare($sql_check_booking);
    $stmt_check->bind_param("i", $tour_id);
    $stmt_check->execute();
    $stmt_check->bind_result($booking_count);
    $stmt_check->fetch();
    $stmt_check->close();

    // Nếu tour đang được đặt, không cho phép xóa
    if ($booking_count > 0) {
        echo "<script>alert('Không thể xóa tour này vì đang có khách hàng đặt tour!'); window.location.href='manage_tours.php';</script>";
    } else {
        // Xóa tour nếu không có bản ghi đặt tour
        $sql_delete = "DELETE FROM tours WHERE tour_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $tour_id);
        if ($stmt_delete->execute()) {
            echo "<script>alert('Xóa tour thành công!'); window.location.href='manage_tours.php';</script>";
        } else {
            echo "Lỗi: " . $conn->error;
        }
        $stmt_delete->close();
    }
}

// Truy vấn danh sách tour
$sql = "SELECT t.tour_id, t.title, t.description, t.url_img, t.itinerary, t.duration, t.price, c.name as city_name, r.name as region_name
        FROM tours t
        LEFT JOIN cities c ON t.city_id = c.city_id
        LEFT JOIN regions r ON c.region_id = r.region_id";
$result = $conn->query($sql);

// đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quản lý Tour</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        #tour-form {
            display: none;
        }
    </style>
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
                            <a class="nav-link active" href="manage_tours.php">Quản lý tour</a>
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

            <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <h2>Quản lý Tour</h2>
                <!-- Thông báo lỗi nếu có -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger text-center">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                <button id="show-form-btn" class="btn btn-primary mb-3">Thêm Tour Mới</button>

                <!-- Form Thêm Tour -->
                <div id="tour-form-container">
                    <form id="tour-form" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="tour_id" id="tour_id"> <!-- Dùng để lưu ID tour khi sửa -->
                        <input type="text" name="title" id="title" placeholder="Tên tour" class="form-control" required><br>
                        <textarea name="description" id="description" placeholder="Mô tả" class="form-control" required></textarea><br>
                        <input type="file" name="url_img" id="url_img" class="form-control"><br>
                        <textarea name="itinerary" id="itinerary" placeholder="Nhập lịch trình" class="form-control" required></textarea><br>
                        <input type="number" name="duration" id="duration" placeholder="Số ngày" class="form-control" required><br>
                        <input type="number" name="price" id="price" placeholder="Giá" class="form-control" required><br>
                        <select name="city_id" id="city_id" class="form-control" required>
                            <option value="">Chọn thành phố</option>
                            <?php
                            $city_sql = "SELECT * FROM cities";
                            $city_result = $conn->query($city_sql);
                            while ($city_row = $city_result->fetch_assoc()) {
                                echo "<option value='{$city_row['city_id']}'>{$city_row['name']}</option>";
                            }
                            ?>
                        </select><br>
                        <button type="submit" name="addTour" id="form-btn" class="btn btn-primary">Thêm Tour</button>
                    </form>
                </div>

                <!-- Bảng hiển thị tour -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Tên tour</th>
                                <th>Mô tả</th>
                                <th>Ảnh</th>
                                <th>Lịch trình</th>
                                <th>Thời gian</th>
                                <th>Giá</th>
                                <th>Thành phố</th>
                                <th>Khu vực</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td style='text-align: left'>{$row['title']}</td>";
                                    echo "<td style='text-align: left'>{$row['description']}</td>";
                                    echo "<td><img src='{$row['url_img']}' width='100'></td>";

                                    // Hiển thị lịch trình dạng danh sách
                                    echo "<td><ul>";
                                    $itinerary_items = explode("\n", $row['itinerary']);
                                    foreach ($itinerary_items as $item) {
                                        echo "<li style='list-style-type:none; text-align: left'>" . htmlspecialchars($item) . "</li>";
                                    }
                                    echo "</ul></td>";

                                    echo "<td>{$row['duration']} ngày</td>";
                                    echo "<td>" . number_format($row['price'], 0, ',', '.') . " VND</td>";
                                    echo "<td>{$row['city_name']}</td>";
                                    echo "<td>{$row['region_name']}</td>";
                                    echo "<td>";
                                    echo "<button class='btn btn-warning edit-btn' data-id='{$row['tour_id']}'>Sửa</button>";
                                    echo "<form method='POST' class='d-inline-block'>";
                                    echo "<input type='hidden' name='tour_id' value='{$row['tour_id']}'>";
                                    echo "<button type='submit' name='deleteTour' class='btn btn-danger'>Xóa</button>";
                                    echo "</form>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>

            <!-- Xử lý JavaScript -->
            <script>
                // Hiển thị/ẩn form khi nhấn nút
                document.getElementById('show-form-btn').addEventListener('click', function() {
                    const form = document.getElementById('tour-form');
                    if (form.style.display === 'none') {
                        form.style.display = 'block';
                    } else {
                        form.style.display = 'none';
                    }
                });

                // Xử lý sự kiện nút Xóa
                $(document).on('click', '.delete-btn', function() {
                    const tourId = $(this).attr('data-id');
                    if (confirm('Bạn có chắc chắn muốn xóa tour này?')) {
                        $.post('manage_tours.php', {
                            deleteTour: true,
                            tour_id: tourId
                        }, function(response) {
                            location.reload();
                        });
                    }
                });

                // Xử lý sự kiện nút Sửa
                $(document).on('click', '.edit-btn', function() {
                    const tourId = $(this).attr('data-id');
                    $.get('gettour.php', {
                        tour_id: tourId
                    }, function(response) {
                        const tour = JSON.parse(response);
                        $('#tour_id').val(tour.tour_id);
                        $('#title').val(tour.title);
                        $('#description').val(tour.description);
                        $('#itinerary').val(tour.itinerary);
                        $('#duration').val(tour.duration);
                        $('#price').val(tour.price);
                        $('#city_id').val(tour.city_id);
                        $('#tour-form').show();
                        $('#form-btn').attr('name', 'editTour').text('Cập nhật Tour');
                    });
                });
            </script>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/fnCSDL.php';

ensure_session_started();

// Yêu cầu đăng nhập mới được thanh toán
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

$tour_id = isset($_POST['tour_id']) ? (int) $_POST['tour_id'] : 0;
$departure_date = $_POST['departure_date'] ?? '';
$total_price = isset($_POST['total_price']) ? (float) $_POST['total_price'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
$payment_method = $_POST['paymentMethod'] ?? '';
$payment_date = date('Y-m-d H:i:s');
$customers = $_POST['customers'] ?? [];

if ($tour_id <= 0 || $departure_date === '' || $quantity <= 0 || $total_price <= 0 || empty($customers)) {
    $_SESSION['flash_error'] = 'Thiếu thông tin đặt tour, vui lòng thử lại.';
    header('Location: tour_details.php?id=' . $tour_id);
    exit;
}

// Chỉ chấp nhận các phương thức đã định nghĩa
$allowedMethods = ['credit_card', 'paypal', 'bank_transfer'];
if (!in_array($payment_method, $allowedMethods, true)) {
    $payment_method = 'bank_transfer';
}

try {
    $conn = ConnectDB();
    $conn->beginTransaction();

    $insertBooking = $conn->prepare('INSERT INTO bookings (user_id, tour_id, booking_date, departure_date, quantity, total_price, booking_status) VALUES (?, ?, NOW(), ?, ?, ?, "pending")');
    $insertBooking->execute([$_SESSION['user_id'], $tour_id, $departure_date, $quantity, $total_price]);
    $booking_id = (int) $conn->lastInsertId();

    $insertCustomer = $conn->prepare('INSERT INTO customers (first_name, last_name, id_card_number, phone, booking_id) VALUES (?, ?, ?, ?, ?)');
    $insertCustomerTour = $conn->prepare('INSERT INTO customertours (customer_id, tour_id, departure_date) VALUES (?, ?, ?)');

    foreach ($customers as $customer) {
        $firstName = trim($customer['first_name'] ?? '');
        $lastName = trim($customer['last_name'] ?? '');
        $idCard = trim($customer['id_card_number'] ?? '');
        $phone = trim($customer['phone'] ?? '');

        $insertCustomer->execute([$firstName, $lastName, $idCard, $phone, $booking_id]);
        $customer_id = (int) $conn->lastInsertId();
        $insertCustomerTour->execute([$customer_id, $tour_id, $departure_date]);
    }

    $insertPayment = $conn->prepare('INSERT INTO payments (booking_id, amount, payment_method, payment_date) VALUES (?, ?, ?, ?)');
    $insertPayment->execute([$booking_id, $total_price, $payment_method, $payment_date]);

    $conn->commit();
    header('Location: confirmation.php');
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Thanh toán thất bại: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Thanh toán thất bại, vui lòng thử lại.';
    header('Location: tour_details.php?id=' . $tour_id);
    exit;
}

<?php
require_once __DIR__ . '/../config.php';

// Kết nối PDO dùng cấu hình chung
function ConnectDB(): PDO
{
    return db_pdo();
}

// Thực thi INSERT/UPDATE/DELETE
function executeQuery(string $sql, array $params = []): bool
{
    try {
        $conn = ConnectDB();
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('Lỗi truy vấn: ' . $e->getMessage());
        return false;
    }
}

// Lấy dữ liệu SELECT
function getResults(string $sql, array $params = []): array
{
    try {
        $conn = ConnectDB();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Lỗi truy vấn: ' . $e->getMessage());
        return [];
    }
}

// Kiểm tra thông tin đăng nhập người dùng (tự động nâng cấp mật khẩu cũ sang hash)
function CheckUser(string $username, string $password): ?array
{
    $conn = ConnectDB();
    $sql = "SELECT * FROM users WHERE username = ? AND user_type = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);

    if ($stmt->rowCount() === 0) {
        return null;
    }

    $user = $stmt->fetch();

    if (verifyPasswordAndUpgrade($password, $user['password'], (int) $user['user_id'], $conn)) {
        return $user;
    }

    return null;
}

// Cho phép đăng nhập với mật khẩu hash; nếu còn lưu dạng plain thì nâng cấp lên hash sau lần đăng nhập đầu
function verifyPasswordAndUpgrade(string $plainPassword, string $storedPassword, int $userId, PDO $conn): bool
{
    if (password_verify($plainPassword, $storedPassword)) {
        return true;
    }

    // Fallback: dữ liệu cũ lưu plain-text
    if (hash_equals($storedPassword, $plainPassword)) {
        $newHash = password_hash($plainPassword, PASSWORD_BCRYPT);
        $update = $conn->prepare('UPDATE users SET password = ? WHERE user_id = ?');
        $update->execute([$newHash, $userId]);
        return true;
    }

    return false;
}

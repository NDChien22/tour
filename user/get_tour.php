<?php
include_once 'fnCSDL.php';

function getAllTours()
{
    $conn = ConnectDB();

    // Lấy tất cả các tour từ cơ sở dữ liệu
    $sql = "SELECT * FROM tours";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tours = getAllTours();

// Hàm lấy thông tin tour từ cơ sở dữ liệu

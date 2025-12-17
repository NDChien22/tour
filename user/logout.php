<?php 
session_start();
session_unset(); // Loại bỏ toàn bộ biến session
session_destroy();

header("Location: index.php");
exit();
?>
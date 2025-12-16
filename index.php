<?php
session_start();
// 1. Nhúng file cấu hình
require_once 'config/database.php';
require_once 'router/Router.php';

// 2. Cấu hình CORS (Cho phép Angular port 4200 gọi vào)
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true"); // Dòng này cực quan trọng với Session
// 1. Thiết lập thông báo lỗi (Bật lên để dễ debug khi đang code)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. Kết nối Database
$database = new Database();
$conn = $database->connect(); // Lấy biến PDO Connection

// 4. Lấy đường dẫn (URI) chuẩn hơn cho XAMPP
// Ví dụ: http://localhost/shoptk/users/1 -> uri = 'users/1'
$uri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']); 
$uri = str_replace($scriptName, '', $uri); // Loại bỏ phần thư mục gốc
$uri = trim($uri, '/'); // Loại bỏ dấu / thừa

// 5. Gọi Router (Truyền $conn vào)
$router = new Router($conn);
$router->handle($uri);
?>
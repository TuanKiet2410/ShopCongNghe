<?php

// --- BẮT ĐẦU ĐOẠN CODE DEBUG ---
// Code này sẽ ghi lại mọi dữ liệu Angular gửi lên vào file 'debug_log.txt'

// 1. Lấy dữ liệu thô từ body request (JSON)
$rawInput = file_get_contents("php://input");

// 2. Tạo nội dung log
$logContent = "----------------------------------------\n";
$logContent .= "Thời gian: " . date('Y-m-d H:i:s') . "\n";
$logContent .= "URL: " . $_SERVER['REQUEST_URI'] . "\n";
$logContent .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logContent .= "DỮ LIỆU GỬI LÊN (PAYLOAD):\n";
$logContent .= $rawInput . "\n"; // Đây là cái bạn cần xem nhất
$logContent .= "----------------------------------------\n\n";

// 3. Ghi vào file debug_log.txt (Nằm cùng thư mục với index.php)
file_put_contents('debug_log.txt', $logContent, FILE_APPEND);//FILE_APPEND sử dụng de ghi thêm với file, khong ghi trung lap
// --- KẾT THÚC ĐOẠN CODE DEBUG ---
// 1. Nhúng file cấu hình
require_once 'config/database.php';
require_once 'router/Router.php';
require_once 'controllers/ChatController.php';

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
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
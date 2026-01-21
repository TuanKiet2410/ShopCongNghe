<?php
// routes/Router.php

// Import các Controllers
require_once __DIR__ . './../controllers/UserController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../controllers/EmployeeController.php';
require_once __DIR__ . '/../controllers/InvoiceController.php';
require_once __DIR__ . '/../controllers/VoucherController.php';
require_once __DIR__ . '/../controllers/BannerController.php';
require_once __DIR__ . '/../controllers/Invoice_detailController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AuthMiddleware.php';
class Router
{
    private $db;
    private $auth;
    private $authMiddleware;
    public function __construct($db)
    {
        $this->db = $db;
        $this->auth = new AuthController($this->db);
        $this->authMiddleware = new AuthMiddleware($this->db);
    }
    public function handle($url)
    {

        // ❗ CẮT ?page=1
        $path = parse_url($url, PHP_URL_PATH);

        // products hoặc products/5
        $parts = explode('/', trim($path, '/'));

        $resource = $parts[0] ?? null;
        $id = $parts[1] ?? null;

        if($resource=='login') {
            $this->auth->login();
            return;
        }
        if($resource=='logout') {
            $this->auth->logout();
            return;
        }
        if($resource=='signup') {
            $controller = new UserController($this->db);
            $controller->processRequest($id);
            return;
        }


        switch ($resource) {
            case 'users':
                $this->authMiddleware->isAuthenticatedAdmin();
                    $controller = new UserController($this->db);
                    $controller->processRequest($id);
                break;
            case 'products':
                $controller = new ProductController($this->db);
                $controller->processRequest($id);
                break;
            case 'customers': // Thêm case cho sản phẩm
                $controller = new CustomerController($this->db);
                $controller->processRequest($id);
                break;
            case 'employees':
                $controller = new EmployeeController($this->db);
                $controller->processRequest($id);
                break;
            case 'invoices': // Thêm case cho sản phẩm
                $controller = new InvoiceController($this->db);
                $controller->processRequest($id);
                break;
            case 'vouchers': // Thêm case cho sản phẩm
                $controller = new VoucherController($this->db);
                $controller->processRequest($id);
                break;
            case 'banners': // Thêm case cho sản phẩm
                $controller = new BannerController($this->db);
                $controller->processRequest($id);
                break;
            case 'invoice_detail': // Thêm case cho sản phẩm
                $controller = new InvoiceDetailController($this->db);
                $controller->processRequest($id);
                break;
            case 'chat':
                $controller = new ChatController($this->db);
                $controller->ask();
                break;
            default:
                http_response_code(404);
                echo json_encode(["message" => "Đường dẫn ngoài phạm vi"]);
                break;
        }
    }
}

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
class Router {
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function handle($url){
        $parts = explode('/',$url);
        $resource = $parts[0] ?? null;
        $id = $parts[1] ?? null;
        //đều hướng bằng đều kiện switch
        switch($resource){
            case 'users':
                $controller = new UserController($this->db);
                $controller->processRequest($id);
                break;
            case 'products': // Thêm case cho sản phẩm
                $controller = new ProductController($this->db);
                $controller->processRequest($id);
                break;
            case 'customers': // Thêm case cho sản phẩm
                $controller = new CustomerController($this->db);
                $controller->processRequest($id);
                break;
            case 'employees': // Thêm case cho sản phẩm
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
            default:
                http_response_code(404);
                echo json_encode(["message" => "Đương dẫn ngoài phạm vi"]);
                break;
        }
    }

}
?>
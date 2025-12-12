<?php
// Nhúng Model Product
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    private $productModel;

    // Nhận kết nối DB từ Router truyền sang
    public function __construct($db) {
        $this->productModel = new Product($db);
    }

    // Hàm điều hướng chính
    public function processRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getProduct($id); // Lấy 1 sản phẩm
                } else {
                    $this->getAllProducts(); // Lấy tất cả
                }
                break;

            case 'POST':
                $this->createProduct();
                break;

            case 'PUT': // Lưu ý: Một số host không nhận PUT, có thể dùng POST
                $this->updateProduct($id);
                break;

            case 'DELETE':
                $this->deleteProduct($id);
                break;

            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode(["message" => "Phương thức không được hỗ trợ"]);
                break;
        }
    }

    // --- CÁC HÀM XỬ LÝ LOGIC CHI TIẾT ---

    // 1. Lấy danh sách sản phẩm
    private function getAllProducts() {
        $stmt = $this->productModel->getAll();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $products_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $product_item = [
                    'id' => $id,
                    'name' => $name,
                    'image' => $image,
                    'price' => (float)$price, // Ép kiểu số thực
                    'stock' => (int)$stock,   // --- THÊM: Stock ---
                    'category' => $category,
                    'brand' => $brand,
                    // 'created_at' => $created_at // Bỏ nếu bảng products không có cột này, hoặc thêm vào SQL nếu có
                ];
                array_push($products_arr, $product_item);
            }
            http_response_code(200);
            echo json_encode($products_arr);
        } else {
            http_response_code(200);
            echo json_encode([]); 
        }
    }

    // 2. Lấy 1 sản phẩm theo ID
    private function getProduct($id) {
        $this->productModel->id = $id;

        if ($this->productModel->getById()) {
            $product_item = [
                'id' => $this->productModel->id,
                'name' => $this->productModel->name,
                'image' => $this->productModel->image,
                'price' => (float)$this->productModel->price,
                'stock' => (int)$this->productModel->stock, // --- THÊM: Stock ---
                'description' => $this->productModel->description,
                'category' => $this->productModel->category,
                'color' => $this->productModel->color,
                'brand' => $this->productModel->brand
            ];
            http_response_code(200);
            echo json_encode($product_item);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Không tìm thấy sản phẩm"]);
        }
    }

    // 3. Tạo sản phẩm mới
    private function createProduct() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->name) && !empty($data->price)) {
            // Gán dữ liệu
            $this->productModel->name = $data->name;
            $this->productModel->price = $data->price;
            
            // Các trường bổ sung
            $this->productModel->image = $data->image ?? null;
            $this->productModel->description = $data->description ?? '';
            $this->productModel->category = $data->category ?? '';
            $this->productModel->color = $data->color ?? '';
            $this->productModel->brand = $data->brand ?? '';
            
            // --- THÊM: Stock (Mặc định là 0 nếu không nhập) ---
            $this->productModel->stock = $data->stock ?? 0;

            if ($this->productModel->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Tạo sản phẩm thành công"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Lỗi hệ thống"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu tên hoặc giá sản phẩm"]);
        }
    }

    // 4. Cập nhật sản phẩm
    private function updateProduct($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu ID sản phẩm"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        $this->productModel->id = $id;
        
        // Gán dữ liệu mới
        $this->productModel->name = $data->name;
        $this->productModel->price = $data->price;
        $this->productModel->image = $data->image ?? null;
        $this->productModel->description = $data->description ?? '';
        $this->productModel->category = $data->category ?? '';
        $this->productModel->color = $data->color ?? '';
        $this->productModel->brand = $data->brand ?? '';
        
        // --- THÊM: Update Stock ---
        $this->productModel->stock = $data->stock ?? 0;

        if ($this->productModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Cập nhật thành công"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Cập nhật thất bại"]);
        }
    }

    // 5. Xóa sản phẩm
    private function deleteProduct($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu ID sản phẩm"]);
            return;
        }

        $this->productModel->id = $id;

        if ($this->productModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Xóa thành công"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Xóa thất bại"]);
        }
    }
}
?>
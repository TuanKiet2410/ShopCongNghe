<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../controllers/AuthMiddleware.php';

class ProductController
{
    private AuthMiddleware $authMiddleware;
    private Product $productModel;

    public function __construct($db)
    {
        $this->productModel = new Product($db);
        $this->authMiddleware = new AuthMiddleware($db);
    }

    // ==========================
    // ROUTER
    // ==========================
    public function processRequest($id)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getProduct($id);
                } else {
                    $this->XuLyPhanTrang();
                }
                break;

            case 'POST':
                $this->createProduct();
                break;

            case 'PUT':
                $this->updateProduct($id);
                break;

            case 'DELETE':
                $this->deleteProduct($id);
                break;

            default:
                http_response_code(405);
                echo json_encode(["message" => "Method Not Allowed"]);
        }
    }

    // ==========================
    // GET ONE
    // ==========================
    private function getProduct($id)
    {
        $this->productModel->id = (int)$id;

        if ($this->productModel->getById()) {
            http_response_code(200);
            echo json_encode([
                "id" => $this->productModel->id,
                "name" => $this->productModel->name,
                "price" => (int)$this->productModel->price,
                "image" => $this->productModel->image,
                "description" => $this->productModel->description,
                "category" => $this->productModel->type,
                "color" => $this->productModel->color,
                "brand" => $this->productModel->brand_name,
                "stock" => (int)$this->productModel->stock
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Không tìm thấy sản phẩm"]);
        }
    }

    // ==========================
    // CREATE
    // ==========================
    private function createProduct()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->name) || empty($data->price)) {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu name hoặc price"]);
            return;
        }

        $this->productModel->name = $data->name;
        $this->productModel->price = (int)$data->price;
        $this->productModel->image = $data->image ?? null;
        $this->productModel->description = $data->description ?? '';
        $this->productModel->type = $data->category ?? null;
        $this->productModel->color = $data->color ?? null;
        $this->productModel->id_brand = 1;
        $this->productModel->stock = $data->stock ?? 0;

        if ($this->productModel->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Tạo sản phẩm thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Lỗi tạo sản phẩm"]);
        }
    }

    // ==========================
    // UPDATE
    // ==========================
    private function updateProduct($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu ID"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        $this->productModel->id = (int)$id;
        $this->productModel->name = $data->name;
        $this->productModel->price = (int)$data->price;
        $this->productModel->image = $data->image ?? null;
        $this->productModel->description = $data->description ?? '';
        $this->productModel->type = $data->category ?? null;
        $this->productModel->color = $data->color ?? null;
        $this->productModel->brand_name = $data->brand ?? null;
        $this->productModel->stock = $data->stock ?? 0;

        if ($this->productModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Cập nhật thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Cập nhật thất bại"]);
        }
    }

    // ==========================
    // DELETE
    // ==========================
    private function deleteProduct($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu ID"]);
            return;
        }

        $this->productModel->id = (int)$id;

        if ($this->productModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Xóa thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Xóa thất bại"]);
        }
    }

    // ==========================
    // PAGINATION + SEARCH + FILTER
    // ==========================
    private function XuLyPhanTrang()
    {
        // --- AUTH ---
        $this->authMiddleware->isAuthenticated();

        // Page
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 6;
        $offset = ($page - 1) * $limit;

        // Query params
        $search     = trim($_GET['search'] ?? '');
        $category   = trim($_GET['category'] ?? '');
        $brand      = trim($_GET['brand'] ?? '');
        $priceRange = trim($_GET['priceRange'] ?? '');

        // Gán vào model (MODEL sẽ tự build WHERE)
        $this->productModel->name       = $search !== '' ? $search : null;
        $this->productModel->type   = ($category && $category !== 'Tất cả') ? $category : null;
        $this->productModel->brand_name      = ($brand && $brand !== 'Tất cả') ? $brand : null;
        $this->productModel->priceRange = $priceRange !== '' ? $priceRange : null;

        // Query
        $stmt = $this->productModel->filter($offset, $limit);
        $totalItems = $this->productModel->countFilterProducts();
        $totalPages = $totalItems > 0 ? ceil($totalItems / $limit) : 0;

        // Data
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = [
                "id" => $row['id'],
                "name" => $row['name'],
                "price" => (int)$row['price'],
                "image" => $row['image'],
                "description" => $row['description'],
                "category" => $row['type'],
                "brand" => $row['brand_name'],
                "stock" => (int)$row['stock']
            ];
        }

        echo json_encode([
            "status" => "success",
            "pagination" => [
                "current_page" => $page,
                "limit" => $limit,
                "total_pages" => $totalPages,
                "total_items" => $totalItems
            ],
            "data" => $products
        ]);
    }
}

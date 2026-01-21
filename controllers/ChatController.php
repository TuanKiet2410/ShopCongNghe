<?php
require_once './models/Product.php';
require_once './models/Voucher.php';

class ChatController {
    // ⚠️ Dán Key Groq của bạn vào đây (bắt đầu bằng gsk_...)
    private $apiKey = ''; 
    private $productModel;
    private $voucherModel;

    public function __construct($db) {
        $this->productModel = new Product($db);
        $this->voucherModel = new Voucher($db);
    }

public function ask() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $userMessage = isset($input['message']) ? $input['message'] : '';

            if (empty($userMessage)) {
                echo json_encode(['reply' => 'Bạn chưa nhập nội dung.']);
                exit;
            }

            // 1. Lấy dữ liệu dịch vụ
            $products = $this->productModel->getAll();
            $voucher= $this->voucherModel->getAll();
            // --- 2. DẠY CON AI Ở ĐÂY (SYSTEM PROMPT) ---
            
            // Đặt tên cho nó và chủ nhân
            $botName = "IPhone 17"; // Tên con AI
            $ownerName = " Kiệt Mobile"; // Tên bạn (Chủ quán)
            
            // Xây dựng kịch bản (Prompt)
            $systemPrompt = "Bạn tên là $botName, một trợ lý ảo cực kỳ vui tính và am hiểu về tóc nam tại MobileShop.\n";
            $systemPrompt .= "Chủ nhân của bạn (và là chủ tiệm) là $ownerName .\n";
            $systemPrompt .= "Dưới đây là bảng giá sản phẩm điện thoại hot hòn họt nhất:\n";
            
            foreach ($products as $s) {
                $systemPrompt .= "- " . $s['name'] . ": " . number_format($s['price']) . " VND.\n";
            }
            
            $systemPrompt .= "Dưới đây là một số voucher mới nhất bên em ạ:\n";
            foreach ($voucher as $v) {
                $systemPrompt .= " một số voucher ưu đãi lên đến  " . ": " .$v["description"]. "\n";
            }
            
            $systemPrompt .= "\nQuy tắc trả lời:\n";
            $systemPrompt .= "1. Luôn xưng hô là 'em' và gọi khách là 'anh' hoặc 'đại ca' nếu khách nữ thì gọi là 'đại tỷ' hoặc 'công chúa'.\n";
            $systemPrompt .= "2. Nếu khách hỏi 'Bạn là ai?', hãy giới thiệu tên tên chủ nhân $ownerName một cách tự hào.\n";
            $systemPrompt .= "3. Chỉ trả lời ngắn gọn, hài hước, tập trung vào tư vấn giá cả điện thoại, laptop, phụ kiện.\n";
            $systemPrompt .= "4. Nếu khách khen chủ quán, hãy hùa theo khen chủ nhân đẹp trai.\n";
            $systemPrompt .= "5. BẮT BUỘC sử dụng nhiều Icon/Emoji (ví dụ: 👍, 😎, 🔥, 💪, ✨) và 'hì hì', 'hí hí' trong câu trả lời để tạo cảm giác thân thiện, sôi động, dễ thương.\n";
            // 3. Gọi Groq (Truyền kịch bản này vào)
            $reply = $this->callGroq($systemPrompt, $userMessage);
            
            ob_clean(); // Xoá mọi output thừa (nếu có)
            echo json_encode(['reply' => $reply]);
            exit; 
        }
    }

private function callGroq($systemPrompt, $userMessage) {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        $data = [
            // ⚠️ CẬP NHẬT: Dùng model mới nhất hiện nay của Groq
            'model' => 'llama-3.3-70b-versatile', 
            
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],// Kịch bản dạy con AI
                ['role' => 'user', 'content' => $userMessage]// Câu hỏi từ người dùng
            ],
            // Nhiệt độ 0.5 để câu trả lời ổn định, đúng trọng tâm hơn
            'temperature' => 0.5, 
            'max_tokens' => 1024,// Tăng giới hạn token để có câu trả lời dài hơn
            'top_p' => 1,
            'stream' => false,
            'stop' => null
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//curl_setopt() → cấu hình
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey // Nhớ kiểm tra key gsk_...
        ]);

        // Tắt SSL cho Localhost
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// Tắt kiểm tra cơ sở SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// Tắt kiểm tra tên host SSL

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return 'Lỗi kết nối cURL: ' . curl_error($ch);
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        // --- XỬ LÝ KẾT QUẢ ---

        // 1. Nếu Groq báo lỗi
        if (isset($result['error'])) {
            return 'Lỗi Groq (' . $result['error']['code'] . '): ' . $result['error']['message'];
        }

        // 2. Thành công
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }

        // 3. Khác
        return 'Không nhận được phản hồi.';
    }
}
?>
import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';

@Component({
  selector: 'app-checkout',
  imports: [CommonModule,FormsModule],
  templateUrl: './checkout.html',
  styleUrls: ['./checkout.css']
})
export class CheckoutComponent implements OnInit {

  // Thông tin người mua
  customer = {
    name: '',
    phone: '',
    address: '',
    email: '',
    note: ''
  };

  // Danh sách sản phẩm (Giả lập lấy từ Cart đã chọn)
  items = [
    { name: 'iPhone 15 Pro Max 256GB', price: 34000000, quantity: 1, image: 'https://images.unsplash.com/photo-1696446701796-da61225697cc?auto=format&fit=crop&w=100&q=60' },
    { name: 'Tai nghe Sony WH-1000XM5', price: 8500000, quantity: 2, image: 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?auto=format&fit=crop&w=100&q=60' }
  ];

  paymentMethod: string = 'cod'; // Mặc định là COD
  voucherCode: string = '';
  discountAmount: number = 0;
  shippingFee: number = 30000;
  
  // Trạng thái thanh toán ngân hàng
  isProcessingBanking: boolean = false;
  showSuccessModal: boolean = false;

  constructor(private router: Router) { }

  ngOnInit(): void {
  }

  // 1. Tính tổng tiền hàng
  get subTotal(): number {
    return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  }

  // 2. Tính tổng thanh toán cuối cùng
  get finalTotal(): number {
    return this.subTotal + this.shippingFee - this.discountAmount;
  }

  // 3. Xử lý áp mã Voucher
  applyVoucher() {
    if (this.voucherCode === 'TECH100') {
      this.discountAmount = 500000; // Giảm 500k
      alert('Áp dụng mã giảm giá thành công!');
    } else {
      this.discountAmount = 0;
      alert('Mã giảm giá không hợp lệ!');
    }
  }

  // 4. Xử lý Đặt hàng
  placeOrder() {
    if (!this.customer.name || !this.customer.phone || !this.customer.address) {
      alert('Vui lòng điền đầy đủ thông tin giao hàng!');
      return;
    }

    if (this.paymentMethod === 'banking') {
      // Mở Modal giả lập thanh toán ngân hàng
      this.openBankingSimulation();
    } else {
      // Thanh toán COD -> Thành công luôn
      this.orderSuccess();
    }
  }

  // 5. Giả lập thanh toán Ngân hàng
  openBankingSimulation() {
    // Kích hoạt nút Button trigger modal (Dùng JS thuần để trigger Bootstrap modal cho đơn giản)
    const btn = document.getElementById('openBankModalBtn');
    if (btn) btn.click();

    // Giả lập đang chờ quét QR (Sau 5 giây tự động thành công)
    this.isProcessingBanking = true;
    setTimeout(() => {
      this.isProcessingBanking = false;
      // Đóng modal bank
      const closeBtn = document.getElementById('closeBankModalBtn');
      if (closeBtn) closeBtn.click();
      
      this.orderSuccess();
    }, 5000); // 5 giây
  }

  // 6. Thông báo thành công và chuyển hướng
  orderSuccess() {
    this.showSuccessModal = true;
    // Tự động chuyển về trang chủ sau 3 giây
    setTimeout(() => {
      this.router.navigate(['/']);
    }, 3000);
  }
}
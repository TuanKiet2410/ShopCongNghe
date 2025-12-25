import { CommonModule } from '@angular/common';
import { Component, computed, inject, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { Checkout } from '../../services/checkout/checkout';

@Component({
  selector: 'app-checkout',
  imports: [CommonModule,FormsModule],
  templateUrl: './checkout.html',
  styleUrls: ['./checkout.css']
})
export class CheckoutComponent implements OnInit {
  chehckoutService = inject(Checkout);
    items = computed(() => this.chehckoutService.checkout_products());
  // Danh sách sản phẩm (Giả lập lấy từ Cart đã chọn)

  paymentMethod: string = 'cod'; // Mặc định là COD
  voucherCode: string = '';
  discountAmount: number = 0;
  shippingFee: number = 30000;
  
  // Trạng thái thanh toán ngân hàng
  isProcessingBanking: boolean = false;
  showSuccessModal: boolean = false;

  // Thông tin người mua
   userStr = localStorage.getItem('user');
 user = this.userStr ? JSON.parse(this.userStr) : null;


   constructor(private router: Router) { }

  ngOnInit(): void {
  }

  customer = {
    user_id:this.user?.user_id,
    image:'',
    name: '',
    phone: '',
    address: '',
    email: '',
  };
  invoices={
    user_id:this.user?.user_id,
    voucher_id:1,
    payment_method:this.paymentMethod,
    status:'pending',
    total_money:this.finalTotal,
    
  }

 



  // 1. Tính tổng tiền hàng
  get subTotal(): number {
    return this.items().reduce((sum, item) => sum + (item.price * item.quantity), 0);
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
            this.invoices.payment_method = 'banking';
      // Mở Modal giả lập thanh toán ngân hàng
      this.openBankingSimulation();
      this.chehckoutService.order(this.customer, this.invoices, this.items());
      
    } else {
      // Thanh toán COD -> Thành công luôn
      this.chehckoutService.order(this.customer, this.invoices, this.items());
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
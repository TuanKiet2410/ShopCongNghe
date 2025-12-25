import { CommonModule } from '@angular/common';
import { Component, computed, effect, inject, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Cartservice } from '../../services/cart/cartservice';
import { ProductInterface } from '../../interface/product-interface';
import { RouterLink } from "@angular/router";
import { Checkout } from '../../services/checkout/checkout';



@Component({
  selector: 'app-cart',
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './cart.html',
  styleUrls: ['./cart.css']
})
export class CartComponent implements OnInit {
  cartService=inject(Cartservice)
  chechOutService=inject(Checkout)
  // Dữ liệu giả lập trong giỏ hàng
  cartItems = computed(() => this.cartService.product_temp());
  constructor() {effect(() => { this.loadCart(); });}
 loadCart(){return this.cartService.loadCart()}
  ngOnInit(): void {
   
  }

  // 1. Tăng số lượng
  increaseQty(item: ProductInterface) {
    item.quantity++;
    while (item.quantity > item.stock) {
      alert('hết hàng số lượng mua hệ thống!');
      item.quantity=item.stock;
    }
  }

  // 2. Giảm số lượng (tối thiểu là 1)
  decreaseQty(item: ProductInterface) {
    if (item.quantity > 1) {
      item.quantity--;
    } else {
      // Hỏi người dùng có muốn xóa không nếu giảm về 0 (tuỳ chọn)
      const confirmDelete = confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?');
      if (confirmDelete) {
        this.removeItem(item.id);
      }
    }
  }

  // 3. Xóa sản phẩm
  removeItem(id: number) {
    this.cartService.removeItem(id);
  
  }

  // 4. Chọn tất cả / Bỏ chọn tất cả
  toggleAll(event: any) {
    const isChecked = event.target.checked;
    this.cartItems().forEach(item => item.selected = isChecked);
  }

  // Kiểm tra xem có đang chọn tất cả không (để update checkbox tổng)
  isAllSelected(): boolean {
    return this.cartItems().length > 0 && this.cartItems().every(item => item.selected);
  }

  onCheckOut() {
   this.chechOutService.checkout_products().push(...this.cartItems().filter(item => item.selected));
  }

  // 5. Tính tổng tiền (chỉ tính các item được selected)
  get totalAmount(): number {
    return this.cartItems()
      .filter(item => item.selected)
      .reduce((sum, item) => sum + (item.price * item.quantity), 0);
  }
  
  // Đếm số lượng sản phẩm đang chọn
  get selectedCount(): number {
    return this.cartItems().filter(item => item.selected).length;
  }


}
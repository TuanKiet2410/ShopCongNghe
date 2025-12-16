import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';

interface CartItem {
  id: number;
  name: string;
  price: number;
  image: string;
  category: string;
  quantity: number;
  selected: boolean; // Trạng thái chọn để thanh toán
}

@Component({
  selector: 'app-cart',
  imports: [CommonModule,FormsModule],
  templateUrl: './cart.html',
  styleUrls: ['./cart.css']
})
export class CartComponent implements OnInit {

  // Dữ liệu giả lập trong giỏ hàng
  cartItems: CartItem[] = [
    {
      id: 1,
      name: 'iPhone 15 Pro Max 256GB',
      price: 34000000,
      image: 'https://images.unsplash.com/photo-1696446701796-da61225697cc?auto=format&fit=crop&w=200&q=60',
      category: 'Điện thoại',
      quantity: 1,
      selected: true
    },
    {
      id: 2,
      name: 'Tai nghe Sony WH-1000XM5',
      price: 8500000,
      image: 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?auto=format&fit=crop&w=200&q=60',
      category: 'Phụ kiện',
      quantity: 2,
      selected: true
    },
    {
      id: 3,
      name: 'Ốp lưng MagSafe',
      price: 1200000,
      image: 'https://images.unsplash.com/photo-1603313011101-320f71811e7e?auto=format&fit=crop&w=200&q=60',
      category: 'Phụ kiện',
      quantity: 1,
      selected: false // Mặc định chưa chọn
    }
  ];

  constructor() { }

  ngOnInit(): void {
  }

  // 1. Tăng số lượng
  increaseQty(item: CartItem) {
    item.quantity++;
  }

  // 2. Giảm số lượng (tối thiểu là 1)
  decreaseQty(item: CartItem) {
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
    this.cartItems = this.cartItems.filter(item => item.id !== id);
  }

  // 4. Chọn tất cả / Bỏ chọn tất cả
  toggleAll(event: any) {
    const isChecked = event.target.checked;
    this.cartItems.forEach(item => item.selected = isChecked);
  }

  // Kiểm tra xem có đang chọn tất cả không (để update checkbox tổng)
  isAllSelected(): boolean {
    return this.cartItems.length > 0 && this.cartItems.every(item => item.selected);
  }

  // 5. Tính tổng tiền (chỉ tính các item được selected)
  get totalAmount(): number {
    return this.cartItems
      .filter(item => item.selected)
      .reduce((sum, item) => sum + (item.price * item.quantity), 0);
  }
  
  // Đếm số lượng sản phẩm đang chọn
  get selectedCount(): number {
    return this.cartItems.filter(item => item.selected).length;
  }
}
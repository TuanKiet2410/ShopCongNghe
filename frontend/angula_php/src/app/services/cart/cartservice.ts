import { Injectable, signal } from '@angular/core';
import { ProductInterface } from '../../interface/product-interface';

@Injectable({
  providedIn: 'root',
})
export class Cartservice {
  // Khởi tạo signal rỗng
  product_temp = signal<ProductInterface[]>([]);

  constructor() {
    this.loadCart();
  }

  loadCart() {
    // 1. Lấy dữ liệu từ LocalStorage
    const data = localStorage.getItem('cart');
    
    // 2. Parse dữ liệu, nếu không có thì gán mảng rỗng
    const parsedData = data ? JSON.parse(data) : [];

    // 3. Dùng hàm .set() để GÁN lại toàn bộ giá trị cho Signal
    // (Thay vì push mảng vào mảng)
    this.product_temp.set(parsedData);
  }

  addToCart(product: ProductInterface) {
    // Dùng .update() để cập nhật giá trị cũ dựa trên giá trị mới
    this.product_temp.update((currentCart) => {
      // Tìm xem sản phẩm đã có trong giỏ chưa
      const existingItem = currentCart.find((p) => p.id === product.id);

      if (existingItem) {
        // Nếu có rồi: Tạo ra mảng mới, update số lượng của item đó
        return currentCart.map(item => 
          item.id === product.id 
            ? { ...item, quantity: item.quantity + product.quantity }
            : item
        );
      } else {
        // Nếu chưa có: Tạo mảng mới bao gồm mảng cũ + item mới
        return [...currentCart, product];
      }
    });

    // Sau khi update signal xong, lưu mảng mới nhất vào LocalStorage
    this.saveToLocalStorage();
  }

  removeItem(id: number) {
    this.product_temp.update((item) => item.filter((p) => p.id !== id));
    // Nhớ lưu lại sau khi xóa
    this.saveToLocalStorage();
  }

  // Viết hàm lưu riêng để tái sử dụng cho gọn
  private saveToLocalStorage() {
    localStorage.setItem('cart', JSON.stringify(this.product_temp()));
  }
}
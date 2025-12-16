import { CommonModule } from '@angular/common';
import { Component, effect, inject, OnInit } from '@angular/core';
import { ProductPtService } from '../../phantrang/product-pt-service';

// Định nghĩa nhanh Interface cho Sản phẩm (Sau này sẽ đưa vào model riêng)
interface Product {
  id: number;
  name: string;
  price: number;
  discountPrice?: number; // Giá khuyến mãi (có thể có hoặc không)
  image: string;
  isHot: boolean;
  rating: number;
}

@Component({
  selector: 'app-home',
  imports: [CommonModule],
  templateUrl: './home.html',
  styleUrls: ['./home.css']
})
export class HomeComponent implements OnInit {
productService=inject(ProductPtService)
  // Dữ liệu giả lập cho Banner
  banners = [
    {
      image: '#',
      title: 'Công nghệ Tương lai',
      subtitle: 'Khám phá những thiết bị đột phá nhất năm 2025'
    },
    {
      image: '#',
      title: 'Siêu Sale Mùa Hè',
      subtitle: 'Giảm giá lên đến 50% cho các phụ kiện laptop'
    },
    {
      image: '#',
      title: 'Gaming Gear Đỉnh Cao',
      subtitle: 'Nâng tầm trải nghiệm game của bạn'
    }
  ];

  // Dữ liệu giả lập cho Sản phẩm Hot Deal
  hotProducts: Product[] = [
    {
      id: 1,
      name: 'Laptop Gaming Asus ROG',
      price: 25000000,
      discountPrice: 22500000,
      image: 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=500&q=60',
      isHot: true,
      rating: 5
    },
    {
      id: 2,
      name: 'Tai nghe Sony WH-1000XM5',
      price: 8500000,
      discountPrice: 7200000,
      image: 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?auto=format&fit=crop&w=500&q=60',
      isHot: true,
      rating: 4.5
    },
    {
      id: 3,
      name: 'MacBook Air M2 2024',
      price: 32000000,
      image: 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=500&q=60',
      isHot: true,
      rating: 5
    },
    {
      id: 4,
      name: 'Bàn phím cơ Keychron K2',
      price: 2100000,
      discountPrice: 1850000,
      image: 'https://images.unsplash.com/photo-1587829741301-dc798b91a603?auto=format&fit=crop&w=500&q=60',
      isHot: true,
      rating: 4
    }
  ];

constructor() {
  this.productService=inject(ProductPtService)
    // Nếu muốn log ra console mỗi khi dữ liệu thay đổi thì dùng effect
    effect(() => {
      const data = this.productService.products_signal();
      console.log("Dữ liệu trong Component hiện tại là:", this.productService.products_signal());
    });

    console
  }
  ngOnInit(): void {
  }

}

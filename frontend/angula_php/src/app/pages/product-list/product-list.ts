import { CommonModule } from '@angular/common';
import { Component, computed, effect, inject, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ProductService } from '../../services/product-service';
import { ProductInterface } from '../../interface/product-interface';
import { ProductPtService } from '../../phantrang/product-pt-service';

@Component({
  selector: 'app-product-list',
  imports: [CommonModule, FormsModule],
  templateUrl: './product-list.html',
  styleUrls: ['./product-list.css']
})
export class ProductListComponent implements OnInit {
  producService = inject(ProductPtService);
  product_List = computed(() => this.producService.products_signal());

  // --- PHẦN 1: DỮ LIỆU ---
  // Sửa: Khởi tạo rỗng, không gán snapshot cũ dễ gây lỗi
  filteredProducts: ProductInterface[] = []; 

  // --- PHẦN 2: FILTER ---
  searchText: string = '';
  selectedCategory: string = 'Tất cả';
  selectedBrand: string = 'Tất cả';
  selectedPriceRange: string = 'all';
  selectedRating: number = 0;

  categories = ['Điện thoại', 'Laptop', 'Tablet', 'Phụ kiện'];
  brands = ['Apple', 'Samsung', 'Dell', 'Sony'];

  // --- PHẦN 3: PHÂN TRANG ---
  currentPage: number = 1;
  itemsPerPage: number = 6; // Sửa: Nên để 6 hoặc 8 sản phẩm/trang cho dễ nhìn
   // Sửa: Khởi tạo là 0, để code tự tính toán
  totalPages: number = 0;
// 1. THÊM BIẾN LƯU TRẠNG THÁI SẮP XẾP
  selectedSort: string = 'default'; // default, price-asc, price-desc

  // Getter tạo mảng số trang an toàn hơn
  get pageNumbers(): number[] {
    if (this.totalPages <= 0) return [];
    return Array(this.totalPages).fill(0).map((_, i) => i + 1);
  }

  constructor() {
    // Effect tự động chạy khi signal product_List thay đổi (khi API trả về data)
    effect(() => {
      if (this.product_List().length > 0) {
        this.totalPages = this.producService.total_pages;
        this.currentPage = this.producService.current_page;
        // Data về -> Chạy lọc lần đầu -> Tự động tính phân trang luôn
        this.applyFilter(); 
        console.log("Dữ liệu trong Component hiện tại là:", this.producService.products_signal());
        console.log(this.totalPages)
      }
    });
   
  }

  ngOnInit(): void {
    // --- SỬA LỖI QUAN TRỌNG NHẤT ---
    // Phải gọi API thì mới có dữ liệu để signal hoạt động
   // this.producService.loadProducts(); 
    if(this.searchText){
    this.producService.loadSearchProducts(this.searchText);
   }
    if (localStorage.getItem('token')) {
    this.producService.loadProducts(1);
  }
   
  }

  // --- LOGIC LỌC & PHÂN TRANG ---
  applyFilter() {
    // 1. Lọc dữ liệu từ nguồn gốc (Signal)
   
      this.filteredProducts = this.product_List().filter(product => {
      const matchName = product.name.toLowerCase().includes(this.searchText.toLowerCase());
      const matchCategory = this.selectedCategory === 'Tất cả' || product.category === this.selectedCategory;
      const matchBrand = this.selectedBrand === 'Tất cả' || product.brand === this.selectedBrand;

      let matchPrice = true;
      if (this.selectedPriceRange === 'under-10') matchPrice = product.price < 10000000;
      else if (this.selectedPriceRange === '10-30') matchPrice = product.price >= 10000000 && product.price <= 30000000;
      else if (this.selectedPriceRange === 'over-30') matchPrice = product.price > 30000000;

      return matchName && matchCategory && matchBrand && matchPrice;
    });

    // 5. Sắp xếp theo giá
    if (this.selectedSort === 'price-asc') {
      this.filteredProducts.sort((a, b) => a.price - b.price);
    } else if (this.selectedSort === 'price-desc') {
      this.filteredProducts.sort((a, b) => b.price - a.price);
    }

  }



  goToPage(page: number) {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      this.producService.loadProducts(page);
      // Mẹo: Cuộn lên đầu trang khi chuyển trang
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }


  resetFilter() {
    this.searchText = '';
    this.selectedCategory = 'Tất cả';
    this.selectedBrand = 'Tất cả';
    this.selectedPriceRange = 'all';
    this.selectedRating = 0;
    this.applyFilter();
  }


onchangesearch(){
      if(this.searchText){
    this.producService.loadSearchProducts(this.searchText);
   }else{
    this.producService.loadProducts(1);
   }

}




}
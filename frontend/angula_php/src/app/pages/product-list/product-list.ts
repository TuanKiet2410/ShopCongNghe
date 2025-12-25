import { CommonModule } from '@angular/common';
import { Component, computed, effect, inject, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ProductInterface } from '../../interface/product-interface';
import { ProductPtService } from '../../phantrang/product-pt-service';
import { Cartservice } from '../../services/cart/cartservice';

@Component({
  selector: 'app-product-list',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './product-list.html',
  styleUrls: ['./product-list.css'],
})
export class ProductListComponent implements OnInit {

  productService = inject(ProductPtService);
  cartService = inject(Cartservice);

  // ===== SIGNAL =====
  product_List = computed(() => this.productService.products_signal());

  // ===== FILTER =====
  searchText = '';
  selectedCategory = '';
  selectedBrand = '';
  selectedPriceRange = '';
  selectedSort = 'default';

  categories = ['Tất cả', 'Điện thoại', 'Laptop', 'Tablet', 'Phụ kiện'];
  brands = ['Tất cả', 'Apple', 'Samsung', 'Dell', 'Sony'];

  // ===== PAGINATION =====
  currentPage = 1;
  totalPages =0;

  constructor() {
    this.loadData(1)
    // Lắng nghe khi API trả dữ liệu
    effect(() => {
      const products = this.product_List();
  const totalPages = this.productService.totalPagesSignal();
  const currentPage = this.productService.currentPageSignal();

  if (products.length > 0) {
    this.totalPages = totalPages;
    this.currentPage = currentPage;
  }
    });
  }

  ngOnInit(): void {
    // Load lần đầu
    this.loadData(1);
  }

  // =================================================
  // LOAD DATA (TRUNG TÂM)
  // =================================================
  loadData(page: number) {
    this.productService.loadProducts(
      page,
      this.searchText,
      this.selectedCategory,
      this.selectedBrand,
      this.selectedPriceRange
    );

  }

  // =================================================
  // APPLY FILTER
  // =================================================
  applyFilter() {
    this.currentPage = 1;
    this.loadData(1);
  }

  // =================================================
  // SEARCH
  // =================================================
  onSearchChange() {
    this.currentPage = 1;
    this.loadData(1);
    this.applyFilter();
    
  }

  // =================================================
  // PAGINATION
  // =================================================
  goToPage(page: number) {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      this.loadData(page);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  get pageNumbers(): number[] {
    return Array.from({ length: this.totalPages }, (_, i) => i + 1);
  }

  // =================================================
  // RESET FILTER
  // =================================================
  resetFilter() {
    this.searchText = '';
    this.selectedCategory = '';
    this.selectedBrand = '';
    this.selectedPriceRange = '';
    this.selectedSort = 'default';
    this.loadData(1);
  }

  // =================================================
  // SORT (CLIENT SIDE)
  // =================================================
  get sortedProducts(): ProductInterface[] {
    const products = [...this.product_List()];

    if (this.selectedSort === 'price-asc') {
      return products.sort((a, b) => a.price - b.price);
    }

    if (this.selectedSort === 'price-desc') {
      return products.sort((a, b) => b.price - a.price);
    }

    return products;
  }

  // =================================================
  // ADD TO CART
  // =================================================
  addToCart(product: ProductInterface) {
    this.cartService.addToCart({ ...product, quantity: 1 });
  }
}

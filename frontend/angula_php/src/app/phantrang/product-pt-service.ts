import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable, signal } from '@angular/core';
import { Product_PT_Interface } from '../interface/product-pt-interface';
import { ProductInterface } from '../interface/product-interface';

@Injectable({
  providedIn: 'root',
})
export class ProductPtService {

  apiUrl = 'http://localhost/DA_CD_PHP/products';

  http = inject(HttpClient);

  // ===== SIGNAL =====
  products_signal = signal<ProductInterface[]>([]);

  totalPagesSignal = signal(0);
  currentPageSignal = signal(1);

  total_items = 0;

  constructor() {
    this.loadProducts();
  }

  // =================================================
  // LOAD PRODUCTS (SEARCH + FILTER + PAGINATION)
  // =================================================
  loadProducts(
    page: number = 1,
    search: string = '',
    category: string = '',
    brand: string = '',
    priceRange: string = ''
  ) {
    let params = new HttpParams().set('page', page);

    if (search) params = params.set('search', search);
    if (category && category !== 'Tất cả') params = params.set('category', category);
    if (brand && brand !== 'Tất cả') params = params.set('brand', brand);
    if (priceRange) params = params.set('priceRange', priceRange);

    this.http
      .get<Product_PT_Interface>(this.apiUrl, { params })
      .subscribe({
        next: (response) => {
          console.log('API response:', response);

          this.totalPagesSignal.set(response.pagination.total_pages);
          this.currentPageSignal.set(response.pagination.current_page);
          this.total_items = response.pagination.total_items
            ;


          this.products_signal.set(response.data);
        },
        error: (err) => {
          console.error('Lỗi gọi API:', err);
        },
      });
  }

  // =================================================
  // GET SIGNAL
  // =================================================
  getProducts() {
    return this.products_signal();
  }

  // =================================================
  // CREATE
  // =================================================
  create(product: ProductInterface) {
    this.http.post(this.apiUrl, product).subscribe({
      next: () => {
        alert('Thêm sản phẩm thành công');
        this.loadProducts(this.currentPageSignal());
      },
      error: (err) => {
        console.error('Lỗi tạo sản phẩm:', err);
      },
    });
  }

  // =================================================
  // DELETE
  // =================================================
  delete(id: number) {
    this.http.delete(`${this.apiUrl}/${id}`).subscribe({
      next: () => {
        alert('Xóa thành công');
        this.loadProducts(this.currentPageSignal());
      },
      error: (err) => {
        console.error('Lỗi xóa:', err);
      },
    });
  }

  // =================================================
  // UPDATE
  // =================================================
  update(id: number, product: ProductInterface) {
    this.http.put(`${this.apiUrl}/${id}`, product).subscribe({
      next: () => {
        alert('Cập nhật thành công');
        this.loadProducts(this.currentPageSignal());
      },
      error: (err) => {
        console.error('Lỗi cập nhật:', err);
      },
    });
  }
}

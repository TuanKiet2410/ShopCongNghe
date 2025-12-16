import { HttpClient } from '@angular/common/http';
import { inject, Injectable, signal } from '@angular/core';
import { Product_PT_Interface } from '../interface/product-pt-interface';
import { ProductInterface } from '../interface/product-interface';

@Injectable({
  providedIn: 'root',
})
export class ProductPtService {

  
  apiUrl='http://localhost/DAPHP2/products';
  http= inject(HttpClient);
  products_signal= signal<ProductInterface[]>([])
  total_pages=0;
  current_page=1;
  constructor() {
  
    // XÓA console.log ở đây, vì lúc này chưa có dữ liệu đâu!
  }

  loadProducts(page: number) {
    // Chú ý: get<ProductResponse> chứ không phải get<Product_PT_Interface[]>
    this.http.get<Product_PT_Interface>(`${this.apiUrl}?page=${page}`).subscribe({
      next: (response) => {
        // Log ở đây mới thấy dữ liệu, vì đây là lúc server đã trả về
        console.log('Dữ liệu từ server:', response); 
        this.current_page=response.pagination.current_page;
        this.total_pages=response.pagination.total_pages;
        // Cập nhật signal bằng dữ liệu bên trong biến .data
        this.products_signal.set(response.data); 
      },
      error: (err) => {
        console.error('Lỗi gọi API:', err);
      }
    });
  }
  loadSearchProducts(searchTerm: string) {
    console.log(`dữ liệu gửi đi: ${this.apiUrl}?page=&&search=${searchTerm} `)
    this.http.get<Product_PT_Interface>(`${this.apiUrl}?page=&&search=${searchTerm}`).subscribe({
      next: (response) => {
        this.current_page=response.pagination.current_page;
        this.total_pages=response.pagination.total_pages;
        this.products_signal.set(response.data); 
      },
      error: (err) => {
        console.error('Lỗi gọi API:', err);
      }
    });
    
  }
  getProducts() {
    return this.products_signal();
  }
}

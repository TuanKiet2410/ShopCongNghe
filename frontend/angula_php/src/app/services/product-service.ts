import { Injectable, signal } from '@angular/core';
import { ProductInterface } from '../interface/product-interface';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class ProductService {
  private apiUrl= 'http://localhost/DA_CD_PHP/products';
  // tạo một signal lưu danh sách sản phâm
  products_signal= signal<ProductInterface[]>([]);
  constructor (private http: HttpClient ) {
    this.loadProducts();
  }
  loadProducts(): void{
    this.http.get<ProductInterface[]>(this.apiUrl).subscribe((data)=>{
      this.products_signal.set(data);
    })
  }
  
  getProductsById(id: number){
    return this.http.get<ProductInterface>(`${this.apiUrl}/${id}`);
  }
  getProducts(): ProductInterface[]{
    return this.products_signal();
  }
  create(product: ProductInterface){
    this.http.post<ProductInterface>(this.apiUrl,product).subscribe((newProduct)=>{
      this.products_signal.update((item)=>{
        return [...item,newProduct];
      })
    })
  }

  updateProducts(id:number, product:ProductInterface){
    this.http.put<ProductInterface>(`${this.apiUrl}/${id}`,product).subscribe((updatedProduct)=>{
      this.products_signal.update((item)=>{
        return item.map((p)=>{
          if(p.id===id){
            return updatedProduct;
          }
          return p;
        })
      })
    })
  }
  
  deleteProduct(id: number){
    this.http.delete(`${this.apiUrl}/${id}`).subscribe(()=>{
      this.products_signal.update((item)=>{
        return item.filter((p)=>p.id!==id);
      })
    })
  }













//--------------------------------------

  
}

import { inject, Injectable, signal } from '@angular/core';
import { ProductInterface } from '../../interface/product-interface';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class Checkout {
  apiUrl = 'http://localhost/DA_CD_PHP';
  http = inject(HttpClient);
  checkout_products=signal<any[]>([])

  constructor() { }
  order(customer: any, invoices: any, product:any) {
    console.log(customer);
    console.log(invoices);
    console.log(product);
    this.http.post<any>(`${this.apiUrl}/customers`, { customer}).subscribe((data) => { console.log(data); });
  
    this.http.post<any>(`${this.apiUrl}/invoices`, { invoices,cart_items:product}).subscribe((data) => { console.log(data); });
    this.checkout_products.set([]);
    
  }
}

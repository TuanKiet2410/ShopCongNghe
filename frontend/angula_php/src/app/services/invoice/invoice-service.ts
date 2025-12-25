import { HttpClient } from '@angular/common/http';
import { inject, Injectable, signal } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class InvoiceService {
  http=inject(HttpClient)
  apiUrl = 'http://localhost/DA_CD_PHP/invoices';

  invoices_signal = signal<any[]>([])
constructor() { 
  this.loadInvoices();
} 
loadInvoices() { return this.http.get<any>(`${this.apiUrl}`).subscribe({
  next: (response) => {
    this.invoices_signal.set(response);
  },
  error: (err) => {
    console.error('Lỗi gọi API:', err);
  }
}); 
}


updateStatusInvoice(id: number, invoice: any) {
   const isConfirm = confirm('Bạn có chắc chắn muốn up date thành :'+ invoice.status);

  if (!isConfirm) return;
  return this.http.put(`${this.apiUrl}/${id}`, invoice).subscribe({
    next: (reponse) => {
      console.log("dữ liệu từ server", reponse);
      this.loadInvoices();
    },
    error: (err) => {
      console.error('Lỗi gọi API:', err);
    }
  });
}

deleteInvoice($id: number) {
  return this.http.delete(`${this.apiUrl}/${$id}`).subscribe({
    next: (reponse) => {
      console.log("đã xóa invoice");
      this.loadInvoices();
    },
    error: (err) => {
      console.error('Lỗi gọi API:', err);
    }
  });
}
}

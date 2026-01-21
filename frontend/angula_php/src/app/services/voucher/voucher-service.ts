import { HttpClient } from '@angular/common/http';
import { Injectable, signal } from '@angular/core';
import { VoucherInterface } from '../../interface/voucher';

@Injectable({
  providedIn: 'root',
})
export class VoucherService {
  apiurl = 'http://localhost/DA_CD_PHP/vouchers';
  vouchers_signal = signal<VoucherInterface[]>([]);
  constructor(private http: HttpClient) {
    this.loadVouchers();
  }
  loadVouchers() {
    return this.http.get<VoucherInterface[]>(this.apiurl).subscribe((data) => {
      this.vouchers_signal.set(data);
    })
  }
  getVouchersById(id: number) {
    return this.http.get<VoucherInterface>(`${this.apiurl}/${id}`);
  }
  getVouchers() {
    return this.vouchers_signal();
  }
  createVoucher(voucher: VoucherInterface) {
    return this.http.post<VoucherInterface>(this.apiurl, voucher);
  }
  updateVoucher(id: number, voucher: VoucherInterface) {
    return this.http.put<VoucherInterface>(`${this.apiurl}/${id}`, voucher).subscribe({
        next: (res) => {
          alert('Cập nhật thành công!');
          
          this.loadVouchers();
        },
        error: (err) => alert('Lỗi cập nhật: ' + err.error.message)
      });;
  }
  deleteVoucher(id: number) {
    return this.http.delete(`${this.apiurl}/${id}`);
  }
}

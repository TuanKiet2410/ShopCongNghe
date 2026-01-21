import { CommonModule } from '@angular/common';
import { Component, computed, inject, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { VoucherService } from '../../services/voucher/voucher-service';

interface Promotion {
  id: number;
  title: string;
  description: string;
  image: string;
  startDate: Date;
  endDate: Date;
  discountCode: string; // Mã giảm giá
  discountValue: string; // Ví dụ: -20%, -500k
  isActive: boolean;
}

@Component({
  selector: 'app-promotions',
  imports: [CommonModule,FormsModule],
  templateUrl: './promotions.html',
  styleUrls: ['./promotions.css']
})
export class PromotionsComponent implements OnInit {
  voucherService = inject(VoucherService)
  promotions_list =computed(() => this.voucherService.vouchers_signal())

  constructor() { }

  ngOnInit(): void {
    this.voucherService.loadVouchers();
  }
  isActive: boolean = true;
  // Hàm copy mã giảm giá
  copyCode(code: number) {
    let codeString = String(code);//ép kiểu dữ liệu
    navigator.clipboard.writeText(codeString);
    alert('Đã sao chép mã: ' + codeString);
  }
}
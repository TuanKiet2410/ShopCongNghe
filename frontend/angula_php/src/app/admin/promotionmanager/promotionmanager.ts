import { Component, computed, OnInit } from '@angular/core';
import { VoucherService } from '../../services/voucher/voucher-service';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { VoucherInterface } from '../../interface/voucher';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-promotionmanager',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './promotionmanager.html',
  styleUrl: './promotionmanager.css',
})
export class Promotionmanager implements OnInit {
vouchers =computed(() => this.voucherService.vouchers_signal())
  imageBase64: string | null = null;

  voucherForm: FormGroup;
  isEditMode = false;
  currentVoucherId: number | null = null;
  message: string = '';

  constructor(
    private voucherService: VoucherService,
    private fb: FormBuilder
  ) {
    // Khởi tạo Form với các validate cơ bản
    this.voucherForm = this.fb.group({
      code: ['', Validators.required],
      discount: 25,
      quantity: 5,
      description: [''],
      image: '', // Tạm thời nhập link ảnh text
      start_date: ['', Validators.required],
      end_date: ['', Validators.required]
    });
  }

  ngOnInit(): void {
    this.loadVouchers();
  }

  // Lấy danh sách voucher
  loadVouchers() {
    this.voucherService.loadVouchers()
  }

  // Xử lý Submit (Thêm mới hoặc Cập nhật)
  onSubmit() {
    console.log(this.voucherForm.value);
    if (this.voucherForm.invalid) return;

    const formData = this.voucherForm.value;

    // Backend cần format ngày giờ chuẩn SQL, input datetime-local trả về 'YYYY-MM-DDTHH:mm'
    // Ta replace 'T' thành khoảng trắng để khớp với MySQL DATETIME
    formData.start_date = formData.start_date.replace('T', ' ') + ':00';
    formData.end_date = formData.end_date.replace('T', ' ') + ':00';

    if (this.isEditMode && this.currentVoucherId) {
      this.voucherService.updateVoucher(this.currentVoucherId, formData)
    } else {
      this.voucherService.createVoucher(formData).subscribe({
        next: (res) => {
          alert('Thêm mới thành công!');
          this.resetForm();
          this.loadVouchers();
        },
        error: (err) => alert('Lỗi thêm mới: ' + err.error.message)
      });
    }
  }

  // Đổ dữ liệu lên form để sửa
  onEdit(voucher: VoucherInterface) {
    this.isEditMode = true;
    this.currentVoucherId = voucher.id || null;

    // Convert format ngày MySQL (YYYY-MM-DD HH:mm:ss) sang input HTML (YYYY-MM-DDTHH:mm)
    const formatForInput = (dateStr: string) => dateStr.replace(' ', 'T').slice(0, 16);

    this.voucherForm.patchValue({
      code: voucher.code,
      discount: voucher.discount,
      quantity: voucher.quantity,
      description: voucher.description,
      image: voucher.image,
      start_date: formatForInput(voucher.start_date),
      end_date: formatForInput(voucher.end_date)
    });
    
    // Cuộn trang lên đầu
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Xóa voucher
  onDelete(id: number) {
    if (confirm('Bạn có chắc chắn muốn xóa mã giảm giá này?')) {
      this.voucherService.deleteVoucher(id).subscribe({
        next: () => {
          this.loadVouchers();
          alert('Đã xóa!');
        },
        error: (err) => alert('Xóa thất bại')
      });
    }
  }

  // Reset form về trạng thái ban đầu
  resetForm() {
    this.isEditMode = false;
    this.currentVoucherId = null;
    this.voucherForm.reset({
      quantity: 1,
      discount: 0
    });
  }



onImageChange(event: any) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = () => {
    this.imageBase64 = reader.result as string;
    this.voucherForm.patchValue({ image: this.imageBase64 });
  };
  reader.readAsDataURL(file);
}

}

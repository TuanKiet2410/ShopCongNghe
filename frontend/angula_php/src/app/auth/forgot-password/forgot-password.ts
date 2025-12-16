import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from "@angular/router";

@Component({ selector: 'app-forgot-password',
  imports: [FormsModule, RouterLink],
  templateUrl: './forgot-password.html', styleUrls: ['./forgot-password.css'] })
export class ForgotPasswordComponent {
  email: string = '';

  onSubmit() {
    if(this.email) {
      alert(`Đã gửi link khôi phục mật khẩu đến email: ${this.email}`);
    }
  }
}
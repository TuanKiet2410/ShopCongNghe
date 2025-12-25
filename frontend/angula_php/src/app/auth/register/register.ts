import { Component, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth/auth-service';

@Component({ selector: 'app-register',
  imports: [FormsModule, RouterLink],
  templateUrl: './register.html', styleUrls: ['./register.css'] })
export class RegisterComponent {
  authService = inject(AuthService);
  registerData = { name: '', password: '', confirmPassword: '' };

  constructor(private router: Router) {}
  onRegister() {
    if (this.registerData.password !== this.registerData.confirmPassword) {
      alert('Mật khẩu nhập lại không khớp!');
      return;
    }
    const newUser = { username: this.registerData.name, password: this.registerData.password,role: 'user' };

    this.authService.signup(newUser);
    this.router.navigate(['/login']);
  }
}
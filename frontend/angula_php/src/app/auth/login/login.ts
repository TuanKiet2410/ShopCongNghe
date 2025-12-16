import { Component, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth/auth-service';

@Component({ selector: 'app-login', templateUrl: './login.html',
  imports: [FormsModule, RouterLink],
  styleUrls: ['./login.css'] })
export class LoginComponent {
  authService=inject(AuthService);
  loginData = { username: '', password: '' };

  constructor(private router: Router) {}

  onLogin() {
   this.authService.Signin(this.loginData.username, this.loginData.password);
    this.router.navigate(['/']);
  }
}
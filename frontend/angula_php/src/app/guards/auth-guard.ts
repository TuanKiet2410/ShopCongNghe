// auth.guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth/auth-service';
// Nhớ import service của bạn

export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.isLoggedIn()) {
    return true; // Cho phép đi tiếp
  } else {
    // Chưa đăng nhập -> Đá về trang login
    router.navigate(['/login']); 
    return false;
  }
};
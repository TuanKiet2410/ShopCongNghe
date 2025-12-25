// admin.guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth/auth-service';

export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Phải đăng nhập VÀ có quyền admin
  if (authService.isLoggedIn() && (authService.isAdmin() || authService.isEmployee())) {
    return true;
  } else {
    alert('Bạn không có quyền truy cập trang này!');
    router.navigate(['/home']); // Hoặc trang nào đó cho user thường
    return false;
  }
};
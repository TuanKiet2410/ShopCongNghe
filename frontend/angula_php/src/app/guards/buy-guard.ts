import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth/auth-service';

export const buyGuard: CanActivateFn = (route, state) => {
  const authService=inject(AuthService);
  const router = inject(Router);
  // Phải đăng nhập VÀ có quyền admin
  if (authService.isLoggedIn() && (authService.isAdmin() || authService.isEmployee() || authService.isCustomer())) {
    return true;
  } else {
    alert('hãy trở thành thành viên để đặt hàng!');
    router.navigate(['/home']); // Hoặc trang nào đó cho user thường
    return false;
  }
  return true;
};

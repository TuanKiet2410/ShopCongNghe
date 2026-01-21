import { Component, computed, inject, signal } from '@angular/core';
import { UserService } from '../../services/user/user-service';
import { AuthService } from '../../services/auth/auth-service';
import { userNameManageInterface } from '../../interface/user-manager-interface';

@Component({
  selector: 'app-permissionmanager',
  imports: [],
  templateUrl: './permissionmanager.html',
  styleUrl: './permissionmanager.css',
})
export class Permissionmanager {
idUserEdit: number = 0
newPermissions={
  permission:[]
}
upPermissions: string[]=[];
userService= inject(UserService)
users=computed<any[]>(()=> this.userService.user_list())
authService= inject(AuthService)
 editablePermissions: string[]=['create', 'update', 'delete','buy'];
hasPermission(user: userNameManageInterface, perm: string): boolean {
  if (user.permission) {
    if (user.permission.includes('all')) {
      return true;
    }
  }
  return user.permission?.includes(perm) ?? false;
}

// //lưu trực tiếp kh cần nhấn nú save
// togglePermission(user: UserInterface, perm: string) {
//   const newPermissions = user.permissions?.includes(perm)
//     ? user.permissions?.filter(p => p !== perm)
//     : [...user.permissions! , perm];

//   // Gọi API update
//   this.authService.updatePermissions(user.id!, newPermissions).subscribe(() => {
//     user.permissions = newPermissions; // cập nhật tạm trong UI
//   });
// }



/** Bật/tắt quyền — chỉ thay đổi cục bộ, chưa lưu */
  togglePermission(user: userNameManageInterface, perm: string) {
    const has = user.permission?.includes(perm);
    user.permission = has
      ? user.permission!.filter(p => p !== perm)
      : this.upPermissions=[...user.permission!, perm];
      this.idUserEdit=user.id!
  }

/** Khi nhấn nút “Lưu” */
  saveAllChanges() {
 
      this.userService.updatePermissions(this.idUserEdit, this.upPermissions);
   
       // Gọi API cập nhật quyền ở đây
    this.isEditing.set(false);
    alert('Đã lưu tất cả thay đổi quyền!');
    console.log(this.upPermissions, this.idUserEdit)
  }
// 🔹 Trạng thái bật/tắt chỉnh sửa
  isEditing = signal(false);
   enableEdit() {
    this.isEditing.set(true);
  }



  cancelEdit() {
    this.isEditing.set(false);
    alert('Đã hủy chỉnh sửa!');
  }
}

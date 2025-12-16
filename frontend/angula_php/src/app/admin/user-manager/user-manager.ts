import { CommonModule } from '@angular/common';
import { Component, computed, effect, inject, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { UserService } from '../../services/user/user-service';

@Component({ selector: 'app-user-manager',
  imports: [CommonModule,FormsModule],
  templateUrl: './user-manager.html' })
export class UserManagerComponent implements OnInit {
  userService= inject(UserService)
  user_list= computed(() => this.userService.user_list())
  constructor() {
     effect(() => {
      this.loadUser();
 })
  }
  ngOnInit(): void {
    
  }

  loadUser() {
    return this.user_list();
  }

  
  users = [
    { id: 1, name: 'Nguyễn Văn A', role: 'Admin', locked: false },
    { id: 2, name: 'Trần Thị B', role: 'Employer', locked: false },
    { id: 3, name: 'Lê Văn C', role: 'Customer', locked: true }
  ];

  newUser = { name: '', role: 'Customer' }; // Model cho form thêm mới

  addUser() {
    console.log(this.user_list());
    if (this.newUser.name) {
      this.users.push({ id: Date.now(), name: this.newUser.name, role: this.newUser.role, locked: false });
      this.newUser = { name: '', role: 'Customer' }; // Reset form
    }
  }

  deleteUser(id: number) {
    if (confirm('Xóa user này?')) this.users = this.users.filter(u => u.id !== id);
  }

  toggleLock(user: any) {
    user.locked = !user.locked;
  }
}
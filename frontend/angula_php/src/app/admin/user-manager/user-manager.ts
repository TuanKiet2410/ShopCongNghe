import { CommonModule } from '@angular/common';
import { Component, computed, effect, inject, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { UserService } from '../../services/user/user-service';
import { AuthService } from '../../services/auth/auth-service';
import { userNameManageInterface } from '../../interface/user-manager-interface';

@Component({
  selector: 'app-user-manager',
  imports: [CommonModule, FormsModule],
  templateUrl: './user-manager.html'
})
export class UserManagerComponent implements OnInit {
  userService = inject(UserService)
  user_list = computed(() => this.userService.user_list())
  constructor() {
    effect(() => {
      this.loadUser();
      console.log("hr", this.user_list());
    })
  }
  ngOnInit(): void {

  }
  isEditMode = false;
  loadUser() {
    return this.user_list();
  }



  authService = inject(AuthService);

  newUser = {
    fullname:'',
    email:'',
    phone:'',
    address:'',
    username: '',
    password: '123456', 
    role: '' ,
    is_locked: 0
  };
  resetForms() {
    this.newUser.username = '';
    this.newUser.role = '';
    this.newUser.password = '123456';
  }
  addUser() {
    this.authService.signup(this.newUser);
  }

  deleteUser(id: number) {
    this.userService.deleteUser(id);
  }

  toggleLock(user: any) {
    
    const updateLock={...user, is_locked: user.is_locked == 1 ? 0 : 1}
    this.userService.updateUser(user.id!, updateLock);
    console.log(updateLock)
  }

  idEdit: number = 0;
  editMode(username: string, id: number) {
    this.isEditMode = true
    this.newUser.username = this.user_list().find(u => u.username === username)?.username || '';
    this.newUser.password = '123456'
    this.idEdit = id;
  }

  updateUser() {
    this.userService.updateUser(this.idEdit, this.newUser);
    this.isEditMode = false
    this.resetForms();
  }
}
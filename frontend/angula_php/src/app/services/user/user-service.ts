import { HttpClient } from '@angular/common/http';
import { inject, Injectable, signal } from '@angular/core';
import { userNameManageInterface } from '../../interface/user-manager-interface';

@Injectable({
  providedIn: 'root',
})
export class UserService {
  apiUrl = 'http://localhost/DA_CD_PHP/users'
  http = inject(HttpClient);
  user_list = signal<userNameManageInterface[]>([])
  constructor() {
    this.loadUser();
  }
  loadUser() {
    

    return this.http.get<userNameManageInterface[]>(this.apiUrl).subscribe({
      next: (reponse) => {
        console.log("dữ liệu từ server", reponse);
        this.user_list.set(reponse);
      },
      error: (err) => {
        console.error('Lỗi gọi API:', err);
      }
    });
  }

  deleteUser(id: number) {
    return this.http.delete(`${this.apiUrl}/${id}`).subscribe({
      next: (reponse) => {
        console.log("dữ liệu từ server", reponse);
        this.loadUser();
      },
      error: (err) => {
        console.error('Lỗi gọi API:', err);
      }
    });
  }

  //updateUser
  updateUser(id: number, newUser: userNameManageInterface) {

    this.http.put(`${this.apiUrl}/${id}`, newUser).subscribe({
      next: (reponse) => {
        console.log("dữ liệu từ server", reponse);
        this.loadUser();
      }

    })
  }


  updatePermissions(id: number, permissions: string[]) {
    console.log({ permissions: permissions });
    return this.http.put(`${this.apiUrl}/${id}?permission`, { permission: permissions }).subscribe({

      next: (reponse) => {
        console.log("dữ liệu từ server", reponse);
        this.loadUser();
      }
    });
  }
}

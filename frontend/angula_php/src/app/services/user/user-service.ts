import { HttpClient } from '@angular/common/http';
import { inject, Injectable, signal } from '@angular/core';
import { userNameManageInterface } from '../../interface/user-manager-interface';

@Injectable({
  providedIn: 'root',
})
export class UserService {
  apiUrl ='http://localhost/DAPHP2/users'
  http= inject(HttpClient);
  user_list= signal<userNameManageInterface[]>([])
  constructor() {
    this.loadUser();
   }
  loadUser() {
    return this.http.get<userNameManageInterface[]>(this.apiUrl).subscribe( {
      next:(reponse)=>{
        console.log("dữ liệu từ server",reponse);
        this.user_list.set(reponse);
      },
      error: (err) => {
        console.error('Lỗi gọi API:', err);
      }
    });
  }
}

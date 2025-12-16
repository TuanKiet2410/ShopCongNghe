import { HttpClient } from '@angular/common/http';
import { Injectable, signal } from '@angular/core';
import { UserInterface } from '../../interface/user-interface';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  isLogin=signal<boolean>(false);
  userName=signal<string>('');
  private apiUrl='http://localhost/DAPHP2/users'
  constructor(private http: HttpClient) {
    
  }
Signin(username: string, password: string ) {
  return this.http
    .post<any>(`${this.apiUrl}?action=login`, { username, password })
    .subscribe({
      next: (res) => {
        console.log('API success:', res);
        this.isLogin.set(true);
        localStorage.setItem('username', res.data.username);
        localStorage.setItem('token', res.token);
        



        console.log('Token đã lưu:', localStorage.getItem('token'));
      },
      error: (err) => {
        console.error('API error:', err);
      }
    });
}


initUserFromStorage() {
  const username = localStorage.getItem('username');
  if (username) {
    this.userName.set(username);
  }
}




//=============signup
signup(username: string, password: string) {
  this.http.post<any>(`${this.apiUrl}?action=signup`, { username, password }).subscribe(data=>{
    if(data.message=='User created successfully'){
      alert('đăng ký thành công');
    }
    
  })
}

}

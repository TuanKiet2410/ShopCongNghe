import { HttpClient } from '@angular/common/http';
import { Injectable, signal } from '@angular/core';
import { userNameManageInterface } from '../../interface/user-manager-interface';
import { tap } from 'rxjs/operators';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  isLogin=signal<boolean>(false);
  userName=signal<string>('');
  private apiUrl='http://localhost/DA_CD_PHP'
  constructor(private http: HttpClient) {
    
  }
  Signin(username: string, password: string) {
    return this.http.post<any>(`${this.apiUrl}/login`, { username, password }, { withCredentials: true }).pipe(
      tap(res => {
        console.log('API success:', res);
        this.isLogin.set(true);
        localStorage.setItem('username', res.data.username);
        localStorage.setItem('token', res.token);
        localStorage.setItem('user', JSON.stringify(res.data));
        console.log('Token đã lưu:', localStorage.getItem('token'));
      })
    );
  }

signOut(){
  localStorage.clear();
  this.http.get<any>(`${this.apiUrl}/logout`).subscribe(data=>{
    console.log(data);
  })
}
initUserFromStorage() {
  const username = localStorage.getItem('username');
  if (username) {
    this.userName.set(username);
  }
}




//=============signup
signup(newUser:userNameManageInterface){
  console.log(newUser); 
  this.http.post<any>(`${this.apiUrl}/signup`,  newUser).subscribe(data=>{
    if(data.message=='User created successfully'){
      alert('đăng ký thành công');
    }
    
  })

}


// 1. Kiểm tra đã đăng nhập chưa
  isLoggedIn(): boolean {
    const token = localStorage.getItem('token'); // Hoặc lấy từ user-service
    return !!token; // Trả về true nếu có token, false nếu không
  }

  // 2. Kiểm tra có phải Admin không (Dùng cho Admin Guard)
  isAdmin(): boolean {
    const userStr = localStorage.getItem('user'); // Giả sử bạn lưu info user ở đây
    if (userStr) {
      const user = JSON.parse(userStr);
      return user.role === 'admin'; // Khớp với field 'role' trong PHP của bạn
    }
    return false;
  }

  //kiểm tra có phải nhân viên không
  isEmployee(): boolean {
    const userStr = localStorage.getItem('user');
    if (userStr) {
      const user = JSON.parse(userStr);
      //biến user thành chữ thường hết
      console.log(user.role.toLowerCase());
      return user.role.toLowerCase() === 'employer';
    }
    return false;
  }

  isCustomer(): boolean {
    const userStr = localStorage.getItem('user');
    if (userStr) {
      const user = JSON.parse(userStr);
      return user.role.toLowerCase() === 'customer';
    }
    return false;
  }
}

import { HttpInterceptorFn } from '@angular/common/http';

export const AuthInterceptor: HttpInterceptorFn = (req, next) => {
 
  const token = localStorage.getItem('token');

  if (token) {
    console.log(token);
    req = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      },
       withCredentials: true
    });
  }

  return next(req);
};

import { Component, CUSTOM_ELEMENTS_SCHEMA, inject, signal } from '@angular/core';
import { NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { filter } from 'rxjs';
import { Navbar } from "./layout/navbar/navbar";
import { Footer } from "./layout/footer/footer";

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, Navbar, Footer],
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
  template: `
    

    @if (show3D()) {
      <div class="spline-wrapper" [class.loaded]="loaded">
<spline-viewer url="https://prod.spline.design/9XJan4ZRC51ttwex/scene.splinecode"></spline-viewer>   
      </div>}



    @if (!show3D()) {
      <app-navbar ></app-navbar>
        <div style="min-height: 80vh; padding-top: 80px;">
          <router-outlet></router-outlet>
        </div>
      <app-footer></app-footer>

        
    }
  `,
  // CSS giữ nguyên để định vị trí
  styles: [`
    .spline-wrapper {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100vh;
      z-index: 0;
      pointer-events: none;
      background-color: #000;
       opacity: 0;
  transform: scale(0.96);
  transition: all 0.8s ease;
    }


.spline-wrapper.loaded {
  opacity: 1;
  transform: scale(1);
}

    spline-viewer { pointer-events: auto; }
    .main-content { position: relative; z-index: 10; min-height: 100vh; }
  `]
})
export class App {
  private router = inject(Router);
  loaded = true;
  // Logic Signal của bạn đã chuẩn rồi, giữ nguyên
  show3D = signal<boolean>(true);

  constructor() {
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe((event: any) => {
      const url = event.urlAfterRedirects;

      // Logic cũ của bạn đã hoạt động tốt
      if (url === '/') {
        this.show3D.set(true);
      } else {
        this.show3D.set(false); // Khi vào /login, cái này chạy -> @if sẽ xóa 3D ngay
      }
    });
  }
  //kiểm tra có đang ở trang chủ không

}
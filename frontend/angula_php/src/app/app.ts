import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { Navbar } from './layout/navbar/navbar';
import { Footer } from './layout/footer/footer';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, Navbar, Footer],
  template: `
    <app-navbar></app-navbar>

    <main class="main-content">
      <router-outlet></router-outlet>
    </main>

    <app-footer></app-footer>
  `,
  styles: [`
    .main-content {
      min-height: 100vh;
    }
  `]
})
export class App {}

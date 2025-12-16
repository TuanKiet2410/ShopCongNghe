import { Component, computed, effect, inject, OnInit } from '@angular/core';
import { RouterLinkActive, RouterLinkWithHref } from '@angular/router';
import { AuthService } from '../../services/auth/auth-service';

@Component({
  selector: 'app-navbar',
  imports: [RouterLinkActive, RouterLinkWithHref],
  templateUrl: './navbar.html',
  styleUrl: './navbar.css',
})
export class Navbar implements OnInit {
 authService=inject(AuthService);
 user=computed(() => this.authService.userName());
 constructor() {
this.authService.initUserFromStorage();
 }
 ngOnInit() {
   console.log("sdfsfd");
  
  console.log(this.user());};
}

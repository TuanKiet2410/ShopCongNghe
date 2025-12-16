import { Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home';
import { ProductListComponent } from './pages/product-list/product-list';
import { PromotionsComponent } from './pages/promotions/promotions';
import { CartComponent } from './pages/cart/cart';
import { CheckoutComponent } from './pages/checkout/checkout';
import { DashboardComponent } from './admin/dashboard/dashboard';
import { UserManagerComponent } from './admin/user-manager/user-manager';
import { ProductManagerComponent } from './admin/product-manager/product-manager';
import { BannerManagerComponent } from './admin/banner-manager/banner-manager';
import { OrderManagerComponent } from './admin/order-manager/order-manager';
import { RoleManagerComponent } from './admin/role-manager/role-manager';
import { LoginComponent } from './auth/login/login';
import { RegisterComponent } from './auth/register/register';
import { ForgotPasswordComponent } from './auth/forgot-password/forgot-password';

export const routes: Routes = [
    { path: '', component: HomeComponent },
    { path: 'products', component: ProductListComponent },
    { path: 'promotions', component: PromotionsComponent },
    { path: 'cart', component: CartComponent },
    { path: 'checkout', component: CheckoutComponent },
    {
    path: 'admin',
    component: DashboardComponent, // Component cha chứa Menu
    children: [
      { path: '', redirectTo: 'users', pathMatch: 'full' }, // Mặc định vào User
      { path: 'users', component: UserManagerComponent },
      { path: 'products', component: ProductManagerComponent },
      { path: 'banners', component: BannerManagerComponent },
      { path: 'orders', component: OrderManagerComponent },
      { path: 'roles', component: RoleManagerComponent },
    ]
  },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'forgot-password', component: ForgotPasswordComponent },
];

import { Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home';
import { ProductListComponent } from './pages/product-list/product-list';
import { PromotionsComponent } from './pages/promotions/promotions';
import { CartComponent } from './pages/cart/cart';
import { CheckoutComponent } from './pages/checkout/checkout';
import { DashboardComponent } from './admin/dashboard/dashboard';
import { UserManagerComponent } from './admin/user-manager/user-manager';
import { ProductManagerComponent } from './admin/product-manager/product-manager';
import { OrderManagerComponent } from './admin/order-manager/order-manager';
import { LoginComponent } from './auth/login/login';
import { RegisterComponent } from './auth/register/register';
import { ForgotPasswordComponent } from './auth/forgot-password/forgot-password';
import { Permissionmanager } from './admin/permissionmanager/permissionmanager';
import { authGuard } from './guards/auth-guard';
import { adminGuard } from './guards/admin-guard';
import { buyGuard } from './guards/buy-guard';
import { Promotionmanager } from './admin/promotionmanager/promotionmanager';

export const routes: Routes = [
  //path mặc định
    {path: '', redirectTo: 'home', pathMatch: 'full'},
    { path: 'home', component: HomeComponent },
    { path: 'products', component: ProductListComponent },
    { path: 'promotions', component: PromotionsComponent },
    { path: 'cart', component: CartComponent },
    { path: 'checkout',
       component: CheckoutComponent,
       canActivate: [buyGuard],},
    {
    path: 'admin',
    component: DashboardComponent, // Component cha chứa Menu
    children: [
      { path: '', redirectTo: 'users', pathMatch: 'full' }, // Mặc định vào User
      { path: 'users', component: UserManagerComponent },
      { path: 'products', component: ProductManagerComponent },
      { path: 'orders', component: OrderManagerComponent },
      { path: 'permissions', component: Permissionmanager },
      { path: 'voucher', component: Promotionmanager },


    ],
    canActivate: [authGuard,adminGuard],
  },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'forgot-password', component: ForgotPasswordComponent },
];

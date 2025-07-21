import { Routes } from '@angular/router';
import { Register } from './register/register';
import { Login } from './login/login';
import { ResendVerification } from './resend-verification/resend-verification';
import { Home } from './home/home';
import { Catalog } from './catalog/catalog';

export const routes: Routes = [
    { path: '', component: Home },
    { path: 'register', component: Register },
    { path: 'login', component: Login },
    { path: 'resend-verification', component: ResendVerification },
    { path: 'catalog', component: Catalog }
];

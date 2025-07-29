import { Routes } from '@angular/router';
import { Register } from './register/register';
import { Login } from './login/login';
import { ResendVerification } from './resend-verification/resend-verification';
import { Home } from './home/home';
import { Catalog } from './catalog/catalog';
import { UserLessons } from './user-lessons/user-lessons';
import { Lesson } from './lesson/lesson';

export const routes: Routes = [
    { path: '', component: Home },
    { path: 'register', component: Register },
    { path: 'login', component: Login },
    { path: 'resend-verification', component: ResendVerification },
    { path: 'catalog', component: Catalog },
    { path: 'user-lessons', component: UserLessons },
    { path: 'lesson/:id', component: Lesson }

];

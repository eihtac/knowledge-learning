import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, BehaviorSubject } from 'rxjs';
import { environment } from '../environments/environment';

@Injectable({
    providedIn: 'root'
})
export class Auth {
    private apiUrl = environment.apiUrl;

    private isLoggedInSubject = new BehaviorSubject<boolean>(this.hasToken());
    isLoggedIn$ = this.isLoggedInSubject.asObservable();

    constructor(private http: HttpClient) {}

    private hasToken(): boolean {
        return !!sessionStorage.getItem('token');
    }

    register(data: { name: string; email: string; password: string }): Observable<any> {
        return this.http.post(`${this.apiUrl}/register`, data);
    }

    login(data: { email: string; password: string }): Observable<any> {
        return this.http.post(`${this.apiUrl}/login_check`, data, { headers: { 'Content-Type': 'application/json' } });
    }

    logout(): void  {
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('user');
        this.isLoggedInSubject.next(false);
    }

    notifyLogin(): void {
        this.isLoggedInSubject.next(true);
    }

    getUser(): any {
        const token = sessionStorage.getItem('token');
        if (!token) return null;

        const payload = token.split('.')[1];
        try {
            const decoded = JSON.parse(atob(payload));
            const now = Math.floor(Date.now() / 1000);

            if (decoded.exp && decoded.exp < now) {
                this.logout();
                return null;
            }

            sessionStorage.setItem('user', JSON.stringify(decoded));
            return decoded;
        } catch (error) {
            return null;
        }
    }

    isVerified(): boolean {
        const user = this.getUser();
        return user?.isVerified ?? false;
    }
}
import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, BehaviorSubject } from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class Auth {
    private apiUrl = 'http://localhost:8000/api';

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
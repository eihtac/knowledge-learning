import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LessonsService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getUserLessons(): Observable<any[]> {
    const token = sessionStorage.getItem('token');

    return this.http.get<any[]>(`${this.apiUrl}/user/lessons`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
  }

  getLessonById(id: number): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.get<any>(`${this.apiUrl}/user/lesson/${id}`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
  }

  setLessonCompleted(id: number): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.post<any>(`${this.apiUrl}/user/lesson/${id}/complete`, {}, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
  }
}

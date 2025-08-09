import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Topic } from '../models/topic';
import { Course } from '../models/course';
import { Lesson } from '../models/lesson';
import { User } from '../models/user';
import { Purchase } from '../models/purchase';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  private getAuthHeaders(): HttpHeaders {
    const token = sessionStorage.getItem('token');

    return new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    });
  }

  getAllLessons(): Observable<Topic[]> {
    return this.http.get<Topic[]>(`${this.apiUrl}/admin/lessons`, { 
      headers: this.getAuthHeaders()
    });
  }

  updateTopic(id: number, data: Pick<Topic, 'name'>): Observable<Topic> {
    return this.http.put<Topic>(`${this.apiUrl}/admin/topic/${id}`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  updateCourse(id: number, data: Pick<Course, 'title' | 'price'> & { topicId: number }): Observable<Course> {
    return this.http.put<Course>(`${this.apiUrl}/admin/course/${id}`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  updateLesson(id: number, data: Lesson & {courseId: number }): Observable<Lesson> {
    return this.http.put<Lesson>(`${this.apiUrl}/admin/lesson/${id}`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  deleteTopic(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/admin/topic/${id}`, { 
      headers: this.getAuthHeaders()
    });
  }

  deleteCourse(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/admin/course/${id}`, { 
      headers: this.getAuthHeaders()
    });
  }

  deleteLesson(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/admin/lesson/${id}`, { 
      headers: this.getAuthHeaders()
    });
  }

  addTopic(data: Pick<Topic, 'name'>): Observable<any> {
    return this.http.post(`${this.apiUrl}/admin/topic`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  addCourse(data: Pick<Course, 'title' | 'price'> & { topicId: number }): Observable<any> {
    return this.http.post(`${this.apiUrl}/admin/course`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  addLesson(data: Pick<Lesson, 'title' | 'price' | 'content' | 'videoUrl'> & { courseId: number }): Observable<any> {
    return this.http.post(`${this.apiUrl}/admin/lesson`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  getAllUsers(): Observable<User[]> {
    return this.http.get<User[]>(`${this.apiUrl}/admin/users`, { 
      headers: this.getAuthHeaders()
    });
  }

  addUser(data: { name: string; email: string; password: string; roles: string[] }): Observable<any> {
    return this.http.post(`${this.apiUrl}/admin/user`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  updateUser(id: number, data: { name: string; email: string; roles: string[]; isVerified: boolean; password?: string }): Observable<User> {
    return this.http.put<User>(`${this.apiUrl}/admin/user/${id}`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  deleteUser(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/admin/user/${id}`, { 
      headers: this.getAuthHeaders()
    });
  }

  getAllPurchases(): Observable<Purchase[]> {
    return this.http.get<Purchase[]>(`${this.apiUrl}/admin/purchases`, { 
      headers: this.getAuthHeaders()
    });
  }

  addPurchase(data: { customerId: number; lessonId?: number; courseId?: number }): Observable<any> {
    return this.http.post(`${this.apiUrl}/admin/purchase`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  updatePurchase(id: number, data: { customerId: number; lessonId?: number | null; courseId?: number | null }): Observable<Purchase> {
    return this.http.put<Purchase>(`${this.apiUrl}/admin/purchase/${id}`, data, { 
      headers: this.getAuthHeaders()
    });
  }

  deletePurchase(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/admin/purchase/${id}`, { 
      headers: this.getAuthHeaders()
    });
  }
}

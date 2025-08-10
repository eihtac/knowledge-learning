import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Topic } from '../models/topic';
import { environment } from '../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class CatalogService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getCatalog(topicToOpen: string | null = null): Observable<Topic[]> {
    const url = topicToOpen ? `${this.apiUrl}?topic=${encodeURIComponent(topicToOpen)}` : `${this.apiUrl}/catalog`;
    return this.http.get<Topic[]>(url);
  }

  createPaymentSession(type: 'lesson' | 'course', id: number) {
    const token = sessionStorage.getItem('token');
    return this.http.post<{ id: string; url: string }>(
      `${this.apiUrl}/payment/${type}/${id}`, 
      {}, 
      {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    );
  }

  confirmPurchase(type: string, id: number) {
    const token = sessionStorage.getItem('token');
    return this.http.post<{ message: string }>(
      `${this.apiUrl}/payment/confirm`, 
      { type, id }, 
      {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    );
  }
}

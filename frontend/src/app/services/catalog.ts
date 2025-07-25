import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Topic } from '../models/topic';

@Injectable({
  providedIn: 'root'
})
export class CatalogService {
  private apiUrl = 'http://localhost:8000/api/catalog';

  constructor(private http: HttpClient) {}

  getCatalog(topicToOpen: string | null = null): Observable<Topic[]> {
    const url = topicToOpen ? `${this.apiUrl}?topic=${encodeURIComponent(topicToOpen)}` : this.apiUrl;
    return this.http.get<Topic[]>(url);
  }

  createPaymentSession(type: 'lesson' | 'course', id: number) {
    const token = sessionStorage.getItem('token');
    return this.http.post<{ id: string; url: string }>(
      `http://localhost:8000/api/payment/${type}/${id}`, 
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
      'http://localhost:8000/api/payment/confirm', 
      { type, id }, 
      {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    );
  }
}

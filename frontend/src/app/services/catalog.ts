import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
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
}

import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Certificate } from '../models/certificate';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-certificates',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './certificates.html',
  styleUrl: './certificates.scss'
})
export class Certificates implements OnInit {
  certificates: Certificate[] = [];
  openedTopic: string | null = null;
  openedCourse: string | null = null;
  errorMessage = '';
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    const token = sessionStorage.getItem('token');

    if (!token) {
      this.errorMessage = 'Vous devez être connecté pour voir vos certifications';
      return;
    }

    this.http.get<Certificate[]>(`${this.apiUrl}/user/certificates`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    }).subscribe({
      next: (data) => {
        this.certificates = data;
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les certifications';
      }
    });
  }

  toggleTopic(topic: string) {
    this.openedTopic = this.openedTopic === topic ? null : topic;
    this.openedCourse = null;
  }

  toggleCourse(course: string) {
    this.openedCourse = this.openedCourse === course ? null : course;
  }
}

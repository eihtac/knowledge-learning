import { Component, OnInit } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { LessonsService } from '../services/lessons';
import { CommonModule } from '@angular/common';
import { Auth } from '../services/auth';

@Component({
  selector: 'app-user-lessons',
  standalone: true,
  imports: [ RouterModule, CommonModule ],
  templateUrl: './user-lessons.html',
  styleUrl: './user-lessons.scss'
})
export class UserLessons implements OnInit {
  data: any[] = [];
  openedTopic: string | null = null;
  openedCourse: string | null = null;
  errorMessage: string = '';
  user: any = null;

  constructor(private lessonsService: LessonsService, private auth: Auth, private router: Router) {}

  ngOnInit(): void {
    const token = sessionStorage.getItem('token');

    if (!token) {
      this.errorMessage = 'Vous devez être connecté pour voir vos leçons';
      return;
    }

    const tokenUser = this.auth.getUser();

    if (tokenUser && typeof tokenUser === 'object') {
      this.user = tokenUser;
    }

    this.lessonsService.getUserLessons().subscribe({
      next: (res: any[]) => this.data = res,
      error: () => { this.errorMessage = 'Impossible de charger les leçons' }
    });
  }

  toggleTopic(topic: string) {
    this.openedTopic = this.openedTopic === topic ? null : topic;
    this.openedCourse = null;
  }

  toggleCourse(course: string) {
    this.openedCourse = this.openedCourse === course ? null : course;
  }

  openLesson(id: number) {
    this.router.navigate(['/lesson', id]);
  }
}

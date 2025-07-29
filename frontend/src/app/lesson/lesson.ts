import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { LessonsService } from '../services/lessons';
import { CommonModule } from '@angular/common';
import { SafeUrlPipe } from '../pipes/safe-url.pipe';

@Component({
  selector: 'app-lesson',
  standalone: true,
  imports: [CommonModule, RouterModule, SafeUrlPipe],
  templateUrl: './lesson.html',
  styleUrl: './lesson.scss'
})
export class Lesson implements OnInit {
  lesson: any = null;
  errorMessage: string = '';

  constructor(private route: ActivatedRoute, private lessonsService: LessonsService) {}

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');

    if (!id) {
      this.errorMessage = 'ID de leçon manquant';
      return;
    }

    this.lessonsService.getLessonById(+id).subscribe({
      next: (res: any) => {
        this.lesson = res;
      }, 
      error: () => {
        this.errorMessage = 'Impossible de charger la leçon';
      }
    });
  }

  setCompleted(): void {
    if (!this.lesson || this.lesson.completed) return;

    this.lessonsService.setLessonCompleted(this.lesson.id).subscribe({
      next: () => this.lesson.completed = true,
      error: () => this.errorMessage = 'Erreur lors de la validation de la leçon'
    });
  }

  convertToEmbedUrl(youtubeUrl: string): string {
    const match = youtubeUrl.match(/(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?v=([^&]+)/);
    const videoId = match ? match[1] : '';
    return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
  }
}

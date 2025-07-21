import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { FlashMessage } from '../services/flash-message';
import { Router } from '@angular/router';
import { CatalogService } from '../services/catalog';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { Topic } from '../models/topic';

@Component({
  selector: 'app-home',
  standalone: true,
  templateUrl: './home.html',
  styleUrl: './home.scss',
  imports: [CommonModule, RouterModule]
})
export class Home implements OnInit {
  message: string | null = null;
  topics: Topic[] = [];

  constructor(private flashMessage: FlashMessage, private catalogService: CatalogService, private router: Router, private changeDetector: ChangeDetectorRef) {}

  ngOnInit(): void {
    this.message = this.flashMessage.getMessage();

    this.catalogService.getCatalog().subscribe((data: Topic[]) => {
      this.topics = data;
    });
    
    if (this.message) {
      setTimeout(() => { this.message = null; this.changeDetector.detectChanges() }, 3000);
    }
  }

  goToTopic(topic: Topic): void {
    this.router.navigate(['/catalog'], { queryParams: { topic: topic.name } });
  }
}

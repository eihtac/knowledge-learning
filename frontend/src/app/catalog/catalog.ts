import { Component, OnInit } from '@angular/core';
import { CatalogService } from '../services/catalog';
import { Auth } from '../services/auth';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { Topic } from '../models/topic';
import { Course } from '../models/course';
import { Lesson } from '../models/lesson';

@Component({
  selector: 'app-catalog',
  standalone: true,
  templateUrl: './catalog.html',
  styleUrl: './catalog.scss',
  imports: [CommonModule, RouterModule]
})
export class Catalog implements OnInit {
  catalog: Topic[] = [];
  user: any = null;
  isLoggedIn: boolean = false;
  isVerified: boolean = false;

  constructor(private catalogService: CatalogService, private auth: Auth, private route: ActivatedRoute) {}

  ngOnInit(): void {
    const tokenUser = this.auth.getUser();
    if (tokenUser && typeof tokenUser === 'object') {
      this.user = tokenUser;
      this.isLoggedIn = true;
      this.isVerified = !!tokenUser.isVerified;
    }

    const topicToOpen = this.route.snapshot.queryParamMap.get('topic');

    this.catalogService.getCatalog().subscribe((data: any[]) => {
      const topicToOpen = this.route.snapshot.queryParamMap.get('topic');

      this.catalog = data.map((topic: any) => {
        const open = topicToOpen && topic.name === topicToOpen;
        return {
          ...topic, 
          open, 
          courses: topic.courses.map((course: any) => ({
            ...course,
            open
          }))
        };
      });
    });
  }
}

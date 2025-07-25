import { Component, OnInit } from '@angular/core';
import { CatalogService } from '../services/catalog';
import { Auth } from '../services/auth';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { Topic } from '../models/topic';

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
  errorMessage: string = '';
  successMessage: string = '';

  constructor(private catalogService: CatalogService, private auth: Auth, private route: ActivatedRoute) {}

  ngOnInit(): void {
    const tokenUser = this.auth.getUser();
    if (tokenUser && typeof tokenUser === 'object') {
      this.user = tokenUser;
      this.isLoggedIn = true;
      this.isVerified = !!tokenUser.isVerified;
    }

    const topicToOpen = this.route.snapshot.queryParamMap.get('topic');
    const paymentStatus = this.route.snapshot.queryParamMap.get('payment');
    const type = this.route.snapshot.queryParamMap.get('type');
    const id = this.route.snapshot.queryParamMap.get('id');

    if (paymentStatus === 'cancel') {
      this.errorMessage = 'Le paiement a été annulé.';
      setTimeout(() => {
        this.errorMessage = '';
      }, 5000);
    }

    if (paymentStatus === 'success' && type && id) {
      this.catalogService.confirmPurchase(type, +id).subscribe({
        next: () => {
          this.successMessage = 'Paiement réussi !';
          setTimeout(() => {
            this.successMessage = '';
          }, 5000);
        }, 
        error: () => {
          this.errorMessage = 'Erreur lors du paiement';
          setTimeout(() => {
            this.errorMessage = '';
          }, 5000);
        }
      });
    }

    this.catalogService.getCatalog().subscribe((data: any[]) => {
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

  onBuy(type: 'lesson' | 'course', id: number): void {
    this.catalogService.createPaymentSession(type, id).subscribe({
      next: (res: { id: string; url: string }) => {
        window.location.href = res.url;
      }, 
      error: (err: any) => {
        alert('Une erreur est survenue pendant le paiement.');
      }
    });
  }
}
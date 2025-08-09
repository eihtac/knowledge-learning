import { Component, OnInit } from '@angular/core';
import { Auth } from '../services/auth';
import { Router, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './header.html',
  styleUrl: './header.scss'
})
export class Header implements OnInit {
  user: any = null;

  constructor(private auth: Auth, private router: Router) {}

  ngOnInit(): void {
    this.auth.isLoggedIn$.subscribe(() => {
      this.user = this.auth.getUser();
    });
  }

  logout(): void {
    this.auth.logout();
    this.router.navigate(['/login']);
  }
}

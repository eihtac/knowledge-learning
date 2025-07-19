import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Auth } from '../services/auth';
import { Router, ActivatedRoute, RouterModule } from '@angular/router';
import { FlashMessage } from '../services/flash-message';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class Login implements OnInit {
  loginForm: FormGroup;
  errorMessage: string = '';
  successMessage: string = '';

  constructor(private fb: FormBuilder, private auth: Auth,private flashMessage: FlashMessage, private router: Router, private route: ActivatedRoute) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required],
    });
  }

  ngOnInit(): void {
    const message = this.flashMessage.getMessage();
    if (message) {
      this.successMessage = message;
      setTimeout(() => this.successMessage = '', 5000);
    }

    this.route.queryParams.subscribe(params => {
      const msg = params['message'];
      const err = params['error'];

      if (msg) {
        this.successMessage = msg;
        setTimeout(() => this.successMessage = '', 5000);
      }

      if (err) {
        this.errorMessage = err;
        setTimeout(() => this.errorMessage = '', 5000);
      }
    });
  }

  onSubmit(): void {
    if (this.loginForm.invalid) return;

    const { email, password } = this.loginForm.value;

    this.auth.login({ email, password }).subscribe({
      next: (response) => {
        sessionStorage.setItem('token', response.token);
        this.auth.notifyLogin();
        this.flashMessage.setMessage('Connexion réussie !');
        this.router.navigate(['/']);
      }, 
      error: (err) => {
        if (err?.error?.message) {
          this.errorMessage = err.error.message;
        } else {
          this.errorMessage = 'Identifiants incorrects';
        }
      }
    });
  }
}

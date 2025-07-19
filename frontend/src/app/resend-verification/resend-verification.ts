import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-resend-verification',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './resend-verification.html',
  styleUrl: './resend-verification.scss'
})
export class ResendVerification {
  form: FormGroup;
  message: string = '';
  error: string = '';

  constructor(private fb: FormBuilder, private http: HttpClient) {
    this.form = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  onSubmit(): void {
    if (this.form.invalid) return;

    this.http.post('http://localhost:8000/api/resend-verification', this.form.value).subscribe({
      next: (res: any) => {
        this.message = res.message;
        this.error = '';
        this.form.reset();
      },
      error: (err) => {
        this.error = err.error?.error || 'Une erreur est survenue';
        this.message = '';
      }
    });
  }
}

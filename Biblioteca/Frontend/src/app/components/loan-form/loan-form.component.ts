import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { LoanService } from '../../services/loan.service';
import { LoanRequest } from '../../models/library.models';

@Component({
  selector: 'app-loan-form',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './loan-form.component.html',
  styleUrls: ['./loan-form.component.css']
})
export class LoanFormComponent {
  loan: LoanRequest = {
    bookId: 1,
    memberId: 1
  };

  errorMessage: string = '';
  successMessage: string = '';

  constructor(private loanService: LoanService, private router: Router) {}

  onSubmit(): void {
    this.errorMessage = '';
    this.successMessage = '';

    this.loanService.createLoan(this.loan).subscribe({
      next: (res) => {
        this.successMessage = `Préstamo registrado exitosamente. Fecha límite de devolución: ${res.dueDate}`;
        setTimeout(() => this.router.navigate(['/books']), 2500);
      },
      error: (err: any) => {
        if (err.error && err.error.detail) {
          this.errorMessage = err.error.detail;
        } else {
          this.errorMessage = 'Ocurrió un error al procesar el préstamo.';
        }
      }
    });
  }
}
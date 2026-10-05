import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { BookService } from '../../services/book.service';
import { Book } from '../../models/library.model';

@Component({
  selector: 'app-book-form',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './book-form.component.html',
  styleUrls: ['./book-form.component.css']
})
export class BookFormComponent {
  book: Book = {
    isbn: '',
    title: '',
    authorId: 1,
    totalCopies: 1
  };

  errorMessage: string = '';
  successMessage: string = '';

  constructor(private bookService: BookService, private router: Router) {}

  onSubmit(): void {
    this.errorMessage = '';
    this.successMessage = '';

    this.bookService.createBook(this.book).subscribe({
      next: () => {
        this.successMessage = 'Libro registrado exitosamente.';
        setTimeout(() => this.router.navigate(['/books']), 1500);
      },
      error: (err) => {
        this.errorMessage = err.error?.detail || 'Error al guardar el libro.';
      }
    });
  }
}
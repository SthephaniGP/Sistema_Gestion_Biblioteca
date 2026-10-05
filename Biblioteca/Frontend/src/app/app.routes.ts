import { Routes } from '@angular/router';
import { BookListComponent } from './components/book-list/book-list.component';
import { BookFormComponent } from './components/book-form/book-form.component';
import { LoanFormComponent } from './components/loan-form/loan-form.component';

export class AppRoutes {}

export class AppConfigRoutes {
  static routes: Routes = [
    { path: '', redirectTo: 'books', pathMatch: 'full' },
    { path: 'books', component: BookListComponent },
    { path: 'books/new', component: BookFormComponent },
    { path: 'loans/new', component: LoanFormComponent },
    { path: '**', redirectTo: 'books' }
  ];
}
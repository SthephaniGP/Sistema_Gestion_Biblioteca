import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Book } from '../models/library.models';

@Injectable({
  providedIn: 'root'
})
export class BookService {
  private readonly apiUrl = 'http://localhost:8000/api/v1/books';

  constructor(private http: HttpClient) {}

  getBooks(title?: string, authorId?: number, available?: boolean): Observable<Book[]> {
    let params = new HttpParams();
    if (title) params = params.set('title', title);
    if (authorId) params = params.set('authorId', authorId.toString());
    if (available !== undefined) params = params.set('available', available.toString());

    return this.http.get<Book[]>(this.apiUrl, { params });
  }

  createBook(book: Book): Observable<Book> {
    return this.http.post<Book>(this.apiUrl, book);
  }
}
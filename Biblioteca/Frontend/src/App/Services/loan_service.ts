import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { LoanRequest, LoanResponse } from '../models/library.model';

@Injectable({
  providedIn: 'root'
})
export class LoanService {
  private readonly apiUrl = 'http://localhost:8000/api/v1/loans';

  constructor(private http: HttpClient) {}

  createLoan(loan: LoanRequest): Observable<LoanResponse> {
    return this.http.post<LoanResponse>(this.apiUrl, loan);
  }
}
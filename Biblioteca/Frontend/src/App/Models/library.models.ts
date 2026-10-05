export interface Book {
  id?: number;
  isbn: string;
  title: string;
  authorId: number;
  totalCopies: number;
  availableCopies?: number;
}

export interface LoanRequest {
  bookId: number;
  memberId: number;
}

export interface LoanResponse {
  id: number;
  bookId: number;
  memberId: number;
  loanDate: string;
  dueDate: string;
  status: string;
}

export interface ApiError {
  title: string;
  status: number;
  detail: string;
}
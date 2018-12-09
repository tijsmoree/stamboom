import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { User } from '../all.model';

@Injectable()
export class NavbarService {

  name: string;
  admin: number | boolean;

  constructor (
    private http: HttpClient
  ) {
    this.reset();
  }

  reset = (): void => {
    this.name = '';
    this.admin = false;
  }

  set = (): void => {
    if (!this.name) {
      this.http.get('/api/users/info').subscribe(
        (response: User): void => {
          this.name = response.person.first_name;
          this.admin = response.admin;
        }
      );
    }
  }
}

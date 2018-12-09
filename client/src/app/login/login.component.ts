import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { NotifierService } from 'angular-notifier';
import { HttpClient } from '@angular/common/http';
import { User } from '../all.model';
import { Title } from '@angular/platform-browser';
import { CookieService } from 'ngx-cookie-service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {

  credentials: User;
  wrong: boolean;

  constructor (
    private router: Router,
    private notifier: NotifierService,
    private http: HttpClient,
    private titleService: Title,
    private cookie: CookieService
  ) { }

  ngOnInit (): void {
    this.titleService.setTitle('Stamboom');

    this.credentials = {
      mail: '',
      password: ''
    };

    this.wrong = false;
  }

  login = (): void => {
    if (!this.credentials.mail || !this.credentials.password) {
      this.notifier.notify('warning', 'Je moet beide velden invullen.');
      return;
    }

    this.http.post('/api/users/login', this.credentials).subscribe(
      (response: any): void => {
        if (response.status === 203) {
            this.notifier.notify('error', 'De inloggegevens waren onjuist of je hebt het te vaak geprobeerd. Vraag een admin om hulp.');
            this.wrong = true;
        } else {
          this.cookie.set('token', response.token);
          this.router.navigate(['']);
          this.notifier.notify('info', `Welkom, ${response.name}!`);
        }
      }
    );
  }
}

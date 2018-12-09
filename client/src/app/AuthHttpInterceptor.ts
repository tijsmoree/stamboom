import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { NotifierService } from 'angular-notifier';
import { CookieService } from 'ngx-cookie-service';
import { environment } from 'src/environments/environment';

@Injectable()
export class AuthHttpInterceptor implements HttpInterceptor {

  constructor (
    private router: Router,
    private notifier: NotifierService,
    private cookie: CookieService
  ) { }

  intercept = (request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> => {
    if (this.cookie.check('token')) {
      request = request.clone({
        setHeaders: {
          Authorization: this.cookie.get('token')
        }
      });
    }

    if (!environment.production && request.url.slice(0, 3) === 'api') {
      console.error('An api call should start with a slash...');
    }

    request = request.clone({
      url: request.url.replace('/api/', environment.apiUrl),
    });

    return next.handle(request).pipe(
      tap(
        (): void => {},
        (err: any): void => {
          if (err instanceof HttpErrorResponse) {
            if (err.status === 401) {
              this.router.navigate(['login']);
            } else if (err.status === 403) {
              this.notifier.notify('warning', 'Je mag hier niet komen...');
              this.router.navigate(['']);
            } else {
              this.notifier.notify('error', 'Er is iets misgegaan op de server...');
            }
          }
        }
      )
    );
  }
}

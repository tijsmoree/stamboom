import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router, Event, NavigationEnd } from '@angular/router';
import { NotifierService } from 'angular-notifier';
import { takeUntil } from 'rxjs/operators';
import { Subject } from 'rxjs';
import { NavbarService } from './navbar.service';
import { CookieService } from 'ngx-cookie-service';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html'
})
export class NavbarComponent implements OnInit, OnDestroy {

  navbarOpen = false;

  page: string;

  private unsubscribeAll: Subject<any>;

  constructor (
    private router: Router,
    private notifier: NotifierService,
    private cookie: CookieService,
    public nav: NavbarService
  ) {
    this.unsubscribeAll = new Subject();
  }

  ngOnInit (): void {
    this.router.events.pipe(takeUntil(this.unsubscribeAll)).subscribe(
      (event: Event): void => {
        if (event instanceof NavigationEnd) {
          this.navbarOpen = false;

          this.page = event.url.split('/')[1].replace(/s$/, '');

          if (this.page === 'login') {
            this.nav.reset();
          } else {
            this.nav.set();
          }
        }
      }
    );
  }

  ngOnDestroy (): void {
    this.unsubscribeAll.next();
    this.unsubscribeAll.complete();
  }

  toggleNavbar = (): void => {
    this.navbarOpen = !this.navbarOpen;
  }

  logout = (): void => {
    this.cookie.delete('token');
    this.router.navigate(['login']);
    this.notifier.notify('info', `Tot de volgende keer, ${this.nav.name}`);
  }
}

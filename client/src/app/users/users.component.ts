import { Component, OnInit, OnDestroy, ViewChild, HostListener } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Title } from '@angular/platform-browser';
import { Router, ActivatedRoute, ParamMap } from '@angular/router';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { NotifierService } from 'angular-notifier';
import { NgForm } from '@angular/forms';
import { User, Person } from '../all.model';
import { UtilsService } from '../utils.service';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.css']
})
export class UsersComponent implements OnInit, OnDestroy {

  @ViewChild('userForm') form: NgForm;

  users: User[];
  people: Person[];

  user: User;

  subTitle: string;

  private unsubscribeAll: Subject<any>;

  constructor (
    private route: ActivatedRoute,
    private http: HttpClient,
    private titleService: Title,
    private router: Router,
    private notifier: NotifierService,
    public utils: UtilsService
  ) {
    this.unsubscribeAll = new Subject();
  }

  ngOnInit (): void {
    this.http.get('/api/users').subscribe(
      (data: User[]): void => {
        this.users = data;
      }
    );

    this.http.get('/api/persons').subscribe(
      (data: Person[]): void => {
        this.people = data;
      }
    );

    this.loadUsers();

    this.route.paramMap
      .pipe(takeUntil(this.unsubscribeAll)).subscribe(
        (paramsMap: ParamMap): void => {
          if (paramsMap.get('id')) {
            if (paramsMap.get('id') === '0') {
              this.subTitle = null;
              this.form.control.markAsDirty();
              this.user = <User> {};
              this.titleService.setTitle('Gebruikers');
            } else {
              this.userLoad(paramsMap.get('id'));
            }
          } else if (localStorage.getItem('user')) {
            this.router.navigate([`user/${localStorage.getItem('user')}`]);
          } else {
            this.user = null;
            this.titleService.setTitle('Gebruikers');
          }
        }
      );
  }

  ngOnDestroy (): void {
    this.unsubscribeAll.next();
    this.unsubscribeAll.complete();
  }

  @HostListener('window:beforeunload', ['$event'])
  unloadNotification ($event: any): void {
    if (this.form.control.dirty) {
      $event.returnValue = true;
    }
  }

  newUser = (): void => {
    this.router.navigate(['user/0']);
  }

  private loadUsers = (): void => {
    this.http.get('/api/users').subscribe(
      (data: User[]): void => {
        this.users = data;
      }
    );
  }

  searchUser = (term: string, item: User): boolean => {
    const simple = (string: string): string => string.toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    return simple(item.name).includes(simple(term));
  }

  private userLoad = (id: number | string): void => {
    this.form.control.markAsPristine();

    this.http.get(`/api/users/view?id=${id}`).subscribe(
      (data: User): void => {
        localStorage.setItem('user', id.toString());
        this.user = data;
        this.user.repeat = '';
        this.titleService.setTitle(data ? this.utils.name(data.person) : 'Gebruikers');
        this.subTitle = this.utils.name(data.person);
      },
      (): void => {
        this.subTitle = null;
        localStorage.removeItem('user');
        this.router.navigate(['users']);
      }
    );
  }

  userSelect = (selected: User): void => {
    this.router.navigate([`user/${selected.id}`]);
  }

  userSave = (): void => {
    if (!this.form.control.dirty) {
      return;
    }

    if (!this.user.id && !this.user.password) {
      this.notifier.notify('error', 'Geen wachtwoord opgegeven.');
      return;
    }

    if (this.user.password && this.user.password.length < 8) {
      this.notifier.notify('error', 'Het wachtwoord is te kort.');
      return;
    }

    if (this.user.password !== this.user.repeat) {
      this.notifier.notify('error', 'De opgegeven wachtwoorden zijn niet gelijk.');
      return;
    }

    const url = this.user.id
      ? `/api/users/update?id=${this.user.id}`
      : '/api/users/update';
    this.http.post(url, this.user).subscribe(
      (data: User): void => {
        this.form.control.markAsPristine();
        this.notifier.notify('success', 'Wijzigingen opgeslagen.');
        this.user = data;
        this.router.navigate([`user/${data.id}`]);
      }
    );
  }

  userDelete = (): void => {
    if (!confirm(`Zeker weten om ${this.utils.name(this.user.person)} te verwijderen?`)) {
      return;
    }

    this.http.post(`/api/users/delete?id=${this.user.id}`, {}).subscribe(
      (): void => {
        this.notifier.notify('error', `${this.utils.name(this.user.person)} is succesvol verwijderd.`);
        this.form.control.markAsPristine();
        this.subTitle = null;
        localStorage.removeItem('user');
        this.router.navigate(['users']);
      }
    );
  }
}

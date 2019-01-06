import { Component, OnInit, OnDestroy, ViewChild, HostListener } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Title } from '@angular/platform-browser';
import { Router, ActivatedRoute, ParamMap } from '@angular/router';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { Person, Location, Marriage } from '../all.model';
import { NotifierService } from 'angular-notifier';
import { NgForm } from '@angular/forms';
import { UtilsService } from '../utils.service';

@Component({
  selector: 'app-persons',
  templateUrl: './persons.component.html',
  styleUrls: ['./persons.component.css']
})
export class PersonsComponent implements OnInit, OnDestroy {

  @ViewChild('personForm') form: NgForm;

  people: Person[];

  person: Person;

  subTitle: string;

  locations: Location[];

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
    this.http.get('/api/persons').subscribe(
      (data: Person[]): void => {
        this.people = data;
      }
    );

    this.http.get('/api/locations').subscribe(
      (data: Location[]): void => {
        this.locations = data;
      }
    );

    this.route.paramMap
      .pipe(takeUntil(this.unsubscribeAll))
      .subscribe(
        (paramsMap: ParamMap): void => {
          if (paramsMap.get('id')) {
            if (paramsMap.get('id') === '0') {
              this.subTitle = null;
              this.form.control.markAsDirty();
              this.person = <Person> {};
              this.titleService.setTitle('Personen');
            } else {
              this.personLoad(paramsMap.get('id'));
            }
          } else if (localStorage.getItem('person')) {
            this.router.navigate([`person/${localStorage.getItem('person')}`]);
          } else {
            this.person = null;
            this.titleService.setTitle('Personen');
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

  newPerson = (): void => {
    this.router.navigate(['person/0']);
  }

  searchPerson = (term: string, item: Person): boolean => {
    return this.utils.simple(this.utils.name(item)).includes(this.utils.simple(term));
  }

  private personLoad = (id: number | string): void => {
    this.form.control.markAsPristine();

    this.http.get(`/api/persons/view?id=${id}`).subscribe(
      (data: Person): void => {
        localStorage.setItem('person', id.toString());
        this.person = data;
        this.titleService.setTitle(data ? this.utils.name(data) : 'Personen');
        this.subTitle = this.utils.name(data);
        if (data.birth && data.birth.date) {
          this.subTitle += ' (' + this.utils.age(data) + ')';
        }
      },
      (): void => {
        this.subTitle = null;
        localStorage.removeItem('person');
        this.router.navigate(['persons']);
      }
    );
  }

  personSelect = (selected: Person): void => {
    this.router.navigate([`person/${selected.id}`]);
  }

  personSave = (): void => {
    if (!this.form.control.dirty) {
      return;
    }

    const url = this.person.id
      ? `/api/persons/update?id=${this.person.id}`
      : '/api/persons/update';
    this.http.post(url, this.person).subscribe(
      (data: Person): void => {
        this.form.control.markAsPristine();
        this.notifier.notify('success', 'Wijzigingen opgeslagen.');
        this.person = data;
        this.subTitle = this.utils.name(data);
        if (data.birth && data.birth.date) {
          this.subTitle += ' (' + this.utils.age(data) + ')';
        }
        this.router.navigate([`person/${data.id}`]);
      }
    );
  }

  personDelete = (): void => {
    if (!confirm(`Zeker weten om ${this.utils.name(this.person)} te verwijderen?`)) {
      return;
    }

    this.http.post(`/api/persons/delete?id=${this.person.id}`, {}).subscribe(
      (success: boolean): void => {
        if (success) {
          this.notifier.notify('error', `${this.utils.name(this.person)} is succesvol verwijderd.`);
          this.form.control.markAsPristine();
          this.subTitle = null;
          localStorage.removeItem('person');
          this.router.navigate(['persons']);
        } else {
          this.notifier.notify('error', `${this.utils.name(this.person)} kon niet verwijderd worden. Misschien is hij een gebruiker?`);
        }
      }
    );
  }

  marriageDelete = (id: number): void => {
    this.person.marriages = this.person.marriages.filter((m: Marriage) => m.id !== id);
  }
}

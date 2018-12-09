import { Component, OnInit, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Subject } from 'rxjs';
import { Log, Logs, Cud } from '../all.model';
import { NotifierService } from 'angular-notifier';
import { debounceTime, takeUntil } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-logs',
  templateUrl: './logs.component.html',
  styleUrls: ['./logs.component.css']
})
export class LogsComponent implements OnInit, OnDestroy {

  Cud: typeof Cud = Cud;

  logs: Log[];

  currentPage: number;
  cudFilter: Cud;
  searchFilter: string;
  amountLogs: number;
  totalPages: number;

  pageSize: number;
  startPage: number;
  endPage: number;
  startIndex: number;
  endIndex: number;
  pages: number[];

  search: Subject<string>;

  private unsubscribeAll: Subject<any>;

  constructor (
    private http: HttpClient,
    private notifier: NotifierService,
    private titleService: Title
  ) {
    this.unsubscribeAll = new Subject();

    this.search = new Subject<string>();
    this.search.pipe(debounceTime(500), takeUntil(this.unsubscribeAll)).subscribe(
      (search: string): void => {
        this.searchFilter = search;
        this.loadPage();
      }
    );
  }

  ngOnInit (): void {
    this.titleService.setTitle('Logs');

    this.http.get('/api/logs').subscribe(
      (data: Logs): void => {
        this.logs = data.logs;
        this.totalPages = data.pageCount;
        this.amountLogs = data.totalCount;
      }
    );

    this.currentPage = 1;
    this.cudFilter = null;
    this.searchFilter = null;

    this.loadPage();
  }

  ngOnDestroy (): void {
    this.unsubscribeAll.next();
    this.unsubscribeAll.complete();
  }

  loadPage = (page: number = this.currentPage): void => {
    let url = `/api/logs/index?page=${page}&size=10`;
    if (this.cudFilter) {
      url += `&cudFilter=${this.cudFilter}`;
    }
    if (this.searchFilter) {
      url += `&query=${this.searchFilter}`;
    }
    this.http.get(url).subscribe(
      (data: Logs): void => {
        if (data.totalCount === 0) {
          this.amountLogs = data.totalCount;
          return;
        }

        if (page < 1 || page > data.pageCount) {
          return;
        }

        this.logs = data.logs;
        this.totalPages = data.pageCount;
        this.amountLogs = data.totalCount;

        this.currentPage = page;

        if (this.totalPages <= 10) {
          this.startPage = 1;
          this.endPage = this.totalPages;
        } else {
          if (page <= 6) {
            this.startPage = 1;
            this.endPage = 10;
          } else if (page + 4 >= this.totalPages) {
            this.startPage = this.totalPages - 9;
            this.endPage = this.totalPages;
          } else {
            this.startPage = page - 5;
            this.endPage = page + 4;
          }
        }

        this.startIndex = (page - 1) * this.pageSize;
        this.endIndex = Math.min(
          this.startIndex + this.pageSize - 1,
          this.amountLogs - 1
        );

        this.pages = Array(this.endPage - this.startPage + 1).fill(0).map((_, i: number): number => this.startPage + i);
      }
    );
  }

  revert = (log: Log): void => {
    this.http.get(`/api/logs/revert?id=${log.id}`).subscribe(
      (response: boolean): void => {
        if (response) {
          this.notifier.notify('success', 'Succesvol teruggedraaid.');
          this.loadPage();
        } else {
          this.notifier.notify('error', 'Niet gelukt terug te draaien...');
        }
      }
    );
  }

  setFilter = (cud: Cud): void => {
    this.cudFilter === cud
      ? (this.cudFilter = null)
      : (this.cudFilter = cud);

    this.loadPage(1);
  }

  searchLogs = (search: string): void => {
    this.search.next(search);
  }

  searchReset = (): void => {
    this.searchFilter = null;
    this.loadPage();
  }
}

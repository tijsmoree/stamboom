import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NotifierService } from 'angular-notifier';
import { Title } from '@angular/platform-browser';
import 'codemirror/mode/sql/sql.js';

@Component({
  selector: 'app-queries',
  templateUrl: './queries.component.html'
})
export class QueriesComponent implements OnInit {

  info: any;

  query: string;
  results: any;
  error: string;

  codeMirror: any;

  constructor (
    private http: HttpClient,
    private notifier: NotifierService,
    private titleService: Title
  ) { }

  ngOnInit (): void {
    this.titleService.setTitle('Queries');

    this.codeMirror = {
      lineWrapping: true,
      lineNumbers: true,
      tabSize: 2,
      mode: 'text/x-mysql'
    };

    this.http.get('/api/queries/info').subscribe(
      (info: any): void => {
        this.info = info;
      }
    );
  }

  try = (): void => {
    if (!this.query) {
      return;
    }

    const queryFirstWord = this.query.split(' ').filter(w => w !== '')[0].toUpperCase();

    if (queryFirstWord !== 'SELECT' && !confirm(`Zeker weten deze ${queryFirstWord}-query uit te voeren?`)) {
      return;
    }

    this.http.post('/api/queries/try', {query: this.query}).subscribe(
      (result: any): void => {
        this.results = result;
        this.notifier.notify('success', 'De query was succesvol.');
        this.error = '';
      },
      (error: any): void => {
        this.error = error.error.message;
      });
  }

  ctrlQ = (event: any): void => {
    if (event.ctrlKey && event.keyCode === 17) {
      this.try();
    }
  }
}

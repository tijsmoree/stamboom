import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-root',
  template: `
    <app-navbar></app-navbar>
    <router-outlet></router-outlet>
    <notifier-container></notifier-container>
  `
})
export class AppComponent implements OnInit {
  constructor () { }

  ngOnInit (): void { }
}

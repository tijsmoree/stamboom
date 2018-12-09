import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { Routes, RouterModule } from '@angular/router';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgSelectModule } from '@ng-select/ng-select';
import { NotifierModule } from 'angular-notifier';
import { CodemirrorModule } from '@nomadreservations/ngx-codemirror';
import { CookieService } from 'ngx-cookie-service';

import { AppComponent } from './app.component';
import { NavbarComponent } from './navbar/navbar.component';
import { LoginComponent } from './login/login.component';
import { PersonsComponent } from './persons/persons.component';
import { AuthHttpInterceptor } from './AuthHttpInterceptor';
import { CanDeactivateGuard } from './CanDeactivateGuard';
import { UsersComponent } from './users/users.component';
import { LogsComponent } from './logs/logs.component';
import { QueriesComponent } from './queries/queries.component';
import { NavbarService } from './navbar/navbar.service';
import { ProfileComponent } from './profile/profile.component';
import { UtilsService } from './utils.service';

const appRoutes: Routes = [
  {
    path: '',
    component: PersonsComponent
  },
  {
    path: 'login',
    component: LoginComponent
  },
  {
    path: 'profile',
    component: ProfileComponent,
    canDeactivate: [CanDeactivateGuard]
  },
  {
    path: 'persons',
    component: PersonsComponent,
    canDeactivate: [CanDeactivateGuard]
  },
  {
    path: 'person/:id',
    component: PersonsComponent,
    canDeactivate: [CanDeactivateGuard]
  },
  {
    path: 'users',
    component: UsersComponent,
    canDeactivate: [CanDeactivateGuard]
  },
  {
    path: 'user/:id',
    component: UsersComponent,
    canDeactivate: [CanDeactivateGuard]
  },
  {
    path: 'queries',
    component: QueriesComponent
  },
  {
    path: 'logs',
    component: LogsComponent
  },
  {
    path: '**',
    redirectTo: ''
  }
];

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    LoginComponent,
    ProfileComponent,
    PersonsComponent,
    UsersComponent,
    LogsComponent,
    QueriesComponent
  ],
  imports: [
    BrowserModule,
    NgbModule.forRoot(),
    RouterModule.forRoot(appRoutes, {useHash: true}),
    FormsModule,
    HttpClientModule,
    NgSelectModule,
    NotifierModule.withConfig({behaviour: {
      autoHide: 2000,
      onClick: 'hide',
      onMouseover: 'pauseAutoHide',
      showDismissButton: true,
      stacking: 4
    }}),
    CodemirrorModule
  ],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthHttpInterceptor,
      multi: true
    },
    CanDeactivateGuard,
    NavbarService,
    UtilsService,
    CookieService
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

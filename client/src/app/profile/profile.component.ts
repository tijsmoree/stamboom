import { Component, OnInit, ViewChild, HostListener } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { User } from '../all.model';
import { NotifierService } from 'angular-notifier';
import { NgForm } from '@angular/forms';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html'
})
export class ProfileComponent implements OnInit {

  @ViewChild('profileForm') form: NgForm;

  profile: User;

  constructor (
    private http: HttpClient,
    private notifier: NotifierService,
    private titleService: Title
  ) { }

  ngOnInit (): void {
    this.titleService.setTitle('Profiel');

    this.http.get('/api/users/info').subscribe(
      (data: User): void => {
        this.profile = data;
      }
    );
  }

  @HostListener('window:beforeunload', ['$event'])
  unloadNotification ($event: any): void {
    if (this.form.control.dirty) {
      $event.returnValue = true;
    }
  }

  profileSave = (): void => {
    if (!this.form.control.dirty) {
      return;
    }

    if (this.profile.password && this.profile.password.length < 8) {
      this.notifier.notify('error', 'Het wachtwoord is te kort.');
      return;
    }

    if (this.profile.password !== this.profile.repeat) {
      this.notifier.notify('error', 'De opgegeven wachtwoorden zijn niet gelijk.');
      return;
    }

    this.http.post('/api/users/profile', this.profile).subscribe(
      (): void => {
        this.form.control.markAsPristine();
        this.notifier.notify('success', 'Wijzigingen opgeslagen.');

        delete(this.profile.password);
        delete(this.profile.repeat);
      }
    );
  }
}

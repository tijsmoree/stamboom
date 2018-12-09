import { Injectable } from '@angular/core';
import { CanDeactivate } from '@angular/router';
import { NgForm } from '@angular/forms';

@Injectable()
export class CanDeactivateGuard implements CanDeactivate<{form: NgForm}> {

  canDeactivate = (component: {form: NgForm}): boolean => {
    if (component.form.control.dirty) {
      return confirm('Je hebt wijzigingen gemaakt die nog niet zijn opgeslgen. Toch doorgaan?');
    } else {
      return true;
    }
  }
}

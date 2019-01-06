import {Injectable} from '@angular/core';
import { Person } from './all.model';

@Injectable()
export class UtilsService {

    simple = (string: string): string => string.toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    name(person: Person): string {
      const name: string[] = [];

      if (person.first_name) { name.push(person.first_name); }
      if (person.nickname) { name.push('"' + person.nickname + '"'); }
      if (person.last_name) { name.push(person.last_name); }

      return name.join(' ');
    }

    simpleName(person: Person): string {
      const name: string[] = [];

      if (person.nickname) {
        name.push(person.nickname);
      } else if (person.first_name) {
        name.push(person.first_name);
      }
      if (person.last_name) { name.push(person.last_name); }

      return name.join(' ');
    }

    age(person: Person): number {
      let first: Date;
      let last: Date;
      if (person.birth && person.birth.date) {
        first = new Date(person.birth.date);

        if (person.death && person.death.date) {
          last = new Date(person.death.date);
        } else {
          last = new Date();
        }

        return Math.floor((last.getTime() - first.getTime()) / 1000 / 3600 / 24 / 365);
      }
    }
}

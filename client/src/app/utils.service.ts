import {Injectable} from '@angular/core';
import { Person } from './all.model';

@Injectable()
export class UtilsService {

    simple = (string: string): string => string.toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    name(person: Person): string {
      const name: string[] = [];

      if (person.first_name) { name.push(person.first_name); }
      if (person.nickname) { name.push('"' + person.nickname + '"'); }
      if (person.prefix) { name.push(person.prefix); }
      if (person.last_name) { name.push(person.last_name); }

      return name.join(' ');
    }
}

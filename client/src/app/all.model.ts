export interface Person {
  id: number;
  first_name?: string;
  nickname?: string;
  prefix?: string;
  last_name?: string;
  sex?: 'm' | 'f' | 'u';
  birth?: Moment;
  death?: Moment;
  father?: Person;
  mother?: Person;
  children?: Person[];
  marriage?: Marriage;
}

export interface Moment {
  id: number;
  date?: string;
  location?: Location;
  source?: string;
}

export interface Location {
  id: number;
  name?: string;
  state?: string;
  country?: string;
  longitude?: number;
  latitude?: number;
}

export interface Marriage {
  id: number;
  male?: Person;
  female?: Person;
  marriage?: Moment;
  divorce?: Moment;
}

export interface User {
  id?: number;
  name?: string;
  mail: string;
  password: string;
  repeat?: string;
  admin?: boolean | number;
  person?: Person;
}

export interface Log {
  id: number;
  change_type: Cud;
  changes: {
    key: string;
    old?: any;
    new?: any;
  }[];
  message: string;
  links: {
    name: string;
    links: string;
  }[];
  revertable: boolean;
  displayTime: string;
}

export interface Logs {
  totalCount: number;
  pageCount: number;
  logs: Log[];
}

export enum Cud {
  C = 'create',
  U = 'update',
  D = 'delete'
}

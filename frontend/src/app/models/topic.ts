import { Course } from './course'; 

export interface Topic {
    id: number;
    name: string;
    courses: Course[];
    open?: boolean;
}
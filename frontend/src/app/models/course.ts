import { Lesson } from './lesson';

export interface Course {
    id: number;
    title: string;
    price: number;
    lessons: Lesson[];
    open?: boolean;
}
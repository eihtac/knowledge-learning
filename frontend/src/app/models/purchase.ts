export interface Purchase {
    id: number;
    lesson: {
        id: number;
        title: string;
    } | null;
    course: {
        id: number;
        title: string;
    } | null;
    customer: {
        id: number;
        name: string;
    };
}
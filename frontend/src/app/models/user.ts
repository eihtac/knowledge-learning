export interface User {
    id: number;
    name: string;
    email: string;
    password?: string;
    isVerified: boolean;
    roles: string[];
}
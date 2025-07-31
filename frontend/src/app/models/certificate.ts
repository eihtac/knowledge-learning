export interface Certificate {
    certificateId: number;
    topicTitle: string;
    obtainedAt: string;
    courses: {
        courseTitle: string;
        lessons: string[];
    }[];
}
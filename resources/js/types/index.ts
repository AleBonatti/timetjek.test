export interface User {
    id: number;
    name: string;
    email: string;
    personnummer: string;
    email_verified_at: string | null;
    avatar?: string;
    created_at: string;
    updated_at: string;
}

export interface TimeEntry {
    id: number;
    user_id: number;
    clock_in: string;
    clock_out: string | null;
    latitude: number | null;
    longitude: number | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

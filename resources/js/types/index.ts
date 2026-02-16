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
    clock_in_latitude: number | null;
    clock_in_longitude: number | null;
    clock_out_latitude: number | null;
    clock_out_longitude: number | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

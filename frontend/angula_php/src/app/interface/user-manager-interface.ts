export interface userNameManageInterface {
    id?: number;
    username: string;
    password?: string;
    role?: string;
    permission?: string[];
    is_locked?: number;
    create_at?: string;
}
export interface UserInterface {
    status: string;
    data: {
        user_id: number;
    username: string;
    password: string;
    role: string;
    is_locked: number;
    };
    token: string;
}
export interface User {
    id: number;
    name: string;
    email: string;
}
export interface AuthState {
    token: string | null;
    user: User | null;
    isLoading: boolean;
    error: string | null;
    isInitialized: boolean;
    initialize: () => Promise<void>;
    login: (email: string, password: string, deviceId?: string) => Promise<void>;
    register: (name: string, email: string, password: string, deviceId?: string) => Promise<void>;
    logout: () => void;
    fetchUser: () => Promise<void>;
    refreshToken: () => Promise<void>;
}

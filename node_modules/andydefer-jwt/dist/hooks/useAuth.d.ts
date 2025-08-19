import type { User } from '../types';
interface UseAuthReturn {
    isAuthenticated: boolean;
    user: User | null;
    isLoading: boolean;
    error: string | null;
    login: (email: string, password: string, deviceId?: string) => Promise<void>;
    register: (name: string, email: string, password: string, deviceId?: string) => Promise<void>;
    logout: () => void;
}
export declare const useAuth: () => UseAuthReturn;
export {};

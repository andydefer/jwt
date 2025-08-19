"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.useAuthStore = void 0;
const react_1 = require("@inertiajs/react");
const axios_1 = __importDefault(require("axios"));
const zustand_1 = require("zustand");
const middleware_1 = require("zustand/middleware");
exports.useAuthStore = (0, zustand_1.create)()((0, middleware_1.persist)((set, get) => ({
    token: null,
    user: null,
    isLoading: false,
    error: null,
    isInitialized: false,
    initialize: () => __awaiter(void 0, void 0, void 0, function* () {
        if (get().isInitialized || get().isLoading)
            return;
        set({ isLoading: true });
        try {
            const storedToken = get().token;
            if (storedToken) {
                yield get().fetchUser();
            }
        }
        catch (_a) {
            set({ token: null, user: null });
        }
        finally {
            set({ isLoading: false, isInitialized: true });
        }
    }),
    login: (email, password, deviceId) => __awaiter(void 0, void 0, void 0, function* () {
        var _a, _b, _c;
        set({ isLoading: true, error: null });
        try {
            const response = yield axios_1.default.post('/jwt/login', {
                email,
                password,
                device_id: deviceId || crypto.randomUUID(),
            });
            const token = response.data.token || ((_a = response.data.data) === null || _a === void 0 ? void 0 : _a.token);
            if (!token)
                throw new Error('Token not returned');
            set({ token, isLoading: false, isInitialized: true });
            yield get().fetchUser();
        }
        catch (error) {
            set({ error: ((_c = (_b = error.response) === null || _b === void 0 ? void 0 : _b.data) === null || _c === void 0 ? void 0 : _c.message) || 'Login failed', isLoading: false });
            throw error;
        }
    }),
    register: (name, email, password, deviceId) => __awaiter(void 0, void 0, void 0, function* () {
        var _a, _b, _c, _d;
        set({ isLoading: true, error: null });
        try {
            const response = yield axios_1.default.post('/jwt/register', {
                name,
                email,
                password,
                password_confirmation: password,
                device_id: deviceId || crypto.randomUUID(),
            });
            const token = response.data.token || ((_a = response.data.data) === null || _a === void 0 ? void 0 : _a.token);
            const user = response.data.user || ((_b = response.data.data) === null || _b === void 0 ? void 0 : _b.user);
            if (!token || !user)
                throw new Error('Token or user not returned');
            set({ token, user, isLoading: false, isInitialized: true });
        }
        catch (error) {
            set({ error: ((_d = (_c = error.response) === null || _c === void 0 ? void 0 : _c.data) === null || _d === void 0 ? void 0 : _d.message) || 'Registration failed', isLoading: false });
            throw error;
        }
    }),
    logout: () => __awaiter(void 0, void 0, void 0, function* () {
        const token = get().token;
        try {
            if (token) {
                yield axios_1.default.post('/jwt/logout', {}, {
                    headers: { Authorization: `Bearer ${token}` },
                });
            }
        }
        finally {
            set({ token: null, user: null, isInitialized: true });
            react_1.router.visit('/login');
        }
    }),
    fetchUser: () => __awaiter(void 0, void 0, void 0, function* () {
        var _a;
        const token = get().token;
        if (!token)
            return;
        try {
            const response = yield axios_1.default.get('/jwt/user', {
                headers: { Authorization: `Bearer ${token}` },
            });
            set({ user: response.data.user || response.data.data });
        }
        catch (error) {
            if (((_a = error.response) === null || _a === void 0 ? void 0 : _a.status) === 401) {
                set({ token: null, user: null });
            }
            throw error;
        }
    }),
    refreshToken: () => __awaiter(void 0, void 0, void 0, function* () {
        var _a;
        const token = get().token;
        if (!token)
            return;
        set({ isLoading: true });
        try {
            const response = yield axios_1.default.post('/jwt/refresh', {}, {
                headers: { Authorization: `Bearer ${token}` },
            });
            set({ token: response.data.token || ((_a = response.data.data) === null || _a === void 0 ? void 0 : _a.token) });
        }
        catch (error) {
            console.error('Token refresh failed', error);
        }
        finally {
            set({ isLoading: false });
        }
    }),
}), {
    name: 'jwt-auth-storage',
    partialize: (state) => ({ token: state.token, user: state.user }),
}));

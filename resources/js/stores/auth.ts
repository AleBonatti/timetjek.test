import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { User } from '@/types';
import axios from '@/utils/axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const isAuthenticated = ref(false);
    const isLoading = ref(false);

    const setUser = (userData: User | null) => {
        user.value = userData;
        isAuthenticated.value = !!userData;
    };

    const clearUser = () => {
        user.value = null;
        isAuthenticated.value = false;
    };

    const fetchUser = async () => {
        if (isLoading.value) return;

        isLoading.value = true;

        try {
            const response = await axios.get('/user');
            setUser(response.data.user);
        } catch {
            clearUser();
        } finally {
            isLoading.value = false;
        }
    };

    const logout = async () => {
        try {
            await axios.post('/api/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            clearUser();
        }
    };

    return {
        user,
        isAuthenticated,
        isLoading,
        setUser,
        clearUser,
        fetchUser,
        logout,
    };
});

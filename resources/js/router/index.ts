import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes: RouteRecordRaw[] = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('@/views/Dashboard.vue'),
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Try to load user if not already loaded
    if (!authStore.user && !authStore.isLoading) {
        await authStore.fetchUser();
    }

    const requiresAuth = to.matched.some((record) => record.meta.requiresAuth);
    const isGuestRoute = to.matched.some((record) => record.meta.guest);

    if (requiresAuth && !authStore.isAuthenticated) {
        // Redirect to login if not authenticated
        next({ name: 'login' });
    } else if (isGuestRoute && authStore.isAuthenticated) {
        // Redirect to dashboard if already authenticated
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;

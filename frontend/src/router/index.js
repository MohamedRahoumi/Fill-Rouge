import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Auth Views
import LoginView from '../views/auth/LoginView.vue';

// Admin Views
import AdminDashboard from '../views/admin/DashboardView.vue';
import AdminMembers from '../views/admin/MembersView.vue';
import AdminJoueurs from '../views/admin/JoueursView.vue';
import AdminStructure from '../views/admin/StructureView.vue';
import AdminPayments from '../views/admin/PaymentsView.vue';
import AdminNotifications from '../views/admin/NotificationsView.vue';

// Coach Views
import CoachDashboard from '../views/coach/DashboardView.vue';
import CoachGroupe from '../views/coach/GroupeView.vue';
import CoachAppel from '../views/coach/AppelView.vue';

// Parent Views
import ParentDashboard from '../views/parent/DashboardView.vue';
import ParentJoueurDetail from '../views/parent/JoueurDetailView.vue';
import ParentPayment from '../views/parent/PaymentView.vue';

const routes = [
    { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
    
    // Admin Routes
    { 
        path: '/admin/dashboard', 
        name: 'admin.dashboard', 
        component: AdminDashboard, 
        meta: { auth: true, role: 'superadmin' } 
    },
    { 
        path: '/admin/members', 
        name: 'admin.members', 
        component: AdminMembers, 
        meta: { auth: true, role: 'superadmin' } 
    },
    { 
        path: '/admin/joueurs', 
        name: 'admin.joueurs', 
        component: AdminJoueurs, 
        meta: { auth: true, role: 'superadmin' } 
    },
    { 
        path: '/admin/structure', 
        name: 'admin.structure', 
        component: AdminStructure, 
        meta: { auth: true, role: 'superadmin' } 
    },
    { 
        path: '/admin/paiements', 
        name: 'admin.paiements', 
        component: AdminPayments, 
        meta: { auth: true, role: 'superadmin' } 
    },
    { 
        path: '/admin/notifications', 
        name: 'admin.notifications', 
        component: AdminNotifications, 
        meta: { auth: true, role: 'superadmin' } 
    },

    // Coach Routes
    { 
        path: '/coach/dashboard', 
        name: 'coach.dashboard', 
        component: CoachDashboard, 
        meta: { auth: true, role: 'coach' } 
    },
    { 
        path: '/coach/groupes/:id', 
        name: 'coach.groupes.show', 
        component: CoachGroupe, 
        meta: { auth: true, role: 'coach' } 
    },
    { 
        path: '/coach/seances/:id/appel', 
        name: 'coach.appel', 
        component: CoachAppel, 
        meta: { auth: true, role: 'coach' } 
    },

    // Parent Routes
    { 
        path: '/parent/dashboard', 
        name: 'parent.dashboard', 
        component: ParentDashboard, 
        meta: { auth: true, role: 'parent' } 
    },
    { 
        path: '/parent/joueurs/:id', 
        name: 'parent.joueur.show', 
        component: ParentJoueurDetail, 
        meta: { auth: true, role: 'parent' } 
    },
    { 
        path: '/parent/paiement', 
        name: 'parent.paiement', 
        component: ParentPayment, 
        meta: { auth: true, role: 'parent' } 
    },

    {
        path: '/',
        redirect: () => {
            const authStore = useAuthStore();
            if (authStore.isAuthenticated) {
                if (authStore.isAdmin) return '/admin/dashboard';
                if (authStore.isCoach) return '/coach/dashboard';
                if (authStore.isParent) return '/parent/dashboard';
            }
            return '/login';
        }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;

    if (to.meta.auth && !isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && isAuthenticated) {
        if (authStore.isAdmin) next('/admin/dashboard');
        else if (authStore.isCoach) next('/coach/dashboard');
        else if (authStore.isParent) next('/parent/dashboard');
        else next('/');
    } else if (to.meta.role && authStore.user?.role !== to.meta.role) {
        next('/');
    } else {
        next();
    }
});

export default router;

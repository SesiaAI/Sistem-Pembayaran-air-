import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import LandingView from '../views/LandingView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import InvoiceView from '../views/InvoiceView.vue';

// Pelanggan
import PelangganDashboard from '../views/pelanggan/DashboardView.vue';
import PelangganBills from '../views/pelanggan/BillsView.vue';

// Admin
import AdminDashboard from '../views/admin/DashboardView.vue';
import MeterReadingForm from '../views/admin/MeterReadingFormView.vue';
import MeterHistory from '../views/admin/MeterHistoryView.vue';
import AdminBills from '../views/admin/BillsView.vue';
import CustomersView from '../views/admin/CustomersView.vue';
import TariffsView from '../views/admin/TariffsView.vue';
import UsersView from '../views/admin/UsersView.vue';

const routes = [
  { path: '/', name: 'landing', component: LandingView },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },

  // Invoice / Struk (Bisa dilihat Pelanggan & Admin)
  { 
    path: '/pelanggan/invoice/:id', 
    name: 'invoice', 
    component: InvoiceView,
    meta: { requiresAuth: true }
  },

  // Area Pelanggan
  {
    path: '/pelanggan/dashboard',
    name: 'pelanggan.dashboard',
    component: PelangganDashboard,
    meta: { requiresAuth: true, role: 'pelanggan' }
  },
  {
    path: '/pelanggan/bills',
    name: 'pelanggan.bills',
    component: PelangganBills,
    meta: { requiresAuth: true, role: 'pelanggan' }
  },

  // Area Admin & Super Admin
  {
    path: '/admin/dashboard',
    name: 'admin.dashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/meter-readings',
    name: 'admin.meter-readings',
    component: MeterReadingForm,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/meter-history',
    name: 'admin.meter-history',
    component: MeterHistory,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/bills',
    name: 'admin.bills',
    component: AdminBills,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/customers',
    name: 'admin.customers',
    component: CustomersView,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/tariffs',
    name: 'admin.tariffs',
    component: TariffsView,
    meta: { requiresAuth: true, role: 'super_admin' }
  },
  {
    path: '/admin/users',
    name: 'admin.users',
    component: UsersView,
    meta: { requiresAuth: true, role: 'super_admin' }
  },

  // Fallback
  { path: '/:pathMatch(.*)*', redirect: '/' }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation Guards
router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' });
  }

  // Jika sudah login dan mencoba ke halaman login/register/landing, redirect ke dashboard
  if ((to.name === 'login' || to.name === 'register') && auth.isAuthenticated) {
    if (auth.isPelanggan) {
      return next({ name: 'pelanggan.dashboard' });
    } else {
      return next({ name: 'admin.dashboard' });
    }
  }

  // Role checking
  if (to.meta.role) {
    if (to.meta.role === 'admin' && !auth.isAdmin) {
      return next({ name: 'pelanggan.dashboard' });
    }
    if (to.meta.role === 'super_admin' && !auth.isSuperAdmin) {
      return next({ name: 'admin.dashboard' });
    }
    if (to.meta.role === 'pelanggan' && auth.isAdmin) {
      return next({ name: 'admin.dashboard' });
    }
  }

  next();
});

export default router;

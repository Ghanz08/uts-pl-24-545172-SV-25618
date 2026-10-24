import { createRouter, createWebHistory } from 'vue-router'
import { auth } from './api/http'
import LoginView from './views/LoginView.vue'
import VaultView from './views/VaultView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
    { path: '/', name: 'vault', component: VaultView, meta: { requiresAuth: true } },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !auth.isLoggedIn()) return { name: 'login' }
  if (to.meta.guest && auth.isLoggedIn()) return { name: 'vault' }
})

export default router

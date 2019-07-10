import VueRouter from 'vue-router'
// Pages
import Home from './views/Home'
import Contact from './views/Contact'
import Register from './views/Register'
import Login from './views/Login'
// Routes
const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: {
            auth: undefined
        }
    },
    {
        path: '/contact',
        name: 'contact',
        component: Contact,
        meta: {
            auth: undefined
        }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: {
            auth: false
        }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: {
            auth: false
        }
    }

// // USER ROUTES
//
//     {
//         path: '/dashboard',
//         name: 'dashboard',
//         component: Dashboard,
//         meta: {
//             auth: true
//         }
//     },
//
// // ADMIN ROUTES
//
//     {
//         path: '/admin',
//         name: 'admin.dashboard',
//         component: AdminDashboard,
//         meta: {
//             auth: {roles: 2, redirect: {name: 'login'}, forbiddenRedirect: '/403'}
//         }
//     },
];

const router = new VueRouter({
    history: true,
    mode: 'history',
    routes,
});

export default router

import VueRouter from 'vue-router'
// Pages
import Home from './views/Home'
import Contact from './views/Contact'
import Register from './views/Register'
import Login from './views/Login'
import User from './views/users/UsersShow'
import Users from './views/users/UsersIndex'
import Images from './views/users/images/UsersImagesIndex'
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
    },
// USER ROUTES
    {
        path: '/dashboard',
        name: 'dashboard.show',
        component: User,
        meta: {
            auth: true
        }
    },
    {
        path: '/images',
        name: 'images.index',
        component: Images,
        meta: {
            auth: true
        }
    },
// ADMIN ROUTES
    {
        path: '/users',
        name: 'users.index',
        component: Users,
        meta: {
            auth: {roles: 2}
        }
    },
    {
        path: '/users/:userId',
        name: 'users.show',
        component: User,
        meta: {
            auth: true
        }
    },
    {
        path: '/users/:userId/images',
        name: 'users.images.index',
        component: Images,
        meta: {
            auth: {roles: 2}
        }
    }
];

const router = new VueRouter({
    history: true,
    mode: 'history',
    routes,
});

export default router

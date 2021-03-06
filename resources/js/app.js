import 'es6-promise/auto'
import VueAuth from '@websanova/vue-auth'
import VueAxios from 'vue-axios'
import VueRouter from 'vue-router'
import VeeValidate from 'vee-validate'
import VueLazyload from 'vue-lazyload'
import App from './views/App'
import auth from './auth'
import router from './router'
// import axios from 'axios'
require('./bootstrap');
window.Vue = require('vue');
// Set Vue router
Vue.router = router;
Vue.use(VueRouter);
Vue.use(VeeValidate);
Vue.use(VueLazyload, {
    preLoad: 1.3,
    attempt: 1,
    lazyComponent: true,
});

// Temporary fix for the VueLazyLoad plugin missing the .destroy function
Vue.use({
    install() {
        Vue.prototype.destroy = Vue.prototype.$destroy;
    },
});

// Set Vue authentication
Vue.use(VueAxios, axios);
axios.defaults.baseURL = `${process.env.MIX_APP_URL}/api`;
axios.defaults.headers.common['Content-Type'] = `application/json`;
Vue.use(VueAuth, auth);
// Load Index
Vue.component('app', App);
const app = new Vue({
    el: '#app',
    router
});

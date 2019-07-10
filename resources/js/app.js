import 'es6-promise/auto'
import VueAuth from '@websanova/vue-auth'
import VueAxios from 'vue-axios'
import VueRouter from 'vue-router'
import VeeValidate from 'vee-validate'
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
// Set Vue authentication
Vue.use(VueAxios, axios);
axios.defaults.baseURL = `http://imagegallery.test/api`;
Vue.use(VueAuth, auth);
// Load Index
Vue.component('app', App);
const app = new Vue({
    el: '#app',
    router
});

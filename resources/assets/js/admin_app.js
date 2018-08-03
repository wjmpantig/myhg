
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import { library } from '@fortawesome/fontawesome-svg-core';
import {faEdit} from '@fortawesome/free-regular-svg-icons/faEdit';
import {faTrashAlt} from '@fortawesome/free-regular-svg-icons/faTrashAlt';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faEdit,faTrashAlt);


require('./bootstrap');

window.Vue = require('vue');
import VuejsDialog from 'vuejs-dialog';
import VueRouter from 'vue-router';
window.VueResource = require('vue-resource');
Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.use(VueRouter);
Vue.use(VuejsDialog);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import { routes } from './admin_routes.js';

const router = new VueRouter({
	base: '/admin',
	routes
});

const app = new Vue({router}).$mount('#app');

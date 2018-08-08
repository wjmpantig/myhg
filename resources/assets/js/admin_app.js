
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import { library } from '@fortawesome/fontawesome-svg-core';
import {faEdit} from '@fortawesome/free-regular-svg-icons/faEdit';
import {faBars} from '@fortawesome/free-solid-svg-icons/faBars';
import {faTimes} from '@fortawesome/free-solid-svg-icons/faTimes';
import {faTrashAlt} from '@fortawesome/free-regular-svg-icons/faTrashAlt';
import {faCalendarAlt} from '@fortawesome/free-regular-svg-icons/faCalendarAlt';
import {faFileExport} from '@fortawesome/free-solid-svg-icons/faFileExport';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faEdit,faTrashAlt,faBars,faTimes,faFileExport,faCalendarAlt);


require('./bootstrap');

window.Vue = require('vue');
import VuejsDialog from 'vuejs-dialog';
import VueRouter from 'vue-router';
import Datepicker from 'vuejs-datepicker';

window.VueResource = require('vue-resource');
Vue.component('font-awesome-icon', FontAwesomeIcon);
Vue.component('datepicker',Datepicker);
Vue.use(VueRouter);
Vue.use(VuejsDialog);
Vue.use(require('vue-moment'));


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import { routes } from './admin_routes.js';

const router = new VueRouter({
	base: '/admin',
	routes,
	linkActiveClass: 'is-active'
});

const app = new Vue({
	router,
	mounted(){
		$('body').foundation();
	},
	methods:{
		logout(){
			$('#logout-form').submit()
			
		}
	},
	
}).$mount('#app');

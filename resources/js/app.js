/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import VueApexCharts from 'vue-apexcharts';


import _ from 'lodash'
Vue.prototype._ = str => _.get(window.i18n, str);

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(VueApexCharts);

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('school-table', require('./components/SchoolTable.vue').default);
Vue.component('school-form', require('./components/SchoolForm.vue').default);
Vue.component('user-list', require('./components/UserList.vue').default);
Vue.component('user-form', require('./components/UserForm.vue').default);
Vue.component('apexchart', VueApexCharts);
Vue.component('dashboard', require('./components/Dashboard.vue').default);
Vue.component('register-user-form', require('./components/RegisterUserForm.vue').default);
Vue.component('remote-user-list', require('./components/RemoteUserList.vue').default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});

import App from "./applications/sakhalin-energo/App";
import VueRouter from 'vue-router';
import router from "./applications/sakhalin-energo/routes";

require('./bootstrap');

// Подключение vue (без пути)
window.Vue = require('vue');
Vue.use(VueRouter);

const app = new Vue({
    el: '#app',
    components: {},
    render : h => h(App),
    router
});

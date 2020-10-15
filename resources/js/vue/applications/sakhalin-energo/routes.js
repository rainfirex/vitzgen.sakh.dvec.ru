import VueRouter from 'vue-router';

import Vitz from "./pages/Vitz";

export default new VueRouter({
    routes : [
        {
            path: '/', component:Vitz
        }
    ], mode : 'history'
});

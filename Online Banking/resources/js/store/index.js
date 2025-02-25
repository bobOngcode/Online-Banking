import Vue from 'vue';
import Vuex from 'vuex';
import auth from './modules/auth';
import userRolesPermissions from './modules/userRolesPermissions';
import branches from './modules/branches';
import product from './modules/product/index';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    auth,
    userRolesPermissions,
    branches,
    product,
  }
});
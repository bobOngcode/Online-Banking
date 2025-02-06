import axios from 'axios';
let url = "/api/product";
const state = {
  products: [],
};

const getters = {};

const actions = {
  storeProduct(commit, data) {
    return axios.post(`${url}/store`, data).then(
      (response) => {
        return response;
      },
      (error) => {

      }
    );
  },
};

const mutations = {
  setProducts(state, data) {
    state.products = data;
  }
};

export default {  
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}
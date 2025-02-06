import axios from 'axios';
import router from '../../router';

const state = {
  branches: [],
};

const getters = {};

const actions = {
  getBranch({ commit }) {
    axios.get("/api/branch/index").then(
      (response) => {
        commit('setBranch', response.data.branches);
      },
      (error) => {
        // if unauthenticated (401)
        if (error.response.status == "401") {
          router.push({ name: "unauthorize" });
        }
      }
    );
  },
};

const mutations = {
  setBranch(state, data) {
    state.branches = data;
  }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}
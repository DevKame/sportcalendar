

import authActions from "./authActions.js";
import authGetters from "./authGetters.js";
import authMutations from "./authMutations.js";

export default {
    namespaced: true,
    state() {
        return {
            API_AUTH: "../backend/auth/auth.php",
        };
    },
    actions: authActions,
    getters: authGetters,
    mutations: authMutations,
};
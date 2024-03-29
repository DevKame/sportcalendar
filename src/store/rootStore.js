
import { createStore } from "vuex";

import rootActions from "./rootActions.js";
import rootGetters from "./rootGetters.js";
import rootMutations from "./rootMutations.js";

import authStore from "./auth/authStore.js";
import groupStore from "./groups/groupStore.js";
import studentStore from "./students/studentStore.js";
import trainerStore from "./trainers/trainerStore.js";
import trainingStore from "./trainings/trainingStore.js";
import eventStore from "./events/eventStore.js";

const store = createStore({
    modules: {
        auth: authStore,
        groups: groupStore,
        students: studentStore,
        trainers: trainerStore,
        trainings: trainingStore,
        events: eventStore,
    },
    state() {
        return {
            is_logged_in: false,
            logged_id: null,
            logged_firstname: null,
            logged_lastname: null,
            logged_email: null,
            logged_role: null,
            logged_groups: null,
        };
    },
    actions: rootActions,
    getters: rootGetters,
    mutations: rootMutations,
});

export default store;
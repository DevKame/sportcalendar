

import studentActions from "./studentActions.js";
import studentGetters from "./studentGetters.js";
import studentMutations from "./studentMutations.js";

export default {
    namespaced: true,
    state() {
        return {
            // ENDPOINT FOR WORKING WITH STUDENT-API
            API_STUDENTS: "../backend/students/students.php",
            // FOR EDITING A STUDENT HIS DATA ARE PREPARED HERE
            studentID_edit: null,
            studentemail_edit: null,
            studentfirstname_edit: null,
            studentlastname_edit: null,
            studentgroups_edit: null,
        };
    },
    actions: studentActions,
    getters: studentGetters,
    mutations: studentMutations,
};
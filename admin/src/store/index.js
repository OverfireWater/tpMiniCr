import Vue from 'vue'
import Vuex from 'vuex'
import VuexPersistence from 'vuex-persist'
import getters from './getters'
import app from './modules/app'
import permission from './modules/permission'
import settings from './modules/settings'
import user from './modules/user'

Vue.use(Vuex)

const store = new Vuex.Store({
  plugins: [
    new VuexPersistence({
      reducer: (state) => ({
        user: state.user
      }),
      storage: window.localStorage
    }).plugin
  ],
  modules: {
    app,
    permission,
    settings,
    user
  },
  getters
})

export default store

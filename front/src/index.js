import Vue from 'vue'
import VueI18n from 'vue-i18n'
import Meta from 'vue-meta'
import router from './router'
import store from './store'
import messages from './locale'
import App from './App'
import VueFloatLabel from 'vue-float-label'
import VueObserveVisibility from 'vue-observe-visibility'
import VueSimpleAlert from 'vue-simple-alert'
const moment = require('vue-moment')

Vue.use(VueSimpleAlert)
Vue.use(moment)
Vue.use(VueI18n)
Vue.use(Meta)
Vue.use(VueFloatLabel)
Vue.use(VueObserveVisibility)

const i18n = new VueI18n({
  locale: 'en',
  messages
})

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')

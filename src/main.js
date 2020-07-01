import Vue from 'vue'
import App from './App'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

export default new Vue({
	el: '#content2',
	render: h => h(App),
})

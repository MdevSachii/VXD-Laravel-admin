import { createRouter, createWebHashHistory } from 'vue-router'

import Index from '../components/Tours/Index.vue'

const routes = [
  { path: '/', component: Index },
]

export default createRouter({
  history: createWebHashHistory(),
  routes,
})
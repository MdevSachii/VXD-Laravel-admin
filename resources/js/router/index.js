import { createRouter, createWebHashHistory } from 'vue-router'

import Posts from '../pages/Posts.vue'

const routes = [
  { path: '/', component: Posts },
]

export default createRouter({
  history: createWebHashHistory(),
  routes,
})
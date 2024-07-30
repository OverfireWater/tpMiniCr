import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import Layout from '@/layout'
import system from '@/router/modules/system'
import settings from '@/settings'
import setting from '@/router/modules/setting'

const routePre = settings.routePre

// 主框架之外的路由
export const constantRoutes = [
  {
    path: `${routePre}/login`,
    component: () => import('@/views/login/index'),
    hidden: true
  },

  {
    path: `${routePre}/404`,
    component: () => import('@/views/404'),
    hidden: true
  },

  {
    path: '/',
    component: Layout,
    redirect: `${routePre}/dashboard`,
    children: [{
      path: `${routePre}/dashboard`,
      name: 'Dashboard',
      component: () => import('@/views/dashboard/index'),
      meta: { title: '首页', icon: 'dashboard' }
    }]
  },
  {
    path: routePre,
    redirect: `/`
  }
]
// 错误路由
const errorRoutes = { path: '*', redirect: `${routePre}/404`, hidden: true }
/**
 * 异步路由
 */
export const asyncRoutes = [
  system,
  setting,
  // 错误路由放最后
  errorRoutes
]
const createRouter = () => new Router({
  mode: settings.routeModel, // require service support
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRoutes
})
const router = createRouter()

export function resetRouter() {
  const newRouter = createRouter()
  router.matcher = newRouter.matcher // reset router
}

export default router

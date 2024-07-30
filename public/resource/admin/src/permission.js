import router from './router'
import store from './store'
import { Message } from 'element-ui'
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' // progress bar style
import getPageTitle from '@/utils/get-page-title'
import settings from '@/settings'
const routePre = settings.routePre

NProgress.configure({ showSpinner: false }) // NProgress Configuration

const whiteList = [`${routePre}/login`] // no redirect whitelist

router.beforeEach(async(to, from, next) => {
  // start progress bar
  NProgress.start()
  // set page title
  document.title = getPageTitle(to.meta.title)

  // determine whether the user has logged in
  const hasToken = store.getters.token

  if (hasToken) {
    if (to.path === `${routePre}/login`) {
      // if is logged in, redirect to the home page
      next({ path: `${routePre}/` })
      NProgress.done()
    } else {
      // 判断token是否存在，如何存在，代表权限规则也存在，即路由也存在
      const status = store.getters.status
      if (status) {
        next()
      } else {
        try {
          store.state.app.status = store.getters.token
          const roles = store.getters.roles
          const accessRoutes = await store.dispatch('permission/generateRoutes', roles)
          router.addRoutes(accessRoutes)
          next({ ...to, replace: true })
        } catch (error) {
          await store.dispatch('user/resetToken')
          Message.error(error || 'Has Error')
          next(`${routePre}/login?redirect=${to.path}`)
          NProgress.done()
        }
      }
    }
  } else {
    /* has no token*/
    if (whiteList.indexOf(to.path) !== -1) {
      // in the free login whitelist, go directly
      next()
    } else {
      console.log(to)
      // other pages that do not have permission to access are redirected to the login page.
      next(`${routePre}/login?redirect=${to.path}`)
      NProgress.done()
    }
  }
})

router.afterEach(() => {
  // finish progress bar
  NProgress.done()
})

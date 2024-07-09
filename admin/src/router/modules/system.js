import Layout from '@/layout/index.vue'
import settings from '@/settings'

const routePre = settings.routePre

export default {
  path: `${routePre}/system`,
  name: 'system',
  redirect: `${routePre}/system/system_menus`,
  meta: {
    roles: ['admin-system']
  },
  component: Layout,
  children: [
    {
      path: 'system_menus',
      name: 'systemMenus',
      meta: {
        roles: ['system-system-menus'],
        title: '权限维护'
      },
      component: () => import('@/views/system/systemMenus/index')
    },
    // 接口管理
    {
      path: 'system_route_manage',
      name: 'systemRouteManage',
      meta: {
        roles: ['system-system-route-manage'],
        title: '接口管理'
      },
      component: () => import('@/views/system/systemRouteManage/index')
    }
  ]
}

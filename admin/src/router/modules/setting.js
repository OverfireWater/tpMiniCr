import Layout from '@/layout/index.vue'
import settings from '@/settings'

const routePre = settings.routePre

export default {
  path: `${routePre}/setting`,
  name: 'setting',
  redirect: `${routePre}/setting/system_role`,
  meta: {
    roles: ['admin-setting']
  },
  component: Layout,
  children: [
    {
      path: 'system_role',
      name: 'systemRole',
      meta: {
        title: '角色管理',
        roles: ['setting-system-role']
      },
      component: () => import('@/views/setting/systemRole')
    }
  ]
}

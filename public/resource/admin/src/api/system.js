import request from '@/utils/request'
const pre = '/system'

/**
 * 获取菜单列表
 * */
export function getMenusList() {
  return request({
    url: `${pre}/menus`,
    method: 'get'
  })
}

/**
 * 获取菜单详情
 */
export function getMenusDetail(id) {
  return request({
    url: `${pre}/menus/${id}`,
    method: 'get'
  })
}

/**
 * 获取创建表单时的数据
 * */
export function getCreateFormData() {
  return request({
    url: `${pre}/menus/create`,
    method: 'get'
  })
}

/**
 * 保存菜单信息
 * */
export function saveMenus(data) {
  return request({
    url: `${pre}/menus`,
    method: 'post',
    data
  })
}

/**
 * 更新菜单信息
 */
export function updateMenus(data) {
  return request({
    url: `${pre}/menus/${data.id}`,
    method: 'put',
    data
  })
}

/**
 * 删除
 * */
export function deleteMenus(id) {
  return request({
    url: `${pre}/menus/${id}`,
    method: 'delete'
  })
}

/**
 * 获取路由权限分类
 */
export function getRouteCateList() {
  return request({
    url: `${pre}/routeCate`,
    method: 'get'
  })
}

/**
 * 获取路由权限api列表
 */
export function getRouteApiList(id) {
  return request({
    url: `${pre}/routeList/${id}`,
    method: 'get'
  })
}

/**
 * 保存路由权限api
 */
export function saveSelectRouteRule(data) {
  return request({
    url: `${pre}/saveSelectRouteRule`,
    method: 'post',
    data: { menus: data }
  })
}

/**
 * 获取用户权限、菜单列表
 * */
export function getUniqueMenus() {
  return request({
    url: `${pre}/uniqueMenus`,
    method: 'get'
  })
}

/**
 * 改变菜单显隐
 * */
export function changeShowMenus(data) {
  return request({
    url: `${pre}/changeShowMenus/${data.id}`,
    method: 'put',
    data
  })
}

/**
 * 获取路由接口树
 */
export function getRouterTree(data) {
  return request({
    url: `${pre}/route/tree/${data}`,
    method: 'get'
  })
}

/**
 * 删除接口分类
 */
export function deleteRouteCategory(id, apiType) {
  return request({
    url: `${pre}/routeCate/delete/${id}/${apiType}`,
    method: 'delete'
  })
}

/**
 * 删除分类下所以api
 */
export function deleteRouteCategoryAllApi(id, apiType) {
  return request({
    url: `${pre}/route/deleteAllApi/${id}/${apiType}`,
    method: 'delete'
  })
}

/**
 * 保存分类名称
 */
export function saveRouteCategory(data) {
  return request({
    url: `${pre}/routeCate`,
    method: 'post',
    data
  })
}

/**
 * 修改分类名称
 */
export function updateRouteCategory(data) {
  return request({
    url: `${pre}/routeCate/${data.id}`,
    method: 'put',
    data
  })
}

/**
 * 获取api信息
 */
export function getRouteDetail(id) {
  return request({
    url: `${pre}/routeApi/${id}`,
    method: 'get'
  })
}

/**
 * 新增api
 */
export function saveRouteApi(data) {
  return request({
    url: `${pre}/routeApi`,
    method: 'post',
    data
  })
}

/**
 * 更新api
 */
export function updateRouteApi(data) {
  return request({
    url: `${pre}/routeApi/${data.id}`,
    method: 'put',
    data
  })
}

/**
 * 删除接口
 */
export function deleteRouteApi(id) {
  return request({
    url: `${pre}/routeApi/${id}`,
    method: 'delete'
  })
}

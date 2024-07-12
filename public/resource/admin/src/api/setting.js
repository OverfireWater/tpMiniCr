import request from '@/utils/request'

const pre = 'setting'

/**
 * 获取角色列表
 */
export function getRoleList(data) {
  return request({
    url: `${pre}/role?page=${data.page}&limit=${data.limit}`,
    method: 'get'
  })
}

/**
 * 创建表单时的数据
 */
export function getRoleCreateForm() {
  return request({
    url: `${pre}/role/create`,
    method: 'get'
  })
}

/**
 * 保存角色
 */
export function saveRole(data) {
  return request({
    url: `${pre}/role`,
    method: 'post',
    data
  })
}

/**
 * 获取角色详情
 */
export function getRoleDetail(id) {
  return request({
    url: `${pre}/role/${id}`,
    method: 'get'
  })
}

/**
 * 更新角色
 */
export function updateRole(data) {
  return request({
    url: `${pre}/role/${data.id}`,
    method: 'put',
    data
  })
}

/**
 * 修改角色状态
 */
export function updateRoleStatus(data) {
  return request({
    url: `${pre}/updateStatus/${data.id}`,
    method: 'put',
    data
  })
}

/**
 * 删除角色
 */
export function deleteRole(id) {
  return request({
    url: `${pre}/role/${id}`,
    method: 'delete'
  })
}

/**
 * 管理员api接口
 */
export function getAdminList(data) {
  return request({
    url: `${pre}/admin?page=${data.page}&limit=${data.limit}`,
    method: 'get'
  })
}

/**
 * 保存管理
 */

export function saveAdmin(data) {
  return request({
    url: `${pre}/admin`,
    method: 'post',
    data
  })
}

/**
 * 修改管理
 */

export function updateAdmin(data) {
  return request({
    url: `${pre}/admin/${data.id}`,
    method: 'put',
    data
  })
}

/**
 * 删除管理
 */
export function deleteAdmin(id) {
  return request({
    url: `${pre}/admin/${id}`,
    method: 'delete'
  })
}

/**
 * 创建表单时的数据
 */

export function getAdminCreateForm() {
  return request({
    url: `${pre}/admin/create`,
    method: 'get'
  })
}

/**
 * 获取详情
 */
export function getAdminDetailForm(id) {
  return request({
    url: `${pre}/admin/${id}`,
    method: 'get'
  })
}

/**
 * 修改状态
 */
export function updateAdminStatus(data) {
  return request({
    url: `${pre}/updateAdminStatus/${data.id}`,
    method: 'put',
    data
  })
}

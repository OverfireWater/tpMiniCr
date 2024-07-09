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
export function updateRole(id, data) {
  return request({
    url: `${pre}/role/${id}`,
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

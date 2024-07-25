import request from '@/utils/request'

const pre = '/system'

/**
 * 获取Crud生成列表
 */
export function getCrudList(data) {
  return request({
    url: `${pre}/codeGeneration?page=${data.page}&limit=${data.limit}`,
    method: 'get'
  })
}

/**
 * 获取创建crud的需要的数据
 */
export function createCrudForm() {
  return request({
    url: `${pre}/codeGeneration/create`,
    method: 'get'
  })
}

/**
 * 动态获取表单字段
 */
export function getTableName(name) {
  return request({
    url: `${pre}/getTableName/${name}`,
    method: 'get'
  })
}

/**
 * 保存Crud生成
 */
export function saveCrud(data) {
  return request({
    url: `${pre}/codeGeneration`,
    method: 'post',
    data
  })
}

/**
 * 删除Crud记录表
 */
export function delCrudRecord(id) {
  return request({
    url: `${pre}/codeGeneration/${id}`,
    method: 'delete'
  })
}

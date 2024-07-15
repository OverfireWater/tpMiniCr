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
 * 删除Crud记录表
 */
export function delCrudRecord(id) {
  return request({
    url: `${pre}/codeGeneration/${id}`,
    method: 'delete'
  })
}

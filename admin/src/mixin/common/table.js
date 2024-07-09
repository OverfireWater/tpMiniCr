// 关于table 通用代码

export default {
  data() {
    return {
      loading__: true, // 表格加载状态
      currentPage__: 1, // 当前页码
      limit__: 20, // 每页显示条数
      total__: 0 // 数据总数
    }
  }
}

<template>
  <div>
    <el-card v-if="!isShowForm">
      <el-button class="d-mb-10" type="primary" size="mini" @click="add">添加功能</el-button>
      <el-table v-loading="loading__" :data="tableData">
        <el-table-column prop="id" label="id" />
        <el-table-column prop="name" label="菜单名" />
        <el-table-column prop="table_name" label="表名" />
        <el-table-column prop="model_name" label="模型名" />
        <el-table-column prop="table_comment" label="备注" />
        <el-table-column prop="update_time" label="修改时间" />
        <el-table-column prop="add_time" label="添加时间" />
        <el-table-column label="操作" fixed="right" width="150px">
          <template v-slot="{row}">
            <el-button type="primary" size="mini" @click="edit(row.id)">编辑</el-button>
            <el-button type="danger" size="mini" @click="del(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <code-generation-form v-else @back="back" />
  </div>
</template>

<script>
import { getCrudList, delCrudRecord } from '@/api/systemCodeGeneration'
import table from '@/mixin/common/table'
import codeGenerationForm from './components/codeGenerationForm'
export default {
  components: {
    codeGenerationForm
  },
  mixins: [table],
  data() {
    return {
      tableData: [],
      isShowForm: true
    }
  },
  mounted() {
    this.initData()
  },
  methods: {
    initData() {
      this.loading__ = true
      const page = {
        page: this.currentPage__,
        limit: this.limit__
      }
      getCrudList(page).then(res => {
        const { data } = res
        this.tableData = data.list
        this.total__ = data.count
        this.loading__ = false
      }).catch(() => {})
    },
    add() {
      this.isShowForm = true
    },
    edit(id) {

    },
    del(id) {
      this.$confirm('此操作将永久删除该功能生成记录, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        delCrudRecord(id).then(res => {
          this.$message.success(res.msg)
          this.initData()
        }).catch(() => {})
      }).catch(() => {})
    },
    back() {
      this.isShowForm = false
    }
  }
}
</script>

<style scoped lang="scss">

</style>

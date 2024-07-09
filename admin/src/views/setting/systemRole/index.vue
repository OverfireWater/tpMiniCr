<template>
  <div>
    <el-card class="box-card">
      <el-button class="d-mb-10" type="primary" size="mini" @click="add">添加角色</el-button>
      <el-table
        v-loading="loading__"
        :data="tableData"
      >
        <el-table-column prop="id" label="id" />
        <el-table-column prop="role_name" label="角色名称" />
        <el-table-column prop="status" label="状态">
          <template v-slot="{row}">
            <el-switch v-model="row.status" :active-value="1" />
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="150px">
          <template v-slot="{row}">
            <el-button type="primary" size="mini" @click="edit(row.id)">编辑</el-button>
            <el-button type="danger" size="mini" @click="deleteRole(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <pagination :total="total__" :page.sync="currentPage__" :limit.sync="limit__" @pagination="initData" />
    </el-card>
    <role-form ref="roleForm" :title="title" @initData="initData" />
  </div>
</template>

<script>
import { getRoleList, deleteRole } from '@/api/setting'
import table from '@/mixin/common/table'
import roleForm from './components/roleForm'

export default {
  components: {
    roleForm
  },
  mixins: [table],
  data() {
    return {
      tableData: [],
      title: ''
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
      getRoleList(page).then(res => {
        const { data } = res
        this.tableData = data.list
        this.total__ = data.count
      }).catch(() => {})
      this.loading__ = false
    },
    // 添加角色
    add() {
      this.openDialog('添加角色')
    },
    // 编辑角色
    edit(id) {
      this.openDialog('编辑角色')
      this.$refs.roleForm.getDetail(id)
    },
    // 删除角色
    deleteRole(id) {
      this.$confirm('此操作将永久删除该角色, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        deleteRole(id).then(res => {
          this.$message.success('删除成功')
          this.initData()
        }).catch(() => {})
      }).catch(() => {})
    },
    // 打开弹窗
    openDialog(title) {
      this.title = title
      const roleForm = this.$refs.roleForm
      roleForm.dialogVisible = true
      roleForm.initData()
    }
  }
}
</script>

<style scoped lang="scss">

</style>

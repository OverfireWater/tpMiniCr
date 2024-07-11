<template>
  <div>
    <el-card class="box-card">
      <el-button class="d-mb-10" type="primary" size="mini" @click="add">添加管理员</el-button>
      <el-table
        v-loading="loading__"
        :data="tableData"
      >
        <el-table-column prop="id" label="id" />
        <el-table-column prop="account" label="账号" />
        <el-table-column prop="head_pic" label="头像">
          <template v-slot="{row}">
            <img style="width: 40px;height: 40px;" class="d-border-radius" :src="row.head_pic" :alt="row.account">
          </template>
        </el-table-column>
        <el-table-column label="角色">
          <template v-slot="{row}">
            <el-tag v-for="(item,index) in row.roles" :key="index" class="d-mr-10" type="primary">{{ item.role_name }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="login_count" label="登陆次数" />
        <el-table-column prop="last_time" label="登录时间" />
        <el-table-column prop="last_ip" label="登陆ip" />
        <el-table-column label="状态">
          <template v-slot="{row}">
            <el-switch v-if="row.level" v-model="row.status" :active-value="1" @change="changeSwitch(row)" />
            <div v-else>正常</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="150px">
          <template v-slot="{row}">
            <el-button type="primary" size="mini" @click="edit(row.id)">编辑</el-button>
            <el-button type="danger" size="mini" @click="del(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <pagination :total="total__" :page.sync="currentPage__" :limit.sync="limit__" @pagination="initData" />
    </el-card>
    <admin-form ref="adminForm" :title="title" @initData="initData" />
  </div>
</template>

<script>
import table from '@/mixin/common/table'
import adminForm from './components/adminForm'
import { getAdminList, updateAdminStatus, deleteAdmin } from '@/api/setting'

export default {
  components: { adminForm },
  mixins: [table],
  data() {
    return {
      tableData: [],
      title: ''
    }
  },
  created() {
    this.initData()
  },
  methods: {
    initData() {
      this.loading__ = true
      const page = {
        page: this.currentPage__,
        limit: this.limit__
      }
      getAdminList(page).then(res => {
        const { data } = res
        this.tableData = data.list
        this.total__ = data.count
        this.loading__ = false
      }).catch(() => {})
    },
    add() {
      this.title = '添加管理'
      this.openDialog(false)
    },
    edit(id) {
      this.title = '编辑管理'
      this.openDialog(true)
      this.$refs.adminForm.getDetail(id)
    },
    del(id) {
      this.$confirm('此操作将永久删除该管理员, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        deleteAdmin(id).then(res => {
          this.$message.success(res.msg)
          this.initData()
        }).catch(() => {})
      }).catch(() => {})
    },
    // 改变状态
    changeSwitch(e) {
      const { id, status } = e
      const data = {
        id,
        status
      }
      updateAdminStatus(data).then(res => {
        this.$message.success('修改成功')
        this.initData()
      }).catch(() => {})
    },
    // 打开弹窗
    openDialog(isEdit = false) {
      const adminForm = this.$refs.adminForm
      adminForm.dialogFormVisible = true
      adminForm.isEdit = isEdit
      adminForm.initData()
    }
  }
}
</script>

<style scoped lang="scss">

</style>

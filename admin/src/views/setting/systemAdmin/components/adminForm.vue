<template>
  <div>
    <el-dialog :title="title" :visible.sync="dialogFormVisible" :before-close="handleClose">
      <el-form ref="form" :model="form" label-width="100px" size="mini">
        <el-form-item label="管理员账号">
          <el-input v-model="form.account" maxlength="16" auto-complete="off" placeholder="请输入管理员账号" />
        </el-form-item>
        <el-form-item label="管理员昵称">
          <el-input v-model="form.real_name" maxlength="30" placeholder="请输入管理员昵称" />
        </el-form-item>
        <el-form-item label="管理员密码">
          <el-input v-if="isEdit" v-model="form.password" show-password placeholder="请输入管理员密码（可选）" />
          <el-input v-else v-model="form.password" show-password placeholder="请输入管理员密码" />
        </el-form-item>
        <el-form-item label="确认密码">
          <el-input v-if="isEdit" v-model="form.enter_pwd" show-password placeholder="请再次输入密码（可选）" />
          <el-input v-else v-model="form.enter_pwd" show-password placeholder="请再次输入密码" />
        </el-form-item>
        <el-form-item label="角色">
          <el-select v-model="form.roles" class="d-w-100" multiple placeholder="请选择角色">
            <el-option v-for="item in roleList" :key="item.id" :label="item.role_name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="form.status" :active-value="1" />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button size="mini" @click="handleClose">取 消</el-button>
        <el-button size="mini" type="primary" @click="save">保 存</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { getAdminCreateForm, getAdminDetailForm, saveAdmin, updateAdmin } from '@/api/setting'

export default {
  props: {
    title: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      isEdit: false,
      dialogFormVisible: false,
      form: {
        account: '',
        real_name: '',
        password: '',
        enter_pwd: '',
        roles: [],
        status: 1
      },
      roleList: [] // 角色列表
    }
  },
  methods: {
    initData() {
      getAdminCreateForm().then(res => {
        this.roleList = res.data
      })
    },
    // 获取详情
    getDetail(id) {
      getAdminDetailForm(id).then(res => {
        this.form = res.data
      }).catch(() => {})
    },
    // 保存
    save() {
      if (!this.form.account) return this.$message.error('请填写管理员账号')
      if (!this.form.real_name) return this.$message.error('请填写管理员昵称')
      if (!this.isEdit) {
        if (!this.form.password) return this.$message.error('请填写管理员密码')
      }
      if (this.form.password) {
        if (this.form.password.length < 6) return this.$message.error('管理员密码不能小于6位')
        if (this.form.password !== this.form.enter_pwd) return this.$message.error('两次密码输入不一致')
      }
      if (this.form.roles.length === 0) return this.$message.error('请选择角色')
      const method = this.form.id ? updateAdmin : saveAdmin
      method(this.form).then(res => {
        this.$message.success(res.msg)
        this.handleClose()
        this.$emit('initData')
      }).catch(() => {})
    },
    // 关闭弹窗
    handleClose(done) {
      Object.assign(this.$data, this.$options.data())
      typeof done === 'function' ? done() : this.dialogVisible = false
    }
  }
}
</script>

<style scoped lang="scss">

</style>

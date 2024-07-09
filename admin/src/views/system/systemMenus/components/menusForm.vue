<template>
  <div>
    <el-dialog
      :title="title"
      :visible.sync="dialogVisible"
      width="40%"
      :close-on-click-modal="false"
      :before-close="beforeClose"
    >
      <el-form ref="form" size="mini" label-width="80px" :model="form" :rules="rules" hide-required-asterisk>
        <el-form-item label="类型">
          <el-radio v-model="form.auth_type" :label="1">菜单</el-radio>
          <el-radio v-model="form.auth_type" :label="2">接口</el-radio>
          <el-radio v-model="form.auth_type" :label="3">按钮</el-radio>
        </el-form-item>
        <el-form-item v-if="form.auth_type === 1" label="菜单名称" prop="menu_name">
          <el-input v-model="form.menu_name" placeholder="输入菜单名称" />
        </el-form-item>
        <el-form-item v-if="form.auth_type === 3" label="按钮名称" prop="menu_name">
          <el-input v-model="form.menu_name" placeholder="输入按钮名称" />
        </el-form-item>
        <template>
          <el-form-item v-if="form.auth_type === 2" label="接口名称" prop="menu_name">
            <el-input v-model="form.menu_name" placeholder="输入接口名称" />
          </el-form-item>
          <el-form-item v-if="form.auth_type === 2" label="接口地址" prop="api_url">
            <el-input v-model="form.api_url" placeholder="请输入接口地址" />
          </el-form-item>
          <el-form-item v-if="form.auth_type === 2" label="请求方式" prop="methods">
            <el-select v-model="form.methods" placeholder="请选择请求方式">
              <el-option label="GET" value="get" />
              <el-option label="POST" value="POST" />
              <el-option label="PUT" value="PUT" />
              <el-option label="DELETE" value="DELETE" />
            </el-select>
          </el-form-item>
        </template>
        <template>
          <el-form-item v-if="form.auth_type !== 2" label="页面地址" prop="menu_path">
            <el-input v-model="form.menu_path" placeholder="请输入页面地址">
              <template v-slot:prepend>
                <span style="color: black">admin</span>
              </template>
            </el-input>
          </el-form-item>
          <el-form-item v-if="form.auth_type !== 2" label="图标">
            <el-input v-model="form.icon" readonly placeholder="点击右边图标选择菜单图标">
              <template v-slot:append>
                <el-button style="font-size: 16px" icon="el-icon-picture-outline" @click="openIcon" />
              </template>
            </el-input>
          </el-form-item>
        </template>
        <el-form-item label="父级分类">
          <el-cascader
            v-model="form.path"
            style="width: 100%"
            :options="parentOptions"
            :props="{ checkStrictly: true }"
            clearable
          />
        </el-form-item>
        <el-form-item label="权限标识" prop="unique_auth">
          <el-input v-model="form.unique_auth" placeholder="请输入权限标识" />
        </el-form-item>
        <el-form-item label="权重" prop="sort">
          <el-input v-model="form.sort" placeholder="请输入权重（排序）" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="form.mark" placeholder="请输入备注" />
        </el-form-item>
        <el-form-item label="状态" prop="is_show">
          <el-radio v-model="form.is_show" :label="1">开启</el-radio>
          <el-radio v-model="form.is_show" :label="0">关闭</el-radio>
        </el-form-item>
      </el-form>
      <span slot="footer">
        <el-button size="mini" type="info" plain @click="resetForm">重置</el-button>
        <el-button size="mini" @click="beforeClose">取 消</el-button>
        <el-button size="mini" type="primary" @click="submit">保 存</el-button>
      </span>
      <span />
    </el-dialog>
    <!--    icon-->
    <el-dialog
      title="图标选择"
      :visible.sync="iconDialogVisible"
      width="50%"
      :close-on-click-modal="false"
    >
      <div class="icon-list">
        <div v-for="(item, index) in icon" :key="index" class="item" @click="selectIcon(item)">
          <i :class="`el-icon-${item}`" style="font-size: 24px" />
          <span>{{ item }}</span>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import icon from '@/utils/icon'
import { getCreateFormData, getMenusDetail, saveMenus, updateMenus } from '@/api/system'

export default {
  props: {
    title: {
      type: String,
      default: '提示'
    }
  },
  data() {
    return {
      // 表单弹窗
      dialogVisible: false,
      // 图标弹窗
      iconDialogVisible: false,
      // 图标
      icon,
      // 表单
      form: {
        icon: '',
        mark: '',
        unique_auth: '',
        auth_type: 1,
        menu_path: '',
        is_show: 1,
        sort: '',
        menu_name: '',
        methods: '',
        api_url: '',
        path: [],
        pid: '' // 父级id
      },
      input: '',
      parentOptions: [],
      rules: {
        menu_name: [
          { required: true, message: '请输入名称', trigger: 'blur' }
        ],
        menu_path: [
          { required: true, message: '请输入页面地址', trigger: 'blur' }
        ],
        unique_auth: [
          { required: true, message: '请输入权限标识', trigger: 'blur' }
        ],
        api_url: [
          { required: true, message: '请输入接口地址', trigger: 'blur' }
        ],
        methods: [
          { required: true, message: '请选择请求方式', trigger: 'blur' }
        ],
        sort: [
          { required: true, message: '请输入权重（排序）', trigger: 'blur' }
        ],
        is_show: [
          { required: true, message: '请选择状态', trigger: 'change' }
        ]
      }
    }
  },
  methods: {
    openIcon() {
      this.iconDialogVisible = true
    },
    // 选择图标
    selectIcon(item) {
      this.form.icon = item
      this.iconDialogVisible = false
    },
    // 重置表单
    resetForm() {
      this.$refs.form.resetFields()
    },
    // 获取父级菜单
    getParentOptions() {
      getCreateFormData().then(res => {
        this.parentOptions = res.data
      }).catch(() => {})
    },
    // 获取详情
    getDetail(id) {
      getMenusDetail(id).then(res => {
        this.form = res.data
      }).catch(() => {})
    },
    // 设置父级菜单选择框显示
    setParentMenus(id) {
      getMenusDetail(id).then(res => {
        const { data } = res
        this.form.path = [...data.path, data.id]
      }).catch(() => {})
    },
    submit() {
      this.$refs.form.validate(valid => {
        // 如果valid为true
        if (!valid) return false
        const { path } = this.form
        const length = path.length
        if (length) {
          this.form.pid = path[length - 1]
        }
        const method = this.form.id ? updateMenus : saveMenus
        method(this.form).then(res => {
          this.$message({
            type: 'success',
            message: '保存成功!'
          })
          this.$emit('initData')
          this.$emit('getUniqueMenus')
          this.dialogVisible = false
        }).catch(() => {})
      })
    },
    // 关闭前的回调
    beforeClose(done) {
      this.form.path = []
      typeof done === 'object' ? this.dialogVisible = false : done()
    }
  }
}
</script>

<style scoped lang="scss">
.icon-list {
  display: flex;
  flex-wrap: wrap;
  height: 500px;
  width: 100%;
  overflow: auto;
  gap: 10px;

  .item {
    width: 70px;
    height: 70px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    cursor: pointer;

    i {
      //text-align: center;
    }
    span {
      font-size: 12px;
    }
  }
}
</style>

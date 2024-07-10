<template>
  <div>
    <el-dialog
      :title="title"
      :visible.sync="dialogVisible"
      width="50%"
      :before-close="handleClose"
    >
      <el-form ref="form" :model="formData" label-width="80px" size="mini">
        <el-form-item label="角色名称">
          <el-input v-model="formData.role_name" placeholder="填写角色名称" />
        </el-form-item>
        <el-form-item label="角色状态">
          <el-radio-group v-model="formData.status">
            <el-radio :label="1">开启</el-radio>
            <el-radio :label="0">关闭</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="角色权限">
          <div class="trees-coadd">
            <div class="scollhide">
              <el-tree
                ref="tree"
                :data="routeList"
                :props="{ children: 'children', label: 'label'}"
                show-checkbox
                node-key="value"
                :default-expanded-keys="expandedKeys"
                @check="handleCheckChange"
              />
            </div>
          </div>
        </el-form-item></el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="mini" @click="handleClose">取 消</el-button>
        <el-button size="mini" type="primary" @click="save">保 存</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { saveRole, getRoleCreateForm, getRoleDetail, updateRole } from '@/api/setting'

export default {
  props: {
    title: {
      type: String,
      default: '提示'
    }
  },
  data() {
    return {
      dialogVisible: false,
      routeList: [], // 路由列表
      // 表单数据
      formData: {
        role_name: '',
        status: 1
      },
      checkRuleBox: [],
      expandedKeys: [] // 默认展开的节点
    }
  },
  methods: {
    initData() {
      getRoleCreateForm().then(res => {
        this.routeList = res.data
      })
    },
    // 获取详情
    getDetail(id) {
      getRoleDetail(id).then(res => {
        this.formData = res.data
        this.$nextTick((e) => {
          setTimeout(() => {
            res.data.rules.forEach(item => {
              const node = this.$refs.tree.getNode(item)
              this.$refs.tree.setChecked(node, true)
              this.expandedKeys = res.data.rules
            })
          }, 300)
        })
      }).catch(() => {})
    },
    // 保存
    save() {
      if (!this.formData.role_name) return this.$message.error('请填写角色名称')
      this.formData.rules = this.checkRuleBox.length ? this.checkRuleBox : this.expandedKeys
      const method = this.formData.id ? updateRole : saveRole
      method(this.formData).then(res => {
        this.$message.success(res.msg)
        this.handleClose()
        this.$emit('initData')
      }).catch(() => {})
    },

    // 节点选中状态发生变化时的回调
    handleCheckChange() {
      // 获取所有选中的节点 start
      const res = this.$refs.tree.getCheckedNodes(false, true)
      this.checkRuleBox = res.map(item => {
        return item.value
      })
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
.trees-coadd {
  width: 100%;
  height: 385px;
  .scollhide {
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: scroll;
  }
}
.scollhide::-webkit-scrollbar {
  display: none;
}
</style>

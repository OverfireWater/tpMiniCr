<template>
  <div>
    <el-card class="box-card">
      <el-tabs v-model="apiType">
        <el-tab-pane label="管理端接口" name="adminapi" />
        <el-tab-pane label="用户端接口" name="api" />
      </el-tabs>
      <div class="d-flex">
        <div class="d-mr-20">
          <el-button class="d-w-100 d-mb-10" type="primary" @click="addTopCate">新增分类</el-button>
          <el-tree
            ref="el_tree"
            class="el-tree"
            :data="treeData"
            node-key="id"
            :props=" { children: 'children', label: 'name' }"
            :default-expanded-keys="expandKey"
            @node-click="handleNodeClick"
          >
            <span slot-scope="{ node, data }" class="d-flex-1">
              <template>
                <div class="d-flex d-justify-between">
                  <div class="d-overflow-hidden d-text-wrap">
                    <span v-if="data.method !== 'DELETE'" :style="{ color: methodsColor(data.method) }">{{ data.method }} </span>
                    <span v-else :style="{ color: methodsColor(data.method) }">DEL </span>
                    <span>{{ data.name }}</span>
                  </div>
                  <el-dropdown @command="handleDropdown($event, data)">
                    <i class="el-icon-more-outline" />
                    <el-dropdown-menu>
                      <el-dropdown-item v-if="data.pid === 0 && !data.method" command="0" icon="el-icon-plus">新增分类</el-dropdown-item>
                      <el-dropdown-item v-if="!data.method" command="1" icon="el-icon-circle-plus">新增API接口</el-dropdown-item>
                      <el-dropdown-item v-if="!data.method" command="2" icon="el-icon-edit">修改分类名称</el-dropdown-item>
                      <el-dropdown-item v-if="!data.method" command="3" icon="el-icon-delete">删除分类</el-dropdown-item>
                      <el-dropdown-item v-if="data.method" command="4" icon="el-icon-delete">删除接口</el-dropdown-item>
                      <el-dropdown-item v-if="!data.method && data.children.length && data.children[0].cate_id" style="color: red" icon="el-icon-delete" command="5">删除全部接口</el-dropdown-item>
                    </el-dropdown-menu>
                  </el-dropdown>
                </div>
              </template>
            </span>
          </el-tree>
        </div>
        <route-form ref="routeForm" class="d-w-100" :methods-color="methodsColor" @changeExpandKey="changeExpandKey" @initData="initData" />
      </div>
    </el-card>
    <el-dialog title="添加分类" :visible.sync="dialogFormVisible">
      <el-form :model="form" label-width="80px" size="mini">
        <template v-if="form.pid && form.parentName">
          <el-form-item label="父类名称">
            <el-input :value="form.parentName" auto-complete="off" disabled />
          </el-form-item>
        </template>
        <el-form-item label="分类名称">
          <el-input v-model="form.name" auto-complete="off" placeholder="请输入分类名称" />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button size="mini" @click="dialogFormVisible = false">取 消</el-button>
        <el-button size="mini" type="primary" @click="save">保 存</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  deleteRouteCategory,
  getRouterTree,
  deleteRouteCategoryAllApi,
  saveRouteCategory,
  updateRouteCategory,
  deleteRouteApi
} from '@/api/system'
import routeForm from './components/routeForm'

export default {
  components: {
    routeForm
  },
  data() {
    return {
      apiType: 'adminapi', // 接口类型
      treeData: [], // 树数据
      expandKey: [], // 当前选中节点
      dialogFormVisible: false, // 新增分类弹窗
      form: {
        pid: 0, // 父类id
        parentName: '', // 父类名称
        name: '',
        app_name: 'adminapi'
      }
    }
  },
  watch: {
    apiType(newValue) {
      this.form.app_name = newValue
      this.initData()
    }
  },
  mounted() {
    this.initData()
  },
  methods: {
    // 初始化数据
    initData() {
      this.getData(this.apiType).then(() => {
        this.getFirstCateId(this.treeData)
        this.$refs.routeForm.getRouteDetail(this.expandKey)
      })
    },
    // 改变expandKey
    changeExpandKey(data) {
      this.expandKey = [data]
      this.getData(this.apiType).then(() => {
        this.$refs.routeForm.getRouteDetail(this.expandKey)
      })
    },
    // 获取数据
    getData(apiType) {
      return new Promise((resolve) => {
        getRouterTree(apiType).then(res => {
          this.treeData = res.data
          resolve()
        }).catch(() => {
        })
      })
    },
    // 节点点击事件
    handleNodeClick(data) {
      if (data.cate_id) {
        this.$refs.routeForm.getRouteDetail(data.id)
        if (this.$refs.routeForm.isEdit) this.$refs.routeForm.isEdit = false
        if (this.$refs.routeForm.isCreate) this.$refs.routeForm.isCreate = false
        if (this.$refs.routeForm.is_resource) this.$refs.routeForm.is_resource = false
      }
    },
    getFirstCateId(data) {
      for (let i = 0; i < data.length; i++) {
        // 如果当前分类有子分类，则递归调用
        if (data[i].children && data[i].children.length) {
          const result = this.getFirstCateId(data[i].children)
          if (result) {
            // 如果递归调用找到了结果，则直接返回
            return result
          }
        }
        // 如果当前分类没有子分类，则检查是否有cate_id，并返回
        if (data[i].cate_id) {
          this.expandKey = [data[i].id]
          return data[i].id // 返回ID以便外部可能也需要这个值
        }
      }
      // 如果没有找到任何cate_id，则返回null或undefined
      return null
    },

    // 下拉框事件
    handleDropdown(e, data) {
      const form = this.$refs.routeForm
      switch (e) {
        // 新增分类
        case '0':
          this.form.pid = data.id
          this.form.parentName = data.name
          this.dialogFormVisible = true
          break
        // 新增接口
        case '1':
          form.isEdit = true
          form.isCreate = true
          form.formValidate.id = ''
          form.formValidate.cate_id = data.id
          form.resetForm()
          break
        // 修改分类名称
        case '2':
          this.form.id = data.id
          this.form.name = data.name
          this.dialogFormVisible = true
          break
        // 删除
        case '3':
          this.$confirm('确定要删除此分类吗？<br/><span style="color: red;font-size: 12px">注：会删除此分类下的所有子分类</span>', '提示', {
            type: 'warning',
            dangerouslyUseHTMLString: true
          }).then(() => {
            this.deleteCate(data)
          }).catch(() => {
          })
          break
        case '4':
          this.$confirm('确定要删除此接口吗？', '提示', {
            type: 'warning'
          }).then(() => {
            this.deleteApi(data)
          }).catch(() => {})
          break
        // 删除全部接口
        case '5':
          this.$confirm('确定要删除此分类下的所有接口吗？<br/><span style="color: red;font-size: 12px">注：会删除此分类下的所有接口，请谨慎操作!</span>', '提示', {
            type: 'warning',
            dangerouslyUseHTMLString: true
          }).then(() => {
            this.deleteAllApi(data)
          }).catch(() => {
          })
          break
      }
    },
    // 保存顶级分类
    addTopCate() {
      this.resetData()
      this.dialogFormVisible = true
    },
    // 保存
    save() {
      if (!this.form.name) return this.$message.error('名称不能为空')
      const method = this.form.id ? updateRouteCategory : saveRouteCategory
      method(this.form).then(res => {
        this.$message.success(res.msg)
        this.getData(this.apiType)
        this.dialogFormVisible = false
        this.resetData()
      }).catch(() => {
      })
    },
    // 删除分类
    deleteCate(data) {
      deleteRouteCategory(data.id, this.apiType).then(res => {
        this.$message.success(res.msg)
        this.getData(this.apiType)
      }).catch(() => {
      })
    },
    // 删除接口
    deleteApi(data) {
      deleteRouteApi(data.id).then(res => {
        this.$message.success(res.msg)
        this.initData()
      }).catch(() => {
      })
    },
    // 删除分类下所有api
    deleteAllApi(data) {
      deleteRouteCategoryAllApi(data.id, this.apiType).then(res => {
        this.$message.success(res.msg)
        this.initData()
      }).catch(() => {
      })
    },
    // 方法颜色
    methodsColor(newVal) {
      if (!newVal) return false
      const method = newVal.toUpperCase()
      if (method === 'GET') {
        return '#61affe'
      } else if (method === 'POST') {
        return '#49cc90'
      } else if (method === 'PUT') {
        return '#fca130'
      } else if (method === 'DEL' || method === 'DELETE') {
        return '#f93e3e'
      }
    },
    // 初始化数据
    resetData() {
      this.form = {
        pid: 0,
        parentName: '',
        name: '',
        app_name: this.apiType
      }
    }
  }
}
</script>

<style scoped lang="scss">
.el-tree {
  width: 290px;
  height: calc(100vh - 240px);
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
  overflow-x: hidden;
  overflow-y: auto;
}
</style>

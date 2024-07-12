<template>
  <div>
    <!--    搜索框-->
    <el-card class="box-card">
      <el-form ref="form" :model="searchForm" label-width="80px">
        <el-form-item label="按钮名称">
          <el-input v-model="searchForm.menu_name" placeholder="请输入按钮名称" />
        </el-form-item>
      </el-form>
      <!--    表单结构-->
      <el-button type="primary" size="mini" style="margin-bottom: 10px;" @click="add">添加规则</el-button>
      <vxe-table
        ref="xTable"
        border="inner"
        highlight-hover-row
        :loading="loading__"
        header-row-class-name="false"
        :tree-config="{ childrenField: 'children', reserve: true, accordion: true }"
        :data="menusList"
        :row-config="{ keyField: 'id' }"
      >
        <vxe-table-column field="menu_name" tree-node title="按钮名称" min-width="100" />
        <vxe-table-column field="unique_auth" title="前端权限" min-width="200" />
        <vxe-table-column field="menu_path" title="路由" min-width="240" tooltip="true">
          <template v-slot="{ row }">
            <span v-if="row.auth_type === 1">菜单：{{ row.menu_path }}</span>
            <span v-if="row.auth_type === 3" style="color: green;">按钮</span>
            <span v-if="row.auth_type === 2" style="color: #20a0ff">接口：[{{ row.methods }}]{{ row.api_url }}</span>
          </template>
        </vxe-table-column>
        <vxe-table-column field="flag" title="显示状态" min-width="120">
          <template v-slot="{ row }">
            <el-switch
              v-model="row.is_show"
              :active-value="1"
              :inactive-value="0"
              :value="row.is_show"
              size="large"
              @change="onchangeIsShow(row)"
            />
          </template>
        </vxe-table-column>
        <vxe-table-column field="mark" title="备注" min-width="120" />
        <vxe-table-column field="date" title="操作" width="330" fixed="right">
          <template v-slot="{ row }">
            <el-button
              v-if="row.auth_type === 1 || row.auth_type === 3"
              type="primary"
              size="mini"
              @click="addRoute(row)"
            >选择权限
            </el-button>
            <el-button
              v-if="row.auth_type === 1 || row.auth_type === 3"
              type="success"
              size="mini"
              @click="addChild(row.id)"
            >添加下级
            </el-button>
            <el-button type="warning" size="mini" @click="edit(row.id)">编辑</el-button>
            <el-button type="danger" size="mini" @click="del(row.id)">删除</el-button>
          </template>
        </vxe-table-column>
      </vxe-table>
      <menus-form ref="menusForm" :title="title" @initData="initData" @getUniqueMenus="getUniqueMenus" />
      <change-route-rule ref="changeRouteRule" @initData="initData" @getUniqueMenus="getUniqueMenus" />
    </el-card>
  </div>
</template>

<script>
import menusForm from './components/menusForm'
import changeRouteRule from './components/changeRouteRule'
import { deleteMenus, getMenusList, getUniqueMenus, changeShowMenus } from '@/api/system'
import table from '@/mixin/common/table'

export default {
  components: {
    menusForm,
    changeRouteRule
  },
  mixins: [table],
  data() {
    return {
      title: '',
      searchForm: {
        menu_name: '', // 菜单名称
        auth_type: '' // 权限类型
      },
      menusList: []
    }
  },
  mounted() {
    this.initData()
  },
  methods: {
    // 初始化数据
    initData() {
      this.loading__ = true
      getMenusList().then(res => {
        this.menusList = res.data
        this.loading__ = false
      }).catch(() => {
      })
    },
    // 获取路由权限
    addRoute(data) {
      let str = data.path
      if (str) {
        str = str.split('/')
        str = [...str, data.id]
      } else {
        str = [data.id]
      }
      this.$refs.changeRouteRule.dialogVisible = true
      this.$refs.changeRouteRule.menusId = data.id
      this.$refs.changeRouteRule.parentMenusPath = str
      this.$refs.changeRouteRule.initData()
    },
    // 添加菜单，按钮，权限规则
    add() {
      this.title = '添加规则'
      this.$refs.menusForm.dialogVisible = true
      this.$refs.menusForm.getParentOptions()
    },
    // 添加下级
    addChild(id) {
      this.title = '添加下级'
      this.$refs.menusForm.dialogVisible = true
      this.$refs.menusForm.getParentOptions()
      this.$refs.menusForm.setParentMenus(id)
    },
    // 编辑
    edit(id) {
      this.title = '修改规则'
      this.$refs.menusForm.dialogVisible = true
      this.$refs.menusForm.getParentOptions()
      this.$refs.menusForm.getDetail(id)
    },
    // 删除
    async del(id) {
      try {
        await this.$confirm('此操作将永久删除该菜单, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        await deleteMenus(id)
        this.$message({
          type: 'success',
          message: '删除成功!'
        })
        this.initData()
        this.getUniqueMenus()
      } catch (e) {
        console.log(e)
      }
    },
    // 获取菜单和权限
    getUniqueMenus() {
      getUniqueMenus().then(res => {
        this.$store.commit('user/SET_MENUS', res.data.menus)
        this.$store.commit('user/SET_ROLES', res.data.unique)
      }).catch(() => {})
    },
    // 改变菜单显隐
    onchangeIsShow(e) {
      const data = {
        id: e.id,
        is_show: e.is_show
      }
      changeShowMenus(data).then(res => {
        this.getUniqueMenus()
        this.$message({
          type: 'success',
          message: res.msg
        })
      }).catch(() => {})
    }
  }
}
</script>

<style scoped lang="less">

</style>

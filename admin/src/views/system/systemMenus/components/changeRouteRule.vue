<template>
  <div>
    <el-dialog
      width="1100px"
      title="权限列表"
      :visible.sync="dialogVisible"
      :close-on-click-modal="false"
      :before-close="cancel"
    >
      <el-alert :closable="false">
        <template slot="title">
          1.接口可多选，可重复添加；<br>2.添加路由按照路由规则进行添加，即可在开发工具->接口管理里面新增；
        </template>
      </el-alert>
      <div class="route-list">
        <div class="tree">
          <el-tree
            ref="treeBox"
            :data="routeCateList"
            :highlight-current="true"
            :props="defaultProps"
            node-key="id"
            :default-expanded-keys="expandedKeys"
            @node-click="handleNodeClick"
          />
        </div>
        <div class="rule">
          <div
            v-for="(item, index) in routeList"
            :key="index"
            class="rule-list"
            :class="{ 'select-rule': selectRouteIds.includes(item.id) }"
            @click="selectRouteFunc(item)"
          >
            <div>接口名称：{{ item.name }}</div>
            <div>请求方式：{{ item.method }}</div>
            <div>接口地址：{{ item.path }}</div>
          </div>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button size="mini" @click="cancel">取 消</el-button>
        <el-button size="mini" type="primary" @click="save">保 存</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { getRouteCateList, getRouteApiList, saveSelectRouteRule } from '@/api/system'

export default {
  data() {
    return {
      dialogVisible: false, // 是否显示弹窗
      expandedKeys: [],
      defaultProps: {
        children: 'children',
        label: 'name'
      },
      routeCateList: [], // 路由分类树
      routeList: [], // 路由列表
      menusId: 0, // 菜单id
      parentMenusPath: [], // 父级菜单路径
      selectRouteIds: [], // 选中的路由id
      selectRoute: [] // 选中的路由
    }
  },
  methods: {
    initData() {
      this.getRouteCateList()
    },
    getRouteCateList() {
      getRouteCateList().then(res => {
        this.routeCateList = res.data
        this.getFirstCateId(res.data)
        this.getRouteList(this.expandedKeys)
      }).catch(() => {})
    },
    getRouteList(id) {
      getRouteApiList(id).then(res => {
        if (!res.data.length) return false
        this.routeList = res.data
      }).catch(() => {})
    },
    handleNodeClick(data) {
      this.getRouteList(data.id)
    },
    selectRouteFunc(data) {
      if (this.selectRouteIds.includes(data.id)) {
        const i = this.selectRouteIds.findIndex((e) => e === data.id)
        this.selectRouteIds.splice(i, 1)
        this.selectRoute.splice(i, 1)
      } else {
        this.selectRouteIds.push(data.id)
        this.selectRoute.push({
          menu_name: data.name,
          api_url: data.path,
          pid: this.menusId,
          method: data.method,
          path: this.parentMenusPath
        })
      }
    },
    // 保存
    save() {
      saveSelectRouteRule(this.selectRoute).then(res => {
        this.$message.success(res.msg)
        this.cancel()
        this.$emit('initData')
        this.$emit('getUniqueMenus')
      }).catch(() => {})
    },
    // 取消
    cancel(done) {
      Object.assign(this.$data, this.$options.data())
      typeof done === 'function' ? done() : this.dialogVisible = false
    },
    // 获取第一个分类的id
    getFirstCateId(data) {
      for (let i = 0; i < data.length; i++) {
        // 如果当前分类有子分类，则递归调用
        if (data[i].children && data[i].children.length) {
          return this.getFirstCateId(data[i].children)
        }
        // 如果当前分类没有子分类，则检查是否有cate_id，并返回
        if (data[i].id) {
          this.expandedKeys = [data[i].id]
          return // 返回ID以便外部可能也需要这个值
        }
      }
      // 如果没有找到任何cate_id，则返回null或undefined
      return null
    }
  }
}
</script>

<style scoped lang="scss">

.rule {
  display: flex;
  flex-wrap: wrap;
  overflow-y: scroll;
  height: max-content;
  max-height: 600px;
  flex: 1;
}
.tree::-webkit-scrollbar {
  width: 2px;
  background-color: #f5f5f5;
}
/*定义滚动条高宽及背景 高宽分别对应横竖滚动条的尺寸*/
.rule::-webkit-scrollbar {
  width: 10px;
  height: 10px;
  background-color: #f5f5f5;
}

/*定义滚动条轨道 内阴影+圆角*/
.rule::-webkit-scrollbar-track {
  border-radius: 4px;
  background-color: #f5f5f5;
}

/*定义滑块 内阴影+圆角*/
.rule::-webkit-scrollbar-thumb {
  border-radius: 4px;
  background-color: #ccc;
}

.rule-list {
  background-color: #f2f2f2;
  width: 48.5%;
  height: max-content;
  margin: 5px;
  border-radius: 3px;
  padding: 10px;
  color: #333;
  cursor: pointer;
  transition: all 0.1s;
}

.rule-list:hover {
  background-color: #76b6f6;
  color: #fff;
}

.rule-list div {
  white-space: nowrap;
}

.select-rule {
  background-color: #409EFF;
  color: #fff;
}
.route-list {
  display: flex;
  margin-top: 10px;

  .tree {
    width: 200px;
    overflow-y: scroll;
    max-height: 600px;
    ::v-deep .el-tree-node__children .el-tree-node .el-tree-node__content {
      padding-left: 14px !important;
    }
  }
}
.el-dropdown-link {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 12px;
  margin-left: 6px;
}
.el-icon-arrow-down {
  font-size: 12px;
}
</style>

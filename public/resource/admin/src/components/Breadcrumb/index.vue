<template>
  <el-breadcrumb class="app-breadcrumb" separator="/">
    <transition-group name="breadcrumb">
      <el-breadcrumb-item v-for="(item,index) in levelList" :key="item.path">
        <a v-if="index === 0" @click.prevent="handleLink(item)">{{ item.title }}</a>
        <span v-else class="no-redirect">{{ item.title }}</span>
      </el-breadcrumb-item>
    </transition-group>
  </el-breadcrumb>
</template>

<script>
import pathToRegexp from 'path-to-regexp'
import { mapState } from 'vuex'
import { getMenuOpen, getCurrentMenu } from '@/utils'

export default {
  data() {
    return {
      levelList: null
    }
  },
  computed: {
    ...mapState('user', ['menus'])
  },
  watch: {
    $route() {
      this.getBreadcrumb()
    }
  },
  created() {
    this.getBreadcrumb()
  },
  methods: {
    getBreadcrumb() {
      const parentMenus = this.getParentMenus()
      const currentMenu = this.getCurrentMenu()
      this.levelList = [...parentMenus, ...currentMenu]
    },
    // 获取面包屑当前菜单的父菜单
    getParentMenus() {
      const openMenus = getMenuOpen(this.$route, this.menus)
      const allMenuList = getCurrentMenu(this.menus, [])
      const selectMenu = []
      if (allMenuList.length > 0) {
        openMenus.forEach((i) => {
          allMenuList.forEach((a) => {
            if (i === a.path) {
              selectMenu.push(a)
            }
          })
        })
      }
      return selectMenu
    },
    // 获取面包屑的当前菜单
    getCurrentMenu() {
      const allMenuList = getCurrentMenu(this.menus, [])
      const selectMenu = []
      if (allMenuList.length > 0) {
        allMenuList.forEach((a) => {
          if (this.$route.path === a.path) {
            selectMenu.push(a)
          }
        })
      }
      return selectMenu
    },
    isDashboard(route) {
      const name = route && route.name
      if (!name) {
        return false
      }
      return name.trim().toLocaleLowerCase() === 'Dashboard'.toLocaleLowerCase()
    },
    pathCompile(path) {
      // To solve this problem https://github.com/PanJiaChen/vue-element-admin/issues/561
      const { params } = this.$route
      var toPath = pathToRegexp.compile(path)
      return toPath(params)
    },
    handleLink(item) {
      const { redirect, path } = item
      if (redirect) {
        this.$router.push(redirect)
        return
      }
      this.$router.push(this.pathCompile(path))
    }
  }
}
</script>

<style lang="scss" scoped>
.app-breadcrumb.el-breadcrumb {
  display: inline-block;
  font-size: 14px;
  line-height: 50px;
  margin-left: 8px;

  .no-redirect {
    color: #97a8be;
    cursor: text;
  }
}
</style>

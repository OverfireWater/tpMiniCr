<template>
  <div>
    <el-card class="box-card">
      <el-form ref="form" size="mini" :model="form" label-width="100px">
        <el-form-item label="父级菜单">
          <el-cascader :options="form.menu" :props="{ checkStrictly: true }" clearable />
        </el-form-item>
        <el-form-item label="表名">
          <el-input v-model="form.table_name" class="d-w-40" placeholder="填写表名" @blur="table_name_blur" />
        </el-form-item>
        <el-form-item label="菜单名">
          <el-input v-model="form.name" class="d-w-40" placeholder="填写菜单名" />
        </el-form-item>
        <el-form-item label="模块名">
          <el-input v-model="form.model_name" class="d-w-40" placeholder="填写模块名" />
        </el-form-item>
      </el-form>
      <div class="d-mt-10">
        <el-button size="mini" type="primary" @click="back">返回</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
import { createCrudForm } from '@/api/systemCodeGeneration'
export default {
  data() {
    return {
      form: {
        menu: [], // 菜单
        table_name: '', // 表名
        name: '', // 菜单名
        model_name: '' // 模块名
      }
    }
  },
  mounted() {
    this.initData()
  },
  methods: {
    initData() {
      createCrudForm().then(res => {
        this.form.menu = res.data
      }).catch(() => {})
    },
    // 表名失去焦点
    table_name_blur() {
      this.form.name = this.form.model_name = this.form.table_name
    },
    back() {
      this.$emit('back')
    }
  }
}
</script>

<style scoped lang="scss">

</style>

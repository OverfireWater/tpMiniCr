<template>
  <div class="d-px-20 d-border-left d-border-gray">
    <div class="d-mt-10 d-flex d-justify-between d-align-center">
      <span class="d-font-22">{{ formValidate.name }}</span>
      <div>
        <el-button v-if="isEdit" size="mini" type="primary" @click="handleSave">保存</el-button>
        <el-button v-else size="mini" type="primary" @click="isEdit = true">编辑</el-button>
        <el-button v-if="isEdit && !isCreate" size="mini" @click="isEdit = false">取消</el-button>
      </div>
    </div>
    <el-form
      ref="formValidate"
      class="d-mt-20 d-w-70"
      :model="formValidate"
      label-width="130px"
      size="mini"
      @submit.native.prevent
    >
      <div class="d-mb-20">接口信息</div>
      <el-form-item label="接口名称：" prop="name">
        <el-input
          v-if="isEdit"
          v-model.trim="formValidate.name"
          type="text"
          placeholder="请输入"
        />
        <span v-else>{{ formValidate.name || '' }}</span>
      </el-form-item>
      <!--      是否为资源路由-->
      <el-form-item v-if="isEdit && isCreate" label="是否为资源路由：" prop="name">
        <el-switch v-model="is_resource" :active-value="1" :inactive-value="0" />
      </el-form-item>
      <el-form-item v-if="!is_resource" label="请求类型：" prop="name">
        <el-select v-if="isEdit" v-model="formValidate.method" style="width: 120px">
          <el-option
            v-for="(item, index) in requestTypeList"
            :key="index"
            :value="item.value"
            :label="item.label"
          />
        </el-select>
        <span v-else class="d-p-5 d-border-radius" style="color: white" :style="{ backgroundColor: methodsColor(formValidate.method) }">
          {{ formValidate.method || '' }}
        </span>
      </el-form-item>
      <el-form-item label="功能描述：" prop="name">
        <el-input
          v-if="isEdit"
          v-model.trim="formValidate.describe"
          type="textarea"
          placeholder="请输入"
        />
        <span v-else class="text-area">{{ formValidate.describe || '--' }}</span>
      </el-form-item>
      <el-form-item v-if="isEdit" label="所属分类：" prop="name">
        <el-cascader
          :key="resetCascader"
          v-model="formValidate.cate_id"
          size="small"
          :options="formValidate.route_tree"
          :props="{ checkStrictly: true, multiple: false, emitPath: false, value: 'id', label: 'name' }"
          clearable
        />
      </el-form-item>
      <el-form-item label="路由地址：" prop="path">
        <el-input
          v-if="isEdit"
          v-model.trim="formValidate.path"
          type="text"
          placeholder="请输入"
        />
        <span v-else>{{ formValidate.path || '' }}</span>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { getRouteDetail, saveRouteApi, updateRouteApi } from '@/api/system'
export default {
  props: {
    methodsColor: {
      type: Function,
      default: null
    },
    parentInitData: {
      type: Function,
      default: null
    }
  },
  data() {
    return {
      isEdit: false, // 是否编辑
      isCreate: false, // 是否新增
      is_resource: false, // 是否为资源路由
      formValidate: {
        name: '',
        method: '',
        describe: '',
        cate_id: '',
        path: '',
        action: ''
      },
      requestTypeList: [
        { label: 'GET', value: 'GET' },
        { label: 'POST', value: 'POST' },
        { label: 'PUT', value: 'PUT' },
        { label: 'DELETE', value: 'DELETE' }
      ],
      resetCascader: 0
    }
  },
  methods: {
    initData(id) {
      this.getRouteDetail(id)
    },
    // 获取路由api详情
    getRouteDetail(id) {
      getRouteDetail(id).then(res => {
        this.resetCascader++
        if (this.isEdit) this.isEdit = false
        if (this.isCreate) this.isCreate = false
        this.formValidate = res.data
      }).catch(() => {})
    },
    // 保存
    handleSave() {
      this.formValidate.is_resource = this.is_resource
      const method = this.formValidate.id ? updateRouteApi : saveRouteApi
      method(this.formValidate).then(res => {
        this.$message.success(res.msg)
        const res_id = Array.isArray(res.data) ? res.data[0].id : res.data.id
        this.$emit('changeExpandKey', this.formValidate.id ? this.formValidate.id : res_id)
      }).catch(() => {})
    },
    // 重置表单
    resetForm() {
      this.is_resource = false
      this.$refs.formValidate.resetFields()
    }
  }
}
</script>

<style scoped lang="scss">
  ::v-deep label {
    font-weight: normal;
  }
</style>

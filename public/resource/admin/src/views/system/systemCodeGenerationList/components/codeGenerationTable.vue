<template>
  <div>
    <el-table :data="tableData" style="width: 100%" size="mini">
      <el-table-column label="表单字段名" min-width="150px">
        <template v-slot="{row}">
          <el-input v-model="row.table_column_name" :disabled="row.is_primary_key" size="mini" placeholder="输入表单字段名称" @blur="table_column_name_blur(row)" />
        </template>
      </el-table-column>
      <el-table-column label="表单类型" min-width="150px">
        <template v-slot="{row}">
          <el-select v-model="row.table_form_type" :disabled="row.is_primary_key" clearable size="mini" placeholder="请选择表单类型">
            <el-option v-for="(item, index) in formType" :key="index" :label="item.label" :value="item.value" />
          </el-select>
        </template>
      </el-table-column>
      <el-table-column label="必填" width="60px">
        <template v-slot="{row}">
          <el-switch v-model="row.is_required" :disabled="row.is_primary_key" size="mini" />
        </template>
      </el-table-column>
      <el-table-column label="查询方式" min-width="150px">
        <template v-slot="{row}">
          <el-select v-model="row.query_type" :disabled="row.is_primary_key" clearable size="mini" placeholder="请选择查询方式">
            <el-option v-for="(item, index) in searchType" :key="index" :label="item.label" :value="item.value" />
          </el-select>
        </template>
      </el-table-column>
      <el-table-column label="显示" width="60px">
        <template v-slot="{row}">
          <el-switch v-model="row.is_show" :disabled="row.is_primary_key" size="mini" />
        </template>
      </el-table-column>
      <el-table-column label="字段名" min-width="150px">
        <template v-slot="{row}">
          <el-input v-model="row.field" :disabled="row.is_primary_key" size="mini" placeholder="输入表单字段名称" />
        </template>
      </el-table-column>
      <el-table-column label="字段类型" min-width="150px">
        <template v-slot="{row}">
          <el-select v-model="row.field_type" :disabled="row.is_primary_key" size="mini" placeholder="请选择字段类型">
            <el-option v-for="(item, index) in fieldType" :key="index" :label="item" :value="item" />
          </el-select>
        </template>
      </el-table-column>
      <el-table-column label="长度" min-width="100px">
        <template v-slot="{row}">
          <el-input v-model="row.length" :disabled="row.is_primary_key" size="mini" placeholder="输入长度" />
        </template>
      </el-table-column>
      <el-table-column label="默认值" min-width="120px">
        <template v-slot="{row}">
          <el-input v-model="row.default_value" :disabled="row.is_primary_key" size="mini" placeholder="输入默认值" />
        </template>
      </el-table-column>
      <el-table-column label="描述" min-width="120px">
        <template v-slot="{row}">
          <el-input v-model="row.comment" :disabled="row.is_primary_key" size="mini" placeholder="输入描述" />
        </template>
      </el-table-column>
      <el-table-column label="关联表" min-width="150px">
        <template v-slot="{row}">
          <el-cascader
            v-model="row.hasOne"
            :disabled="row.is_primary_key"
            :options="tableList"
            :props="codeProps"
            size="mini"
            placeholder="请选择关联表"
            clearable
          />
        </template>
      </el-table-column>
      <el-table-column label="索引" width="60px">
        <template v-slot="{row}">
          <el-switch v-model="row.is_index" :disabled="row.is_primary_key" size="mini" />
        </template>
      </el-table-column>
      <el-table-column label="操作" fixed="right">
        <template v-slot="{row, $index}">
          <el-button v-if="!row.is_primary_key" type="primary" size="mini" @click="del(row, $index)">删除</el-button>
          <div v-else>----</div>
        </template>
      </el-table-column>
    </el-table>
    <el-button class="d-mt-20" type="primary" size="mini" @click="addRow">添加一行</el-button>
  </div>
</template>

<script>
import { getTableName } from '@/api/systemCodeGeneration'

export default {
  props: {
    crudFormRule: {
      type: Object,
      default: null
    },
    tableList: {
      type: Array,
      default: null
    }
  },
  data() {
    return {
      tableData: [
        {
          id: 1,
          table_column_name: 'ID',
          table_form_type: '',
          is_required: true,
          query_type: '=',
          is_show: true,
          field: 'id',
          field_type: 'int',
          length: 11,
          default_value: '',
          comment: '自增长ID',
          hasOne: '',
          is_index: true,
          is_primary_key: true,
          primary_key: 1
        }
      ],
      codeProps: {
        checkStrictly: true,
        label: 'value',
        lazy: true,
        lazyLoad(node, resolve) {
          const { label } = node
          if (label) {
            getTableName(label).then(res => {
              const { data } = res
              resolve(data)
            }).catch(() => {})
          }
        }
      }
    }
  },
  computed: {
    formType() {
      return this.crudFormRule?.form
    },
    searchType() {
      return this.crudFormRule?.search_type
    },
    fieldType() {
      return this.crudFormRule?.types
    }
  },
  methods: {
    table_column_name_blur(row) {
      if (!row.comment) {
        row.comment = row.table_column_name
      }
    },
    // 添加一行
    addRow() {
      if (!this.validateCodeForm()) return false
      const data = {
        table_column_name: '',
        table_form_type: '',
        is_required: false,
        query_type: '',
        is_show: true,
        field: '',
        field_type: '',
        length: '',
        default_value: '',
        comment: '',
        hasOne: '',
        is_index: false,
        is_primary_key: false
      }
      this.tableData.push(data)
    },
    del(row, index) {
      this.$confirm('确定删除吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.tableData.splice(index, 1)
      }).catch(() => {})
    },
    validateCodeForm() {
      const tableData = this.tableData[this.tableData.length - 1]
      if (!tableData.table_column_name) {
        this.$message.error('请填写表单字段')
        return false
      }
      if (!tableData.field) {
        this.$message.error('请填写字段名')
        return false
      }
      if (!tableData.field_type) {
        this.$message.error('请填写字段类型')
        return false
      }
      if (!tableData.length) {
        this.$message.error('请填写字段长度')
        return false
      }
      const isSome = this.tableData.some(item => {
        return item === tableData.field
      })
      if (isSome) {
        this.$message.error('字段名重复')
        return false
      }
      return true
    }
  }
}
</script>

<style scoped lang="scss">

</style>

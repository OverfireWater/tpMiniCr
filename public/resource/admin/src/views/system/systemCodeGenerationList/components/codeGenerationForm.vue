<template>
  <div>
    <el-card class="d-mb-10">
      <el-button size="mini" type="primary" @click="back">返回</el-button>
    </el-card>
    <el-card>
      <el-steps :active="active" align-center finish-status="finish" class="d-m-auto d-w-80 d-mb-20">
        <el-step title="基础信息" />
        <el-step title="字段配置" />
      </el-steps>
      <el-form v-show="active === 0" ref="form" :rules="rules" size="mini" :model="form" label-width="100px">
        <el-form-item label="父级菜单" prop="menu">
          <el-cascader v-model="form.menu" :options="menuList" class="d-w-40" :props="{ checkStrictly: true }" clearable />
        </el-form-item>
        <el-form-item label="表名" prop="table_name">
          <el-input v-model="form.table_name" class="d-w-40" placeholder="填写表名,下面表单会自动生成" @blur="table_name_blur" />
        </el-form-item>
        <el-form-item label="菜单名" prop="name">
          <el-input v-model="form.name" class="d-w-40" placeholder="填写菜单名" />
        </el-form-item>
        <el-form-item label="模块名" prop="model_name">
          <el-input v-model="form.model_name" class="d-w-40" placeholder="填写模块名" />
        </el-form-item>
        <!--生成文件路径-->
        <el-form-item label="控制器" prop="make_path.controller">
          <el-input v-model="form.make_path.controller" />
        </el-form-item>
        <el-form-item label="模型" prop="make_path.model">
          <el-input v-model="form.make_path.model" />
        </el-form-item>
        <el-form-item label="dao层" prop="make_path.dao">
          <el-input v-model="form.make_path.dao" />
        </el-form-item>
        <el-form-item label="service层" prop="make_path.service">
          <el-input v-model="form.make_path.service" />
        </el-form-item>
        <el-form-item label="路由" prop="make_path.route">
          <el-input v-model="form.make_path.route" />
        </el-form-item>
        <el-form-item label="前端路由" prop="make_path.router">
          <el-input v-model="form.make_path.router" />
        </el-form-item>
        <el-form-item label="前端api" prop="make_path.api">
          <el-input v-model="form.make_path.api" />
        </el-form-item>
        <el-form-item label="前端页面" prop="make_path.view">
          <el-input v-model="form.make_path.view" />
        </el-form-item>
        <!--生成文件路径-->
      </el-form>
      <code-generation-table v-show="active === 1" :crud-form-rule="crudFormRule" :table-list="tableList" />
      <div v-show="active === 2">
        <div class="i-success">
          <i class="el-icon-check primary-color d-border-radius-circle d-p-20" />
          <div>数据填写完成，点击完成生成crud</div>
        </div>
      </div>
      <div class="d-text-center d-mt-30">
        <el-button :disabled="active === 0" size="mini" @click="up">上一步</el-button>
        <el-button v-show="active !== 2" type="primary" size="mini" @click="next">下一步</el-button>
        <el-button v-show="active === 2" type="primary" size="mini" @click="finish">完成</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
import { createCrudForm } from '@/api/systemCodeGeneration'
import { toCamelCase } from '@/utils'
import codeGenerationTable from './codeGenerationTable'
export default {
  components: {
    codeGenerationTable
  },
  data() {
    return {
      // 菜单列表
      menuList: [],
      // 创建crud表单时的规则
      crudFormRule: {},
      // 表单列表
      tableList: [],
      form: {
        menu: [], // 菜单
        table_name: '', // 表名
        name: '', // 菜单名
        model_name: '', // 模块名
        make_path: {
          controller: 'app\\adminapi\\controller\\crud\\',
          model: 'app\\model\\crud\\',
          dao: 'app\\dao\\crud\\',
          route: 'app\\adminapi\\route\\crud\\',
          service: 'app\\services\\crud\\',
          router: 'router\\modules\\crud\\',
          api: 'api\\crud\\',
          view: 'pages\\crud\\test\\'
        }
      },
      // 表单验证
      rules: {
        menu: [{ required: true, message: '请选择父级菜单', trigger: 'change' }],
        table_name: [{ required: true, message: '请填写表名', trigger: 'blur' }],
        name: [{ required: true, message: '请填写菜单名', trigger: 'blur' }],
        model_name: [{ required: true, message: '请填写模块名', trigger: 'blur' }],
        'make_path.controller': [{ required: true, message: '请填写控制器', trigger: 'blur' }],
        'make_path.model': [{ required: true, message: '请填写模型', trigger: 'blur' }],
        'make_path.dao': [{ required: true, message: '请填写dao层', trigger: 'blur' }],
        'make_path.service': [{ required: true, message: '请填写service层', trigger: 'blur' }],
        'make_path.route': [{ required: true, message: '请填写路由', trigger: 'blur' }],
        'make_path.router': [{ required: true, message: '请填写前端路由', trigger: 'blur' }],
        'make_path.api': [{ required: true, message: '请填写前端api', trigger: 'blur' }],
        'make_path.view': [{ required: true, message: '请填写前端页面', trigger: 'blur' }]
      },
      active: 1 // 步骤
    }
  },
  mounted() {
    this.initData()
  },
  methods: {
    initData() {
      createCrudForm().then(res => {
        const { data: { menuList, formRule, tableList }} = res
        this.menuList = menuList
        this.crudFormRule = formRule
        this.tableList = tableList
      }).catch(() => {})
    },
    // 表名失去焦点
    table_name_blur() {
      const table_name = this.form.table_name
      if (!table_name) return false
      this.form.name = this.form.model_name = table_name
      this.form.make_path = {
        controller: `app\\adminapi\\controller\\crud\\${toCamelCase(table_name)}.php`,
        model: `app\\model\\crud\\${toCamelCase(table_name)}.php`,
        dao: `app\\dao\\crud\\${toCamelCase(table_name)}Dao.php`,
        route: `app\\adminapi\\route\\crud\\${table_name}.php`,
        service: `app\\services\\crud\\${toCamelCase(table_name)}Services.php`,
        router: `router\\modules\\crud\\${table_name}.js`,
        api: `api\\crud\\${table_name}.js`,
        view: `pages\\crud\\${table_name}\\index.vue`
      }
    },
    // 上一步
    up() {
      if (this.active-- <= 0) this.active = 0
    },
    // 下一步
    next() {
      this.$refs.form.validate(valid => {
        if (valid) {
          if (this.active <= 1) this.active++
        }
      })
    },
    // 完成
    finish() {

    },
    back() {
      this.$emit('back')
    }
  }
}
</script>

<style scoped lang="scss">
.i-success {
  text-align: center;

  i {
    border: 3px solid #409EFF;
    font-size: 100px;
  }
}
</style>

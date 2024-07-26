<?php
declare (strict_types = 1);

namespace app\adminapi\vaildate\system;

use think\Validate;

class SystemCodeGenerationValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'make_path' => 'require',
        'menu' => 'require',
        'model_name' => 'require',
        'menu_name' => 'require',
        'table_name' => 'require',
        'tableData' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'make_path.require' => '请填写生成路径',
        'menu.require' => '请填写菜单',
        'model_name.require' => '请填写模型名称',
        'menu_name.require' => '请填写菜单名称',
        'table_name.require' => '请填写表名',
        'tableData.require' => '请填写表数据'
    ];
}

<?php

namespace app\adminapi\vaildate\system\admin;

use think\Validate;

class SystemAdminValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'account' => ['require', 'alphaDash'],
        'conf_pwd' => 'require',
        'password' => 'require',
        'real_name' => 'require',
        'roles' => ['require', 'array'],
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'account.require' => '400033',
        'account.alphaDash' => '400034',
        'conf_pwd.require' => '400263',
        'password.require' => '400256',
        'real_name.require' => '400035',
        'roles.require' => '400036',
        'roles.array' => '400037',
    ];

    protected $scene = [
        'get' => ['account', 'password'],
        'update' => ['account', 'roles'],
    ];


}

<?php
declare(strict_types=1);
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
        'password' => 'require|min:6',
        'real_name' => 'require',
        'roles' => 'require',
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
        'password.require' => '400256',
        'password.min' => '400257',
        'real_name.require' => '400035',
        'roles.require' => '400036',
    ];

    protected $scene = [
        'get' => ['account', 'password'],
        'update' => ['account', 'roles'],
    ];


}

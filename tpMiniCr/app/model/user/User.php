<?php
declare(strict_types=1);

namespace app\model\user;

use think\Model;

class User extends Model
{

    protected $pk = 'uid';

    protected $name = 'user';

    protected array $insert = ['add_time', 'add_ip', 'last_time', 'last_ip'];

    protected $hidden = [
        'add_ip', 'account', 'clean_time', 'last_ip', 'pwd'
    ];

    /**
     * 自动转类型
     */
    protected $type = [
        'birthday' => 'int'
    ];
    protected $updateTime = false;
}

<?php
declare(strict_types=1);

namespace app\model\system\admin;

use think\Model;

class SystemAdmin extends Model
{
    protected $pk = 'id';
    protected $name = 'system_admin';

    protected array $insert = ['add_time'];
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $type = [
        'last_time' => 'timestamp'
    ];
}

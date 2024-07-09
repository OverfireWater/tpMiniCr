<?php

namespace app\model\system\admin;

use think\Model;

class SystemAdmin extends Model
{
    protected $pk = 'id';
    protected $name = 'system_admin';

    protected array $insert = ['add_time'];
}
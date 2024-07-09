<?php

namespace app\api\dao;

use app\model\user\User;
use base\BaseDao;
use think\Model;

class UserDao extends BaseDao
{
    protected function setModel(): string
    {
        return User::class;
    }
}
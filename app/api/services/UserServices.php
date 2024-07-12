<?php

namespace app\api\services;

use app\api\dao\UserDao;
use base\BaseServices;
use Throwable;


class UserServices extends BaseServices
{


    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户信息
     * @param int $uid
     * @param string $field
     * @return mixed
     * @throws Throwable
     */
    public function getUserInfo(int $uid, string $field = '*'): mixed
    {
        if (is_string($field)) $field = explode(',', $field);
        return $this->dao->get($uid, $field);
    }
}

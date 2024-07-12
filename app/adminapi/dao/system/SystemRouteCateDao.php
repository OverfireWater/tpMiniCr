<?php

namespace app\adminapi\dao\system;

use app\model\system\SystemRouteCate;
use base\BaseDao;

class SystemRouteCateDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemRouteCate::class;
    }
}
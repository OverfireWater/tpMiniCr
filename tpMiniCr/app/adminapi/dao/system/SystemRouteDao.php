<?php

namespace app\adminapi\dao\system;

use app\model\system\SystemRoute;
use base\BaseDao;

class SystemRouteDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemRoute::class;
    }
}
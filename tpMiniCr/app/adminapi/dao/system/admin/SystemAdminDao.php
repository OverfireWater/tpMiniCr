<?php

namespace app\adminapi\dao\system\admin;

use app\model\system\admin\SystemAdmin;
use base\BaseDao;
use think\Model;
use Throwable;

class SystemAdminDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemAdmin::class;
    }

    /**
     * @throws Throwable
     */
    public function accountByAdmin(string $account ): Model
    {
        return $this->search(['account' => $account, 'is_del' => 0])->find();
    }
}
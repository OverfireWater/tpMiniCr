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

    /**
     * 获取管理员列表信息
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws Throwable
     */
    public function getAdminList(array $where, int $page, int $limit): array
    {
        return $this->search($where)->page($page, $limit)->select()->hidden(['pwd'])->toArray();
    }
}

<?php

namespace app\adminapi\dao\system\admin;

use app\model\system\SystemRole;
use base\BaseDao;
use Throwable;

class SystemRoleDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemRole::class;
    }

    /**
     * @param array $where
     * @param string|null $field
     * @param string|null $key
     * @return array
     * @throws Throwable
     */
    public function getRole(array $where = [], string $field = null, string $key = null): array
    {
        return $this->search($where)->column($field ?? 'role_name', $key ?? 'id');
    }

    /**
     * 获取角色列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws Throwable
     */
    public function getRoleList(array $where, int $page, int $limit): array
    {
        return $this->search($where)->page($page, $limit)->select()->toArray();
    }
}

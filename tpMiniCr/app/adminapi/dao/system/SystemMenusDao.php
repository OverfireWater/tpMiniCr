<?php

namespace app\adminapi\dao\system;

use app\model\system\SystemMenus;
use base\BaseDao;
use think\Collection;
use think\db\BaseQuery;
use Throwable;

class SystemMenusDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemMenus::class;
    }

    /**
     * 获取权限菜单列表
     * @param array $where
     * @param array|null $field
     * @return array|Collection
     * @throws Throwable
     */
    public function getMenusRole(array $where, ?array $field = []): array|Collection
    {
        if (!$field) {
            $field = ['id', 'menu_name', 'icon', 'pid', 'sort', 'menu_path', 'is_show', 'header', 'is_header', 'is_show_path', 'is_show'];
        }
        return $this->search($where)->field($field)->order('sort DESC,id DESC')->failException(false)->select();
    }

    /**
     * 获取菜单中的唯一权限
     * @param array $where
     * @return array
     * @throws Throwable
     */
    public function getMenusUnique(array $where): array
    {
        $where['no_model'] = sys_config('model_checkbox', ['seckill', 'bargain', 'combination']);
        return $this->search($where)->where('unique_auth', '<>', '')->column('unique_auth', '');
    }

    /**
     * 获取后台菜单列表并分页
     * @param array $where
     * @param array $field
     * @return array|Collection|BaseQuery
     * @throws Throwable
     */
    public function getMenusList(array $where, array $field = ['*']): array|Collection|BaseQuery
    {
        $where = array_merge($where, ['is_del' => 0]);
        return $this->search($where)->field($field)->order('sort DESC,id ASC')->select();
    }

    /**
     * 查询菜单的某个列
     * @throws Throwable
     */
    public function column(array $where, array|string $field): array
    {
        return $this->search($where)->column($field);
    }

}
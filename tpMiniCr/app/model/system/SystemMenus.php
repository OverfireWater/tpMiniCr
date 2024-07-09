<?php
declare(strict_types=1);
namespace app\model\system;

use think\Model;

class SystemMenus extends Model
{
    protected $pk = 'id';
    protected $name = 'system_menus';

    /**
     * 菜单规格搜索
     * @param Model $query
     * @param $value
     */
    public function searchRouteAttr(Model $query, $value): void
    {
        $query->where('auth_type', 1)->where('is_del', 0);
        if ($value) {
            $query->whereIn('id', $value);
        }
    }

    /**
     * is_show_path
     * @param Model $query
     * @param $value
     */
    public function searchIsShowPathAttr(Model $query, $value): void
    {
        $query->where('is_show_path', $value);
    }

    public function searchPidAttr(Model $query, $value): void
    {
        $query->where('pid', $value);
    }

    public function searchUniqueAttr(Model $query, $value): void
    {
        $query->where('is_del', 0);
        if ($value) {
            $query->whereIn('id', $value);
        }
    }

    /**
     * 菜单类型搜索
     */
    public function searchAuthTypeAttr(Model $query, $value): void
    {
        if (!$value) return;
        if ($value === 3) {
            $query->whereIn('auth_type', [1, 3]);
        } else if ($value === 2) {
            $query->whereIn('auth_type', [3, 2]);
        } else {
            $query->where('auth_type', $value);
        }
    }
}
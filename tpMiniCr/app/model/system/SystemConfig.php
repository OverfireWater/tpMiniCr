<?php
declare(strict_types=1);
namespace app\model\system;

use think\db\Query;
use think\Model;

/**
 * 系统配置模型
 */
class SystemConfig extends Model
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_config';

    /**
     * 菜单名搜索器
     * @param Model $query
     * @param $value
     */
    public function searchMenuNameAttr(Model $query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('menu_name', $value);
        } else {
            $query->where('menu_name', $value);
        }
    }

    /**
     * tab id 搜索
     * @param Model $query
     * @param $value
     */
    public function searchTabIdAttr(Model $query, $value): void
    {
        $query->where('config_tab_id', $value);
    }

    /**
     * 状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr(Model $query, $value): void
    {
        $query->where('status', $value ?: 1);
    }

    /**
     * value搜索器
     * @param Model $query
     * @param $value
     */
    public function searchValueAttr(Model $query, $value): void
    {
        $query->where('value', $value);
    }
}

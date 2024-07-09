<?php
declare(strict_types=1);
namespace app\model\system;

use think\db\Query;
use think\Model;

class SystemRoute extends Model
{
    protected $pk = 'id';
    protected $name = 'system_route';

    /**
     * @param Model $query
     * @param string $value
     * @return void
     */
    public function searchCateIdAttr(Model $query, string $value): void
    {
        $query->where('cate_id', $value);
    }

    /**
     * @param Model $query
     * @param string $value
     * @return void
     */
    public function searchAppNameAttr(Model $query, string $value): void
    {

        $query->where('app_name', $value);
    }
}
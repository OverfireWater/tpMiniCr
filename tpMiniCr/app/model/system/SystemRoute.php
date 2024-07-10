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
     * @param Query $query
     * @param int $value
     * @return void
     */
    public function searchCateIdAttr(Query $query, int $value): void
    {
        $query->where('cate_id', $value);
    }

    /**
     * @param Query $query
     * @param string $value
     * @return void
     */
    public function searchAppNameAttr(Query $query, string $value): void
    {

        $query->where('app_name', $value);
    }
}

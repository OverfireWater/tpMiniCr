<?php
declare(strict_types=1);
namespace app\model\system;

use think\Model;
use think\model\relation\HasMany;

class SystemRouteCate extends Model
{
    protected $pk = 'id';
    protected $name = 'system_route_cate';

    public function children(): HasMany
    {
        return $this->hasMany(SystemRoute::class, 'cate_id', 'id')->order('add_time desc');
    }
}
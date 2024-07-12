<?php

namespace app\adminapi\services\system;

use app\adminapi\dao\system\SystemRouteCateDao;
use base\BaseServices;
use exceptions\ApiException;
use services\CacheService;
use think\facade\Db;

class SystemRouteCateServices extends BaseServices
{
    // 缓存前缀
    protected const ROUTE_PRE = 'SystemRouteTree_';
    public function __construct(SystemRouteCateDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 删除分类
     * @param int $id
     * @param string $app_name
     * @return bool
     */
    public function deleteCate(int $id, string $app_name): bool
    {
        $data = $this->selectList(['app_name' => $app_name, 'id|pid' => $id], with: [
            'children' => function ($query) use ($app_name) {
                $query->where('app_name', $app_name);
            }
        ])->toArray();
        $this->getCountChildren($data);
        $flag = $this->dao->destroy(function ($query) use($id, $app_name) {
            $query->where([['id|pid', '=', $id], 'app_name' => $app_name]);
        });
        if (!$flag) throw new ApiException('删除失败');
        return CacheService::delete(self::ROUTE_PRE . $app_name);
    }

    /**
     * 判断是否存在子级
     * @param array $array
     * @return void
     */
    private function getCountChildren(array $array): void
    {
        foreach ($array as $item) {
            if (isset($item['cate_id'])) {
                throw new ApiException('存在子级接口，无法删除');
            } else if (!empty($item['children'])) {
                $this->getCountChildren($item['children']);
            }
        }
    }


    /**
     * 保存分类名称
     * @param array $data
     * @return bool
     */
    public function saveRouteCate(array $data): bool
    {
        $flag = $this->dao->save($data);
        if (!$flag->id) throw new ApiException('保存失败');
        return CacheService::delete(self::ROUTE_PRE . $data['app_name']);
    }

    /**
     * 修改分类
     */
    public function updateRouteCate(int $id, array $data): bool
    {
        $flag = $this->dao->update($id, $data);
        if (!$flag['name']) throw new ApiException('修改失败');
        return CacheService::delete(self::ROUTE_PRE . $data['app_name']);
    }
}

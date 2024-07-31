<?php
declare(strict_types=1);

namespace app\adminapi\services\system;

use app\adminapi\dao\system\SystemRouteDao;
use base\BaseServices;
use exceptions\ApiException;
use services\CacheService;
use think\Collection;
use Throwable;

class SystemRouteServices extends BaseServices
{
    // 缓存前缀
    protected const ROUTE_PRE = 'SystemRouteTree_';

    public function __construct(SystemRouteDao $systemRouteDao)
    {
        $this->dao = $systemRouteDao;
    }

    /**
     * @param string $app_name
     * @return array
     */
    public function getTree(string $app_name): array
    {
        return CacheService::remember(self::ROUTE_PRE . $app_name, function () use ($app_name) {
            $routeCate = app()->make(SystemRouteCateServices::class);
            $list = $routeCate->selectList(['app_name' => $app_name], with: [
                'children' => function ($query) use ($app_name) {
                    $query->where('app_name', $app_name);
                }
            ])->toArray();
            $list = $this->changeId($list);
            return app()->make(SystemMenusServices::class)->get_tree_children($list);
        });
    }

    // 改变cate_id 为pid
    private function changeId(array &$list): array
    {
        foreach ($list as &$item) {
            if (isset($item['cate_id'])) {
                $item['pid'] = $item['cate_id'];
            } else if (!empty($item['children'])) {
                $this->changeId($item['children']);
            }
        }

        return $list;
    }

    /**
     * 删除分类下的所有api
     * @throws Throwable
     */
    public function deleteApiByCateId(int $id, string $app_name): bool
    {
        $count = $this->dao->count(['cate_id' => $id, 'app_name' => $app_name]);
        if (!$count) throw new ApiException('分类下没有api');

        $flag = $this->dao->delete(['cate_id' => $id, 'app_name' => $app_name]);
        if (!$flag) return false;
        return CacheService::delete(self::ROUTE_PRE . $app_name);
    }


    /**
     * 保存api
     * @param array $data
     * @return mixed
     * @throws Throwable
     */
    public function save(array $data): mixed
    {
        if ($data['is_resource']) return $this->saveResourceRoute($data['name'], $data['path'], (int)$data['cate_id'], $data['app_name'], $data['describe']);
        $model = parent::save($data);
        if ($model && CacheService::delete(self::ROUTE_PRE . $data['app_name'])) {
            return $model;
        }
        return false;
    }

    /**
     * 保存为资源路由
     * @param string $name
     * @param string $path
     * @param int $cate_id
     * @param string $app_name
     * @param string $describe
     * @return Collection
     * @throws Throwable
     */
    public function saveResourceRoute(string $name, string $path, int $cate_id, string $app_name = 'adminapi', string $describe = ''): Collection
    {
        $array = [];
        if (!$describe) $describe = $name;
        $resource_name = [
            'index' => '获取' . $name,
            'create' => '获取' . $name . '创建表单',
            'save' => '保存' . $name,
            'edit' => '获取修改' . $name,
            'read' => '查看' . $name,
            'update' => '修改' . $name,
            'delete' => '删除' . $name,
        ];
        $resource_path = [
            'index' => $path,
            'create' => $path . '/create',
            'save' => $path,
            'edit' => $path . '/<id>/edit',
            'read' => $path . '/<id>',
            'update' => $path . '/<id>',
            'delete' => $path . '/<id>',
        ];
        $resource_method = [
            'index' => 'GET',
            'create' => 'GET',
            'save' => 'POST',
            'edit' => 'GET',
            'read' => 'GET',
            'update' => 'PUT',
            'delete' => 'DELETE',
        ];
        foreach ($resource_name as $k => $v) {
            $array[] = [
                'name' => $v,
                'path' => $resource_path[$k],
                'method' => $resource_method[$k],
                'app_name' => $app_name,
                'describe' => $describe,
                'cate_id' => $cate_id,
                'add_time' => date('Y-m-d H:i:s')
            ];
        }
        CacheService::delete(self::ROUTE_PRE . $app_name);
        return $this->dao->saveAll($array);
    }

    /**
     * 修改api
     */
    public function update($id, array $data): bool
    {
        if (parent::update($id, $data)) return CacheService::delete(self::ROUTE_PRE . $data['app_name']);
        return false;
    }
}

<?php
declare(strict_types=1);

namespace app\adminapi\controller\v1\system;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\SystemMenusServices;
use app\adminapi\services\system\SystemRouteCateServices;
use app\adminapi\services\system\SystemRouteServices;
use app\Request;
use services\CacheService;
use think\Response;
use Throwable;

class SystemRoute extends AuthBaseController
{
    public function __construct(
        protected SystemRouteServices $services,
        protected Request             $request
    )
    {
        parent::__construct();
    }

    /**
     * 获取接口信息
     * @param int $id
     * @param SystemRouteCateServices $systemRouteCateServices
     * @return Response
     */
    public function read(int $id, SystemRouteCateServices $systemRouteCateServices): Response
    {
        $data = $this->services->get($id);
        if ($data) {
            $routeNameList = $systemRouteCateServices->selectList(['app_name' => $data->app_name], 'id,pid, name')->toArray();
            $data['route_tree'] = app()->make(SystemMenusServices::class)->get_tree_children($routeNameList);
            return app('json')->success($data->toArray());
        }
        return app('json')->fail(100011);
    }

    /**
     * 删除接口
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        CacheService::clear();
        if ($this->services->delete($id)) {
            return app('json')->success(100002);
        }
        return app('json')->fail(100008);
    }

    /**
     * 保存接口
     * @return Response
     * @throws Throwable
     */
    public function save(): Response
    {
        $data = $this->request->getMore([
            ['app_name', 'adminapi'],
            ['describe', ''],
            ['is_resource', 0],
            'method',
            'name',
            'path',
            'cate_id'
        ], false);
        $data['add_time'] = date('Y-m-d H:i:s');
        if (empty($data['name']) || empty($data['path'])) return app('json')->fail(100026);
        $model = $this->services->save($data);
        if ($model) {
            return app('json')->success(100000, $model->toArray());
        }
        return app('json')->fail(100006);
    }

    /**
     * 修改接口信息
     * @param int $id
     * @return Response
     */
    public function update(int $id): Response
    {
        $data = $this->request->getMore([
            ['app_name', 'adminapi'],
            ['describe', ''],
            ['method', ''],
            ['name', ''],
            ['path', ''],
            'cate_id'
        ], false);
        if ($this->services->update($id, $data)) {
            return app('json')->success(100000);
        }
        return app('json')->fail(100006);
    }


    /**
     * 获取分类下的所有接口
     * @param string $app_name
     * @return Response
     */
    public function tree(string $app_name): Response
    {
        $data = $this->services->getTree($app_name);
        return app('json')->success($data);
    }

    /**
     * 删除分类下的所有接口
     * @throws Throwable
     */
    public function deleteAllApi(int $id, string $app_name): Response
    {

        if ($this->services->deleteApiByCateId($id, $app_name)) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }
}

<?php
declare (strict_types=1);

namespace app\adminapi\controller\v1\system;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\SystemMenusServices;
use app\adminapi\services\system\SystemRouteCateServices;
use app\adminapi\services\system\SystemRouteServices;
use app\Request;
use think\Response;
use Throwable;

class SystemMenus extends AuthBaseController
{
    public function __construct(
        protected Request             $request,
        protected SystemMenusServices $services
    )
    {
        parent::__construct();
    }


    /**
     * 显示资源列表
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {

        return app('json')->success($this->services->getMenusList(['auth_type' => '']));
    }

    /**
     * 创建表单需要的数据
     * @return Response
     * @throws Throwable
     */
    public function create(): Response
    {
        $menusList = $this->services->getFormCascaderMenus();
        return app('json')->success($menusList);
    }

    /**
     * 保存新建的资源
     * @return Response
     */
    public function save(): Response
    {
        $data = $this->request->getMore([
            ['menu_name', ''],
            ['controller', ''],
            ['module', 'admin'],
            ['action', ''],
            ['icon', ''],
            ['params', ''],
            ['path', []],
            ['menu_path', ''],
            ['api_url', ''],
            ['methods', ''],
            ['unique_auth', ''],
            ['header', ''],
            ['is_header', 0],
            ['pid', 0],
            ['sort', 0],
            ['auth_type', 0],
            ['access', 1],
            ['is_show', 0],
            ['is_show_path', 0],
            ['mark', '']
        ], false);
        $data['is_show_path'] = $data['is_show'];
        $data['path'] = implode('/', $data['path']);
        if (!$data['menu_name'])
            return app('json')->fail(400198);
        if ($this->services->save($data)) {
            return app('json')->success(100021);
        } else {
            return app('json')->fail(100022);
        }
    }

    /**
     * 获取一条菜单权限信息
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function read(int $id): Response
    {
        return app('json')->success($this->services->find($id));
    }

    /**
     * 保存更新的资源
     * @param int $id
     * @return Response
     */
    public function update(int $id): Response
    {
        $data = $this->request->getMore([
            'menu_name',
            'controller',
            ['module', 'admin'],
            'action',
            'params',
            ['icon', ''],
            ['menu_path', ''],
            ['api_url', ''],
            ['methods', ''],
            ['unique_auth', ''],
            ['path', ''],
            ['sort', 0],
            ['pid', 0],
            ['is_header', 0],
            ['header', ''],
            ['auth_type', 0],
            ['access', 1],
            ['is_show', 0],
            ['is_show_path', 0],
            ['mark', '']
        ], false);
        $data['is_show_path'] = $data['is_show'];
        if (!$data['menu_name']) return app('json')->fail(400198);

        if ((int)$data['pid'] === $id) return app('json')->fail('不能选择自己');

        if (empty($data['path'])) {
            $data['pid'] = 0;
            $data['path'] = '';
        } else {
            $data['path'] = implode('/', $data['path']);
        }
        if ($this->services->update($id, $data)) {
            return app('json')->success(100001);
        } else {
            return app('json')->fail(10007);
        }
    }

    /**
     * 删除指定菜单
     *
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function delete(int $id): Response
    {
        if ($this->services->deleteMenus($id)) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }

    /**
     * 获取当前用户的菜单和权限
     * @return Response
     * @throws Throwable
     */
    public function uniqueMenus(): Response
    {
        [$menusList, $unique] = $this->services->getMenusAndUniqueList($this->adminRole, $this->adminInfo['level']);
        return app('json')->success(['menus' => $menusList, 'unique' => $unique]);
    }

    /**
     * 改变菜单的显隐
     * @param int $id
     * @return Response
     */
    public function changeShowMenus(int $id): Response
    {
        $data = $this->request->getMore(['is_show'], false);
        $data['is_show_path'] = $data['is_show'];
        if ($this->services->update($id, $data)) {
            return app('json')->success(100001);
        } else {
            return app('json')->fail(100007);
        }
    }

    /**
     * 保存选好的路由权限
     */
    public function saveSelectRouteRule(): Response
    {
        [$menus] = $this->request->getMore(['menus']);
        if (!$menus) {
            return app('json')->fail(100026);
        }
        $data = [];

        $uniqueAuthAll = $this->services->getColumn(['is_del' => 0, 'is_show' => 1], 'unique_auth');
        $uniqueAuthAll = array_filter($uniqueAuthAll, function ($item) {
            return !!$item;
        });

        $uniqueAuthAll = array_unique($uniqueAuthAll);

        $uniqueFn = function ($path) use (&$uniqueAuthAll) {
            $attPath = explode('/', $path);
            $uniqueAuth = '';

            if ($attPath) {
                $pathData = [];
                foreach ($attPath as $vv) {
                    if (!str_contains($vv, '<')) {
                        $pathData[] = $vv;
                    }
                }
                $uniqueAuth = implode('-', $pathData);
            }

            if (in_array($uniqueAuth, $uniqueAuthAll)) {
                $uniqueAuth .= '-' . uniqid();

            }

            $uniqueAuthAll[] = $uniqueAuth;
            return $uniqueAuth;
        };

        $pid = $menus[0]['pid'];
        $api_url = $this->services->getColumn(['is_del' => 0, 'is_show' => 1, 'pid' => $pid], 'api_url');
        foreach ($menus as $menu) {
            if (empty($menu['menu_name'])) {
                return app('json')->fail(400198);
            }
            if (!in_array($menu['api_url'], $api_url)) {
                $data[] = [
                    'methods' => $menu['method'],
                    'menu_name' => $menu['menu_name'],
                    'unique_auth' => !empty($menu['unique_auth']) ? $menu['unique_auth'] : $uniqueFn($menu['api_url']),
                    'api_url' => $menu['api_url'],
                    'pid' => $menu['pid'],
                    'auth_type' => 2,
                    'path' => implode('/', $menu['path']),
                    'is_show' => 1,
                    'is_show_path' => 1,
                ];
            }
        }
        $this->services->saveAll($data);
        return app('json')->success(100021);
    }

    /**
     * 创建路由权限表单数据
     */
    public function routeCate(SystemRouteCateServices $systemRouteCateServices): Response
    {
        $data = $systemRouteCateServices->selectList(['app_name' => 'adminapi'])->toArray();
        return app('json')->success($this->services->get_tree_children($data));
    }

    /**
     * 获取路由分类下的api列表
     * @param int $id
     * @param SystemRouteServices $systemRouteServices
     * @return Response
     */
    public function routeList(int $id, SystemRouteServices $systemRouteServices): Response
    {
        $data = $systemRouteServices->selectList(['cate_id' => $id, 'app_name' => 'adminapi'])->toArray();
        return app('json')->success($data);
    }
}

<?php

namespace app\adminapi\controller\v1\setting;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\admin\SystemRoleServices;
use app\adminapi\services\system\SystemMenusServices;
use app\Request;
use services\CacheService;
use think\Response;
use Throwable;

class SystemRole extends AuthBaseController
{
    public function __construct(
        protected Request $request,
        protected SystemRoleServices $services
    )
    {
        parent::__construct();
    }

    /**
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        $data = $this->services->getRoleList([]);
        return app('json')->success($data);
    }

    /**
     * 创建表单所需的数据
     * @param SystemMenusServices $systemMenusServices
     * @return Response
     * @throws Throwable
     */
    public function create(SystemMenusServices $systemMenusServices): Response
    {
        return app('json')->success($systemMenusServices->getFormCascaderMenus(''));
    }


    /**
     * @return Response
     */
    public function save(): Response
    {
        $data = $this->request->getMore([
            ['role_name', ''],
            ['rules', []],
            ['status', 1]
        ], false);

        if (!$data['role_name']) return app('json')->fail(100100);

        $data['rules'] = implode(',', $data['rules']);
        $data['level'] = 1;
        $this->services->save($data);
        CacheService::clear();
        return app('json')->success(100000);
    }

    /**
     * 编辑
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function read(int $id): Response
    {
        return app('json')->success($this->services->getRoleInfo($id));
    }

    /**
     * @param int $id
     * @return Response
     */
    public function update(int $id): Response
    {
        $data = $this->request->getMore([
            ['role_name', ''],
            ['rules', []],
            ['status', 1]
        ], false);
        $data['rules'] = implode(',', $data['rules']);
        $this->services->update($id, $data);
        CacheService::clear();
        return app('json')->success(100000);
    }

    /**
     * 修改角色状态
     */
    public function updateStatus(int $id): Response
    {
        $data = $this->request->getMore(['status'], false);
        $this->services->update($id, $data);
        CacheService::clear();
        return app('json')->success(100000);
    }

    /**
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
}

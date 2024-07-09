<?php

namespace app\adminapi\controller\v1\system;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\SystemRouteCateServices;
use app\Request;
use think\Response;

class SystemRouteCate extends AuthBaseController
{
    public function __construct(
        protected Request $request,
        protected SystemRouteCateServices $services
    )
    {
        parent::__construct();
    }

    /**
     * 保存
     */
    public function save(): Response
    {
        $data = $this->request->getMore([
            ['name', ''],
            ['pid', 0],
            ['add_time', time()],
            'app_name'
        ], false);
        if ($this->services->saveRouteCate($data)){
            return app('json')->success(100000);
        }
        return app('json')->fail(100006);
    }

    /**
     * 修改分类
     */
    public function update(int $id): Response
    {
        $data = $this->request->getMore([
            ['name', ''],
            'app_name'
        ], false);
        if ($this->services->updateRouteCate($id, $data)) {
            return app('json')->success(100001);
        }
        return app('json')->fail(100007);
    }

    /**
     * 删除分类
     */
    public function deleteCate(int $id, string $app_name): Response
    {
        $data = $this->services->deleteCate($id, $app_name);
        if ($data) {
            return app('json')->success(100002);
        }
        return app('json')->fail(100008);
    }
}
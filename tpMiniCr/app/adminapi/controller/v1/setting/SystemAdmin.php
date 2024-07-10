<?php
declare(strict_types=1);

namespace app\adminapi\controller\v1\setting;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\admin\SystemAdminServices;
use app\Request;
use think\Response;
use Throwable;

class SystemAdmin extends AuthBaseController
{
    public function __construct(
        protected SystemAdminServices $services,
        protected Request $request
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
//        $where = ['level' => ['level', '<>', 0]];
        $where = [];
        return app('json')->success($this->services->getAdminList($where));
    }

    /**
     * 修改状态
     */
    public function updateAdminStatus(int $id): Response
    {
        $data = $this->request->getMore(['status'], false);
        if ($this->services->updateAdminStatus($id, $data)) {
            return app('json')->success(100000);
        }
        return app('json')->fail(100006);
    }
}

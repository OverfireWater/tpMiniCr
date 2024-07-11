<?php
declare(strict_types=1);

namespace app\adminapi\controller\v1\setting;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\admin\SystemAdminServices;
use app\adminapi\vaildate\system\admin\SystemAdminValidate;
use app\Request;
use think\exception\ValidateException;
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
     * @return Response
     * @throws Throwable
     */
    public function create(): Response
    {
        return app('json')->success($this->services->create());
    }

    /**
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function read(int $id): Response
    {
        return app('json')->success($this->services->read($id));
    }

    /**
     * @return Response
     * @throws Throwable
     */
    public function save(): Response
    {
        $data = $this->request->getMore([
            'account',
            'password',
            'real_name',
            'roles',
            ['status', 1]
        ], false);
        try {
            validate(SystemAdminValidate::class)->check($data);
        }catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if ($this->services->save($data)) {
            return app('json')->success(100000);
        }else {
            return app('json')->fail(100006);
        }
    }

    /**
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function update(int $id): Response
    {
        $data = $this->request->getMore([
            'account',
            ['password', ''],
            ['enter_pwd', ''],
            'real_name',
            'roles',
            ['status', 1]
        ], false);
        try {
            validate(SystemAdminValidate::class)->check($data);
        }catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (!$data['enter_pwd']) return app('json')->fail(400263);
        if ($this->services->update($id, $data)) {
            return app('json')->success(100000);
        }
        return app('json')->fail(100006);
    }

    /**
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function delete(int $id): Response
    {
        if ($this->services->delete($id)) return app('json')->success(100002);

        return app('json')->fail(100008);
    }

    /**
     * 修改状态
     * @throws Throwable
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

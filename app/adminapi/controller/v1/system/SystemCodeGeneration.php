<?php
declare(strict_types=1);
namespace app\adminapi\controller\v1\system;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\SystemCodeGenerationServices;
use app\adminapi\services\system\SystemMenusServices;
use app\Request;
use think\Response;
use Throwable;

class SystemCodeGeneration extends AuthBaseController
{
    public function __construct(
        protected SystemCodeGenerationServices $services,
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
        $where = [];
        return app('json')->success($this->services->getCodeGenerationList($where));
    }

    /**
     * @param SystemMenusServices $systemMenusServices
     * @return Response
     * @throws Throwable
     */
    public function create(SystemMenusServices $systemMenusServices): Response
    {
        $data['menuList'] = $systemMenusServices->getFormCascaderMenus(1);
        $data['formRule'] = $this->services->tableRules();
        return app('json')->success($data);
    }

    public function delete(int $id): Response
    {
        if ($this->services->delete($id)) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }
}

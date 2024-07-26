<?php
declare(strict_types=1);
namespace app\adminapi\controller\v1\system;

use app\adminapi\AuthBaseController;
use app\adminapi\services\system\SystemCodeGenerationServices;
use app\adminapi\services\system\SystemMenusServices;
use app\adminapi\vaildate\system\SystemCodeGenerationValidate;
use app\Request;
use think\exception\ValidateException;
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
        $data['tableList'] = $this->services->getAllTable();
        return app('json')->success($data);
    }

    public function getTableName(string $tableName): Response
    {
        $tableNameList = $this->services->getAllTableColumnName($tableName);
        return app('json')->success($tableNameList);
    }

    public function save(): Response
    {
        $data = $this->request->getMore([
            'make_path', // 生成路径
            'menu_path', // 父级菜单路径
            'model_name', // 模块名
            'menu_name', // 菜单名
            'table_name', // 表名
            'tableData' // 表数据
        ], false);
        try {
            validate(SystemCodeGenerationValidate::class)->check($data);
        }catch (ValidateException $e) {
            return app('json')->fail($e->getMessage());
        }
        $data['menu_path'] = implode(',', $data['menu_path']);
        return app('json')->success($this->services->saveCodeGeneration($data));
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

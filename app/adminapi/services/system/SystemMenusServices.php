<?php
declare(strict_types=1);

namespace app\adminapi\services\system;

use app\adminapi\dao\system\SystemMenusDao;
use app\adminapi\services\system\admin\SystemRoleServices;
use base\BaseServices;
use exceptions\ApiException;
use think\model\Collection;
use Throwable;
use utils\Arr;

class SystemMenusServices extends BaseServices
{


    public function __construct(SystemMenusDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取菜单没有被修改器修改的数据
     * @param $menusList
     * @return array
     */
    public function getMenusData($menusList): array
    {
        $data = [];
        foreach ($menusList as $item) {
            $item = $item->getData();
            if (isset($item['menu_path'])) {
                $item['menu_path'] = '/' . config('app.admin_prefix', 'admin') . $item['menu_path'];
            }
            $data[] = $item;
        }

        return $data;
    }

    /**
     * 获取后台权限菜单和权限
     * @param $roleId
     * @param int $level
     * @return array
     * @throws Throwable
     */
    public function getMenusAndUniqueList($roleId, int $level): array
    {
        $systemRoleServices = app()->make(SystemRoleServices::class);
        $roles = $systemRoleServices->getRoleArray(['status' => 1, 'id' => $roleId], 'rules');
        $rolesStr = Arr::unique($roles);
        $menusList = $this->dao->getMenusRole(['route' => $level ? $rolesStr : '', 'is_show_path' => 1]);
        $unique = $this->dao->getMenusUnique(['unique' => $level ? $rolesStr : '']);
        return [Arr::getMenuList($this->getMenusData($menusList)), $unique];
    }

    /**
     * @param array $where
     * @param array $field
     * @return array
     * @throws Throwable
     */
    public function getMenusList(array $where, array $field = ['*']): array
    {
        $menusList = $this->dao->getMenusList($where, $field);
        $menusList = $this->getMenusData($menusList);
        return $this->get_tree_children($menusList);
    }

    /**
     * tree 子菜单
     * @param array $data 数据
     * @param string $childrenName 子数据名
     * @param string $keyName 数据key名
     * @param string $pidName 数据上级key名
     * @return array
     */
    public function get_tree_children(array $data, string $childrenName = 'children', string $keyName = 'id', string $pidName = 'pid'): array
    {
        $list = array();
        foreach ($data as $value) {
            $list[$value[$keyName]] = $value;
        }
        static $tree = array(); //格式化好的树
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]])) {
                $list[$item[$pidName]][$childrenName][] = &$list[$item[$keyName]];
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }
        return $tree;
    }

    /**
     * 获取一条数据
     * @param int $id
     * @return mixed
     * @throws Throwable
     */
    public function find(int $id): mixed
    {
        $menusInfo = $this->dao->get($id)->toArray();
        if (!$menusInfo) {
            throw new ApiException(100026);
        }
        if ($menusInfo['path']) {
            $menusInfo['path'] = explode('/', $menusInfo['path']);
            $menusInfo['path'] = array_map(function ($item) {
                return (int)$item;
            }, $menusInfo['path']);
        }
        return $menusInfo;
    }

    /**
     * 获取权限表单中 获取父级菜单展示数据
     * @param int|string $auth_type
     * @return array
     * @throws Throwable
     */
    public function getFormCascaderMenus(int|string $auth_type = 3): array
    {
        $where = ['is_del' => 0, 'auth_type' => $auth_type];
        $menuList = $this->dao->getMenusRole($where, ['id as value', 'pid', 'menu_name as label']);
        $menuList = $this->getMenusData($menuList);
        return $this->get_tree_children($menuList, 'children', 'value');
    }

    /**
     * 删除菜单，会删除父菜单下的子菜单
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function deleteMenus(int $id): bool
    {
        $id_array = $this->dao->column(['pid' => $id], 'id');
        if (count($id_array)) {
            foreach ($id_array as $val) {
                $this->deleteMenus($val);
            }
        }
        $id_array[] = $id;
        return $this->dao->destroy($id_array);
    }

    /**
     * @param array $menus
     * @return Collection
     */
    public function saveRouteRule(array $menus): Collection
    {
        $data = [];
        $uniqueAuthAll = $this->getColumn(['is_del' => 0, 'is_show' => 1], 'unique_auth');
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
        $api_url = $this->getColumn(['is_del' => 0, 'is_show' => 1, 'pid' => $pid], 'api_url');
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
        return $this->saveAll($data);
    }
}

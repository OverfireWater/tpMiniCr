<?php
declare(strict_types=1);

namespace app\adminapi\services\system\admin;

use app\adminapi\dao\system\admin\SystemRoleDao;
use app\adminapi\services\system\SystemMenusServices;
use base\BaseServices;
use exceptions\ApiException;
use services\CacheService;
use Throwable;

class SystemRoleServices extends BaseServices
{

    /**
     * 当前管理员权限缓存前缀
     */
    private const ADMIN_RULES_LEVEL = 'Admin_rules_level_';


    public function __construct(SystemRoleDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取角色列表
     * @param array $where
     * @return array
     * @throws Throwable
     */
    public function getRoleList(array $where = []): array
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getRoleList($where, $page, $limit);
        $count = $this->dao->count($where);
        $menusServices = app()->make(SystemMenusServices::class);
        foreach ($list as &$v) {
            $v['rules'] = $menusServices->getColumn([['id', 'in', $v['rules']]], 'menu_name');
        }
        return compact('count', 'list');
    }

    /**
     * 获取权限
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     * @throws Throwable
     */
    public function getRoleArray(array $where = [], string $field = '', string $key = ''): array
    {
        return $this->dao->getRole($where, $field, $key);
    }

    /**
     * 获取角色详情
     * @throws Throwable
     */
    public function getRoleInfo(int $id): array
    {
        $data = $this->dao->get($id)->toArray();
        $data['rules'] = explode(',', $data['rules']);
        $data['rules'] = array_map(function ($item) {
            return (int)$item;
        }, $data['rules']);
        return $data;
    }

    /**
     * 验证角色是否有权限
     */
    public function verifyAuth($request): bool
    {
        // 获取当前的接口于接口类型
        $rule = trim(strtolower($request->rule()->getRule()));
        $method = trim(strtolower($request->method()));

        // 判断接口是一下两种的时候放行
        if (in_array($rule, ['setting/admin/logout', 'menuslist'])) {
            return true;
        }

        // 获取管理员的接口权限列表，存在时放行
        $auth = $this->getRolesByAuth(explode(',', $request->adminInfo['roles']), 2);
        if (isset($auth[$method]) && in_array($rule, $auth[$method])) {
            return true;
        } else {
            throw new ApiException(110000);
        }
    }

    /**
     * @param array $rules
     * @param int $type
     * @return array
     */
    public function getRolesByAuth(array $rules, int $type = 1): array
    {
        if (empty($rules)) return [];
        $cacheName = md5(self::ADMIN_RULES_LEVEL . '_' . $type . '_' . implode('_', $rules));
        return CacheService::remember($cacheName, function () use ($rules, $type) {
            $menusService = app()->make(SystemMenusServices::class);
            $authList = $menusService->getColumn([['id', 'IN', $this->getRoleIds($rules)], ['auth_type', '=', $type]], 'api_url,methods');
            $rolesAuth = [];
            foreach ($authList as $item) {
                $rolesAuth[trim(strtolower($item['methods']))][] = trim(strtolower(str_replace(' ', '', $item['api_url'])));
            }
            return $rolesAuth;
        });
    }

    /**
     * 获取权限id
     * @param array $rules
     * @return array
     */
    public function getRoleIds(array $rules): array
    {
        $rules = $this->dao->getColumn([['id', 'IN', $rules], ['status', '=', '1']], 'rules', 'id');
        return array_unique(explode(',', implode(',', $rules)));
    }
}

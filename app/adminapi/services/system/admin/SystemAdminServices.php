<?php


namespace app\adminapi\services\system\admin;


use app\adminapi\dao\system\admin\SystemAdminDao;
use app\adminapi\services\system\SystemMenusServices;
use base\BaseServices;
use exceptions\ApiException;
use services\CacheService;
use think\db\Query;
use think\facade\Request;
use think\Model;
use Throwable;
use utils\JwtAuth;

/**
 * 管理员service
 */
class SystemAdminServices extends BaseServices
{

    public function __construct(SystemAdminDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 管理员登陆
     * @param string $account
     * @param string $password
     * @return Model|bool
     * @throws Throwable
     */
    public function verifyLogin(string $account, string $password): Model|bool
    {
        $adminInfo = $this->dao->accountByAdmin($account);
        if (!password_verify($password, $adminInfo->pwd)) return false;
        if (!$adminInfo->status) {
            throw new ApiException(400595);
        }

        $adminInfo->last_time = time();
        $adminInfo->last_ip = Request::ip();
        $adminInfo->login_count++;
        $adminInfo->save();

        return $adminInfo;
    }

    /**
     * 后台登陆获取菜单获取token
     * @param string $account
     * @param string $password
     * @param string $type
     * @return array|bool
     * @throws Throwable
     */
    public function login(string $account, string $password, string $type): bool|array
    {
        $adminInfo = $this->verifyLogin($account, $password);
        if (!$adminInfo) return false;
        $tokenInfo = $this->createToken($adminInfo->id, $type, $adminInfo->pwd);
        $services = app()->make(SystemMenusServices::class);
        [$menus, $uniqueAuth] = $services->getMenusAndUniqueList($adminInfo->roles, (int)$adminInfo['level']);
        return [
            'token' => $tokenInfo['token'],
            'expires_time' => $tokenInfo['params']['exp'],
            'user_info' => [
                'id' => $adminInfo->getData('id'),
                'account' => $adminInfo->getData('account'),
                'head_pic' => get_file_link($adminInfo->getData('head_pic')),
                'level' => $adminInfo->getData('level'),
                'real_name' => $adminInfo->getData('real_name'),
            ],
            'logo' => sys_config('site_logo'),
            'logo_square' => sys_config('site_logo_square'),
            'site_name' => sys_config('site_name'),
            'menus' => $menus,
            'unique_auth' => $uniqueAuth
        ];
    }

    /**
     * 获取登陆前的login等信息
     * @return array
     */
    public function getLoginInfo(): array
    {
        $key = uniqid();
        $data = [
            'slide' => json_decode('{"name":"\u5e7b\u706f\u7247","title":"slide","type":"upload","param":""}'),
            'logo_square' => sys_config('site_logo_square'),//透明
            'logo_rectangle' => sys_config('site_logo'),//方形
            'login_logo' => sys_config('login_logo'),//登陆
            'site_name' => sys_config('site_name'),
            'copyright' => sys_config('copyright'),
            'key' => $key,
            'login_captcha' => 0,
            'bg-big' => sys_config('login_bg_big') // 登录页大图
        ];
        if (CacheService::get('login_captcha', 1) > 1) {
            $data['login_captcha'] = 1;
        }
        return $data;
    }


    /**
     * 获取Admin授权信息
     * @param string $token
     * @param int $code
     * @return array
     * @throws Throwable
     */
    public function parseToken(string $token, int $code = 110003): array
    {
        $cacheService = app()->make(CacheService::class);

        if (!$token) {
            throw new ApiException($code);
        }
        $jwtAuth = app()->make(JwtAuth::class);
        //设置解析token
        [$id, $type] = $jwtAuth->parseToken($token);

        //检测token是否过期
        $md5Token = md5($token);
        if (!$cacheService->has($md5Token) || !$cacheService->get($md5Token)) {
            throw new ApiException($code);
        }

        //验证token
        try {
            $jwtAuth->verifyToken();
        } catch (Throwable $e) {
            $cacheService->delete($md5Token);
            throw new ApiException($code);
        }

        //获取管理员信息
        $adminInfo = $this->dao->get($id);
        if (!$adminInfo || !$adminInfo->id) {
            $cacheService->delete($md5Token);
            throw new ApiException($code);
        }

        $adminInfo->type = $type;
        return $adminInfo->hidden(['pwd', 'is_del'])->toArray();
    }

    /**
     * @param array $where
     * @return array
     * @throws Throwable
     */
    public function getAdminList(array $where): array
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getAdminList($where, $page, $limit);
        $count = $this->dao->count($where);
        $roleService = app()->make(SystemRoleServices::class);
        foreach ($list as &$value) {
            $value['roles'] = $roleService->getColumn([['id', 'in', explode(',', $value['roles'])]], 'id, role_name');
        }
        return compact('count', 'list');
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function create(): array
    {
        return app()->make(SystemRoleServices::class)->getRoleArray(['status' => 1], 'id, role_name');
    }

    /**
     * @param int $id
     * @return mixed
     * @throws Throwable
     */
    public function read(int $id): array
    {
        $roleList = $this->dao->get($id)->hidden(['pwd']);
        if (!$roleList->roles) return $roleList->toArray();

        $roles = explode(',', $roleList->roles);
        $roleList->roles = array_map(function ($item) {
            return (int)$item;
        }, $roles);
        return $roleList->toArray();
    }

    /**
     * 修改管理员状态
     * @param int $id
     * @param mixed $status
     * @return Query|Model
     * @throws Throwable
     */
    public function updateAdminStatus(int $id, mixed $status): Model|Query
    {
        $this->isSuperAdmin($id);
        return $this->dao->update($id, $status);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Query|Model
     * @throws Throwable
     */
    public function update(int $id, array $data): Model|Query
    {
        $this->isSuperAdmin($id);
        if ($data['password']) {
            if ($data['password'] !== $data['enter_pwd']) throw new ApiException(400264);
            $data['pwd'] = $this->passwordHash($data['password']);
            CacheService::clear();
        }
        $data['roles'] = implode(',', $data['roles']);
        // 判断账号是否存在
        if ($this->dao->count(['account' => $data['account'], 'is_del' => 0, 'id' => ['id', '<>', $id]])) {
            throw new ApiException(400596);
        }
        return $this->dao->update($id, $data);
    }

    /**
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function delete(int $id): int
    {
        $this->isSuperAdmin($id);
        return $this->dao->delete($id);
    }

    /**
     * @param array $data
     * @return Model|Query
     * @throws Throwable
     */
    public function save(array $data): Model|Query
    {
        if ($this->dao->count(['account' => $data['account'], 'is_del' => 0])) {
            throw new ApiException(400596);
        }
        $data['pwd'] = $this->passwordHash($data['password']);
        $data['head_pic'] = 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif';
        $data['add_time'] = time();
        $data['roles'] = implode(',', $data['roles']);
        return $this->dao->save($data);
    }

    /**
     * 判断是否为超级管理员
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function isSuperAdmin(int $id): void
    {
        $fields = ['id, account, level'];
        $adminInfo = $this->dao->get($id, $fields);
        if(!$adminInfo->level) throw new ApiException('不能修改系统管理员状态');
    }
}

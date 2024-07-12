<?php

namespace app\adminapi\controller;

use app\adminapi\services\system\admin\SystemAdminServices;
use app\adminapi\vaildate\system\admin\SystemAdminValidate;
use app\Request;
use services\CacheService;
use think\exception\ValidateException;
use think\Response;
use Throwable;


class LoginController
{
    public function __construct(
        protected SystemAdminServices $services,
        protected Request $request
    )
    {}

    /**
     * 登陆
     * @return mixed
     * @throws Throwable
     */
    public function login(): Response
    {
        [$account, $password, $key, $captchaVerification, $captchaType] = $this->request->getMore([
            'account',
            'password',
            ['key', ''],
            ['captchaVerification', ''],
            ['captchaType', '']
        ]);

        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail(400762);
        }

        try {
            validate(SystemAdminValidate::class)->scene('get')->check(['account' => $account, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $result = $this->services->login($account, $password, 'admin', $key);
        if (!$result) {
            $num = CacheService::get('login_captcha', 1);
            if ($num > 1) {
                return app('json')->fail(400140, ['login_captcha' => 1]);
            }
            CacheService::set('login_captcha', $num + 1, 60);
            return app('json')->fail(400140, ['login_captcha' => 0]);
        }
        CacheService::delete('login_captcha');
        return app('json')->success($result);
    }

    /**
     * 获取后台登录页轮播图以及LOGO
     * @return mixed
     */
    public function info(): Response
    {
        return app('json')->success($this->services->getLoginInfo());
    }

    /**
     * 退出登陆
     * @return mixed
     */
    public function logout(): Response
    {
        $key = $this->request->header('Authorization');
        CacheService::delete(md5($key));
        return app('json')->success();
    }
}

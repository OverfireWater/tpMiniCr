<?php

namespace app\api\controller\v1;

use app\api\services\LoginServices;
use app\api\validate\user\RegisterValidates;
use app\Request;
use services\CacheService;
use think\exception\ValidateException;
use think\facade\Config;
use Throwable;


class LoginController
{

    public function __construct(
        protected LoginServices $services,
        protected Request $request
    )
    {}

    /**
     * H5账号登陆
     * @return mixed
     */
    public function login(): mixed
    {
        [$account, $password] = $this->request->getMore([
            'account', 'password'
        ]);

        if (!$account || !$password) {
            return app('json')->fail(410000);
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail(400762);
        }
        return app('json')->success(410001, $this->services->login($account, $password));
    }

    /**
     * 退出登录
     * @return mixed
     */
    public function logout(): mixed
    {
        $key = $this->request->header(Config::get('cookie.token_name'));
        CacheService::delete(md5($key));
        return app('json')->success(410002);
    }

    /**
     * H5注册新用户
     * @param Request $request
     * @return mixed
     * @throws Throwable
     */
    public function register(Request $request): mixed
    {
        [$account, $captcha, $password] = $request->getMore([
            ['account', ''], ['captcha', ''], ['password', '']
        ]);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail(400762);
        }
        $verifyCode = CacheService::get('code_' . $account);

        if (!$verifyCode) return app('json')->fail(410009);

        $verifyCode = substr($verifyCode, 0, 6);

        if ($verifyCode != $captcha) return app('json')->fail(410010);

        if (md5($password) == md5('123456')) return app('json')->fail(410012);

        $registerStatus = $this->services->register($account, $password, 'h5');

        if ($registerStatus) return app('json')->success(410013);

        return app('json')->fail(410014);
    }

    /**
     * 密码修改
     * @param Request $request
     * @return mixed
     * @throws Throwable
     */
    public function reset(Request $request): mixed
    {
        [$account, $captcha, $password] = $request->getMore([['account', ''], ['captcha', ''], ['password', '']], true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail(400762);
        }
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode) return app('json')->fail(410009);

        $verifyCode = substr($verifyCode, 0, 6);

        if ($verifyCode != $captcha) return app('json')->fail(410010);

        if ($password == '123456') return app('json')->fail(410012);

        $resetStatus = $this->services->reset($account, $password);

        if ($resetStatus) return app('json')->success(100001);

        return app('json')->fail(100007);
    }
}

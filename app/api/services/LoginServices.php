<?php
declare(strict_types=1);

namespace app\api\services;

use app\api\dao\UserDao;
use base\BaseServices;
use exceptions\ApiException;
use Throwable;

class LoginServices extends BaseServices
{
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    public function login($account, $password): array
    {
        $user = $this->dao->getOne(['account|phone' => $account, 'is_del' => 0]);
        if ($user) {
            if ($user->pwd !== md5((string)$password))
                throw new ApiException(410025);
            if ($user->pwd === md5('123456'))
                throw new ApiException(410026);
        } else {
            throw new ApiException(410025);
        }
        if (!$user['status']) throw new ApiException(410027);
        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else
            throw new ApiException(410019);
    }

    /**
     * H5用户注册
     * @param $account
     * @param $password
     * @param $spread
     * @param string $user_type
     * @return mixed
     * @throws Throwable
     */
    public function register($account, $password, $spread, string $user_type = 'h5'): mixed
    {
        if ($this->dao->getOne(['account|phone' => $account, 'is_del' => 0])) {
            throw new ApiException(410028);
        }
        $phone = $account;
        $data['account'] = $account;
        $data['pwd'] = md5((string)$password);
        $data['phone'] = $phone;
        $data['real_name'] = '';
        $data['birthday'] = 0;
        $data['card_id'] = '';
        $data['mark'] = '';
        $data['address'] = '';
        $data['user_type'] = $user_type;
        $data['add_time'] = time();
        $data['add_ip'] = app('request')->ip();
        $data['last_time'] = time();
        $data['last_ip'] = app('request')->ip();
        $data['nickname'] = substr_replace($account, '****', 3, 4);
        $data['avatar'] = sys_config('h5_avatar');
        $data['city'] = '';
        $data['language'] = '';
        $data['province'] = '';
        $data['country'] = '';
        $data['status'] = 1;
        if (!$re = $this->dao->save($data)) {
            return false;
        } else {
            return $re;
        }
    }

    /**
     * 重置密码
     * @param $account
     * @param $password
     * @return bool
     * @throws Throwable
     */
    public function reset($account, $password): bool
    {
        $user = $this->dao->getOne(['account|phone' => $account, 'is_del' => 0], 'uid');
        if (!$user) {
            throw new ApiException(410032);
        }
        if (!$this->dao->update($user['uid'], ['pwd' => md5((string)$password)], 'uid')) {
            throw new ApiException(410033);
        }
        return true;
    }
}
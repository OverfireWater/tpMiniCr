<?php
declare(strict_types=1);
namespace utils;

use exceptions\ApiException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use services\CacheService;
use think\facade\Env;

class JwtAuth
{
    protected string|null $token;

    /**
     * 获取token
     * @param int|string $id
     * @param string $type
     * @param array $params
     * @return array
     */
    public function getToken(int|string $id, string $type, array $params = []): array
    {
        $host = app()->request->host();
        $time = time();
        $exp_time = strtotime('+ 30day');
        $params += [
            'iss' => $host,
            'aud' => $host,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $exp_time,
        ];
        $params['jti'] = compact('id', 'type');
        $alg = 'HS256';
        $token = JWT::encode($params, Env::get('app.app_key', 'default'), $alg);

        return compact('token', 'params');
    }

    /**
     * 解析token
     * @param string $jwt
     * @return array
     */
    public function parseToken(string $jwt): array
    {
        $this->token = $jwt;
        [$headb64, $bodyb64, $cryptob64] = explode('.', $this->token);
        $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
        return [$payload->jti->id, $payload->jti->type];
    }

    /**
     * 验证token
     */
    public function verifyToken()
    {
        JWT::$leeway = 60;
        $key = new Key(Env::get('app.app_key', 'default'), 'HS256');
        JWT::decode($this->token, $key);
        $this->token = null;
    }

    /**
     * 获取token并放入令牌桶
     * @param $id
     * @param string $type
     * @param array $params
     * @return array
     */
    public function createToken($id, string $type, array $params = []): array
    {
        $tokenInfo = $this->getToken($id, $type, $params);
        $exp = $tokenInfo['params']['exp'] - $tokenInfo['params']['iat'] + 60;
        $res = CacheService::set(md5($tokenInfo['token']), ['uid' => $id, 'type' => $type, 'token' => $tokenInfo['token'], 'exp' => $exp], (int)$exp, $type);
        if (!$res) {
            throw new ApiException(100023);
        }
        return $tokenInfo;
    }
}

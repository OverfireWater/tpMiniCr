<?php
declare(strict_types=1);
namespace utils;

use app\services\system\LangCodeServices;
use think\Response;

/**
 * Json输出类
 */
class Json
{
    private int $code = 200;

    public function make(int $status, string $msg, array $data = null): Response
    {
        $res = compact('status', 'msg');

        if (!is_null($data)) $res['data'] = $data;

        if (is_numeric($res['msg'])) {
            $res['code'] = $res['msg'];
            // 获取错误码信息
            $res['msg'] = app()->make(LangCodeServices::class)->getCodeMsg()[$res['msg']];
        }

        return Response::create($res, 'json', $this->code);
    }


    public function success($msg = 'success', array $data = null): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg = 'success';
        }

        return $this->make(200, $msg, $data);
    }

    public function fail($msg = 'fail', array $data = null): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg = 'fail';
        }

        return $this->make(400, $msg, $data);
    }
}

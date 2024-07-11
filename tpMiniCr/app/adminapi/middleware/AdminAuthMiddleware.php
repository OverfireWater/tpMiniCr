<?php
declare (strict_types = 1);

namespace app\adminapi\middleware;


use app\adminapi\services\system\admin\SystemAdminServices;
use Closure;
use exceptions\ApiException;
use think\facade\Config;
use think\Request;
use think\Response;
use Throwable;

class AdminAuthMiddleware
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header(Config::get('cookie.token_name'));
        if (!$token) {
            throw new ApiException(410032); // 用户不存在
        }
        $adminInfo = app()->make(SystemAdminServices::class);
        $adminInfo = $adminInfo->parseToken($token);
        if (!$adminInfo['status']) throw new ApiException(400595);
        $request->adminInfo = $adminInfo;
        $request->adminId = $adminInfo['id'];
        $request->adminRole = $adminInfo['roles'];
        return $next($request);
    }
}

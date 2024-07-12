<?php
declare (strict_types = 1);

namespace app\adminapi\middleware;

use app\adminapi\services\system\admin\SystemRoleServices;
use Closure;
use exceptions\ApiException;
use think\Request;
use think\Response;

class AdminAuthCheckMiddleware
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure       $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->adminId || !$request->adminInfo) throw new ApiException(100100);

        if ($request->adminInfo['level']) {
            app()->make(SystemRoleServices::class)->verifyAuth($request);
        }
        return $next($request);
    }
}

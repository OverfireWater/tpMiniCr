<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\facade\Config;
use think\Request;
use think\Response;

/**
 * 跨域请求支持
 */
class AllowCrossDomain
{
    protected mixed $cookieDomain;

    protected array $header = [
        'Access-Control-Allow-Origin'       => '*',
        'Access-Control-Allow-Headers'      => '*',
        'Access-Control-Allow-Methods'      => 'GET,POST,PATCH,PUT,DELETE,OPTIONS,DELETE',
        'Access-Control-Max-Age'            =>  '1728000',
        'Access-Control-Allow-Credentials'  => 'true'
    ];

    /**
     * 允许跨域请求
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->cookieDomain = Config::get('cookie.domain', '');
        $origin = $request->header('origin');

        if ($origin && ('' == $this->cookieDomain || strpos($origin, $this->cookieDomain)))
            $this->header['Access-Control-Allow-Origin'] = $origin;
        if ($request->method(true) == 'OPTIONS') {
            $response = Response::create('ok')->code(200)->header($this->header);
        } else {
            $response = $next($request)->header($this->header);
        }
        return $response;
    }
}

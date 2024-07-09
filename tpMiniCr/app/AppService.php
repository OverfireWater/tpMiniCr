<?php
declare (strict_types = 1);

namespace app;

use think\Service;
use utils\Json;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public array $bind = [
        'json' => Json::class
    ];

    public function boot()
    {
        // 服务启动

    }
}

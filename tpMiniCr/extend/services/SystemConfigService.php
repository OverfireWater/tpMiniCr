<?php
declare(strict_types=1);
namespace services;


use app\services\system\SystemConfigServices;
use utils\Arr;

/**
 * 获取系统配置服务类
 */
class SystemConfigService
{
    const CACHE_SYSTEM = 'system_config';

    /**
     * 获取单个配置效率更高
     * @param string $key
     * @param string $default
     * @param bool $isCaChe 是否获取缓存配置
     * @return bool|mixed|string
     */
    public static function get(string $key, string $default = '', bool $isCaChe = false): mixed
    {
        $service = app()->make(SystemConfigServices::class);
        try {
            return $service->getConfigValue($key);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * 获取多个配置
     * @param array $keys 示例 [['appid','1'],'appkey']
     * @param bool $isCaChe 是否获取缓存配置
     * @return array
     */
    public static function more(array $keys, bool $isCaChe = false): array
    {
        $service = app()->make(SystemConfigServices::class);
        try {
            return Arr::getDefaultValue($keys, $service->getConfigAll($keys));
        } catch (\Throwable $e) {
            return Arr::getDefaultValue($keys);
        }
    }

}

<?php
declare(strict_types=1);
namespace services;

use think\cache\TagSet;
use think\facade\Cache;
use Throwable;

/**
 *  缓存类
 */
class CacheService
{
    /**
     * 过期时间
     */
    protected static int $expire;

    /**
     * 写入缓存
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param int $expire 缓存时间，为0读取系统缓存时间
     * @param string $tag 标签
     * @return bool
     */
    public static function set(string $name, mixed $value, int $expire = 0, string $tag = ''): bool
    {
        try {
            return Cache::tag($tag)->set($name, $value, $expire);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name
     * @param mixed $default
     * @param int $expire
     * @param string $tag
     * @return mixed|string|null
     */
    public static function remember(string $name, mixed $default = '', int $expire = 0, string $tag = ''): mixed
    {
        try {
            return Cache::tag($tag)->remember($name, $default, $expire);
        } catch (Throwable $e) {
            try {
                if (is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            } catch (Throwable $e) {
                return null;
            }
        }
    }

    /**
     * 读取缓存
     * @param string $name
     * @param mixed $default
     * @return mixed|string
     */
    public static function get(string $name, mixed $default = ''): mixed
    {
        return Cache::get($name) ?? $default;
    }

    /**
     * 删除缓存
     * @param string $name
     * @return bool
     */
    public static function delete(string $name): bool
    {
        return Cache::delete($name);
    }

    /**
     * 清空缓存池
     * @param string $tag 标签
     * @return bool
     */
    public static function clear(string $tag = ''): bool
    {
        return Cache::tag($tag)->clear();
    }

    /**
     * 清空全部缓存
     * @return bool
     */
    public static function clearAll(): bool
    {
        return Cache::clear();
    }

    /**
     * 检查缓存是否存在
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        try {
            return Cache::has($key);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * 指定缓存类型
     * @param string $type
     * @param string $tag
     * @return TagSet
     */
    public static function store(string $type = 'file', string $tag = ''): TagSet
    {
        return Cache::store($type)->tag($tag);
    }
}

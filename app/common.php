<?php
// 应用公共文件
if (!function_exists('sys_config')) {
    /**
     * 获取系统单个配置
     * @param string $name
     * @param mixed $default
     * @return string|array|object
     */
    function sys_config(string $name, mixed $default = ''): string|array|object
    {
        if (empty($name)) return $default;

        $sysConfig = app()->make(\services\SystemConfigService::class)->get($name);
        if (is_array($sysConfig)) {
            foreach ($sysConfig as &$item) {
                if (str_contains($item, '/uploads/system/') || str_contains($item, '/statics/system_images/')) $item = set_file_url($item);
            }
        } else {
            if (str_contains($sysConfig, '/uploads/system/') || str_contains($sysConfig, '/statics/system_images/')) $sysConfig = set_file_url($sysConfig);
        }
        $config = is_array($sysConfig) ? $sysConfig : trim($sysConfig);
        if ($config === '') {
            return $default;
        } else {
            return $config;
        }
    }
}
if (!function_exists('set_file_url')) {
    /**
     * 设置附加路径
     * @param $image
     * @param string $siteUrl
     * @return mixed
     */
    function set_file_url($image, string $siteUrl = ''): mixed
    {
        if (!strlen(trim($siteUrl))) $siteUrl = sys_config('site_url');
        if (!$image) return $image;
        if (is_array($image)) {
            foreach ($image as &$item) {
                $domainTop1 = substr($item, 0, 4);
                $domainTop2 = substr($item, 0, 2);
                if ($domainTop1 != 'http' && $domainTop2 != '//')
                    $item = $siteUrl . str_replace('\\', '/', $item);
            }
        } else {
            $domainTop1 = substr($image, 0, 4);
            $domainTop2 = substr($image, 0, 2);
            if ($domainTop1 != 'http' && $domainTop2 != '//')
                $image = $siteUrl . str_replace('\\', '/', $image);
        }
        return $image;
    }
}

if (!function_exists('get_file_link')) {
    /**
     * 获取文件带域名的完整路径
     * @param string $link
     * @return string
     */
    function get_file_link(string $link): string
    {
        if (!$link) {
            return '';
        }
        if (str_starts_with($link, "http") || str_starts_with($link, 'https') || str_starts_with($link, "//")) {
            return $link;
        } else {
            return app()->request->domain() . $link;
        }
    }
}

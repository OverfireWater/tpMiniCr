<?php

namespace app\services\system;

use app\dao\system\SystemConfigDao;
use base\BaseServices;
use Throwable;

class SystemConfigServices extends BaseServices
{

    public function __construct(SystemConfigDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取单个系统配置
     * @param string $configName
     * @param null $default
     * @return mixed|null
     * @throws Throwable
     */
    public function getConfigValue(string $configName, $default = null): mixed
    {
        $value = $this->dao->getConfigValue($configName);
        return is_null($value) ? $default : json_decode($value, true);
    }

    /**
     * 获取全部配置
     * @param array $configName
     * @return array
     * @throws Throwable
     */
    public function getConfigAll(array $configName = []): array
    {
        return array_map(function ($item) {
            return json_decode($item, true);
        }, $this->dao->getConfigAll($configName));
    }
}
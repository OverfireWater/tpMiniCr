<?php
declare(strict_types=1);

namespace app\dao\system;

use app\model\system\SystemConfig;
use base\BaseDao;
use Throwable;

/**
 * 系统配置
 */
class SystemConfigDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemConfig::class;
    }

    /**
     * 获取某个系统配置
     * @param string $configNmae
     * @return mixed
     * @throws Throwable
     */
    public function getConfigValue(string $configNmae): mixed
    {
        return $this->search(['menu_name' => $configNmae])->value('value');
    }

    /**
     * 获取所有配置
     * @param array $configName
     * @return array
     * @throws Throwable
     */
    public function getConfigAll(array $configName = []): array
    {
        if ($configName) {
            return $this->search(['menu_name' => $configName])->column('value', 'menu_name');
        } else {
            return $this->getModel()->column('value', 'menu_name');
        }
    }

    /**
     * 获取配置列表分页
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws Throwable
     */
    public function getConfigList(array $where, int $page, int $limit): array
    {
        return $this->search($where)->page($page, $limit)->order('sort desc,id asc')->select()->toArray();
    }

    /**
     * 获取某些分类配置下的配置列表
     * @param int $tabId
     * @param int $status
     * @return array
     * @throws Throwable
     */
    public function getConfigTabAllList(int $tabId, int $status = 1): array
    {
        $where['tab_id'] = $tabId;
        if ($status == 1) $where['status'] = $status;
        return $this->search($where)->order('sort desc,id ASC')->select()->toArray();
    }

    /**
     * 获取上传配置中的上传类型
     * @param string $configName
     * @return array
     * @throws Throwable
     */
    public function getUploadTypeList(string $configName): array
    {
        return $this->search(['menu_name' => $configName])->column('upload_type', 'type');
    }
}

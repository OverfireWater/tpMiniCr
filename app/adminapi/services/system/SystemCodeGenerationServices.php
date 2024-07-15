<?php
declare(strict_types=1);
namespace app\adminapi\services\system;

use app\adminapi\dao\system\SystemCodeGenerationDao;
use base\BaseServices;
use Throwable;

class SystemCodeGenerationServices extends BaseServices
{
    public function __construct(SystemCodeGenerationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $where
     * @return array
     * @throws Throwable
     */
    public function getCodeGenerationList(array $where = []): array
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getCodeGenerationList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('count', 'list');
    }
}

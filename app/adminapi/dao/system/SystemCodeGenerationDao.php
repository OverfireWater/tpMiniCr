<?php
declare(strict_types=1);
namespace app\adminapi\dao\system;

use app\model\system\SystemCrud;
use base\BaseDao;
use Throwable;

class SystemCodeGenerationDao extends BaseDao
{

    protected function setModel(): string
    {
        return SystemCrud::class;
    }

    /**
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws Throwable
     */
    public function getCodeGenerationList(array $where, int $page, int $limit): array
    {
        return $this->search($where)->page($page, $limit)->select()->toArray();
    }
}

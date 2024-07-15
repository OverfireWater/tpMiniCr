<?php
declare(strict_types=1);
namespace app\services\system;

use app\dao\system\LangCodeDao;
use base\BaseServices;
use services\CacheService;

// 信息错误码
class LangCodeServices extends BaseServices
{

    // 前缀
    protected string $prefix = 'cr_';
    public function __construct(LangCodeDao $dao)
    {
        $this->dao = $dao;
    }

    public function getCodeMsg(): array
    {
        $name = $this->prefix . 'langCodeMsg';
        return CacheService::remember($name, function (){
            return $this->dao->getColumn(['type_id' => 1], 'lang_explain', 'code');
        }, 0);
    }
}

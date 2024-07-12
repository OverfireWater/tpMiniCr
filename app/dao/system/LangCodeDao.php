<?php

namespace app\dao\system;


use app\model\system\LangCode;
use base\BaseDao;

class LangCodeDao extends BaseDao
{
    protected function setModel(): string
    {
        return LangCode::class;
    }
}
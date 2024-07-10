<?php
declare(strict_types=1);
namespace app\model\system;

use think\db\Query;
use think\Model;

class LangCode extends Model
{
    /**
     * 模型名称
     */
    protected $name = 'lang_code';

    /**
     * code搜索器
     * @param Query $query
     * @param string $value
     */
    public function searchCodeAttr(Query $query, string $value): void
    {
        if ($value) {
            $query->where('code', $value);
        }
    }
}

<?php
declare(strict_types=1);
namespace app\model\system;

use think\Model;

class LangCode extends Model
{
    /**
     * 模型名称
     */
    protected $name = 'lang_code';

    /**
     * code搜索器
     * @param Model $query
     * @param string $value
     */
    public function searchCodeAttr(Model $query, string $value): void
    {
        if ($value) {
            $query->where('code', $value);
        }
    }
}
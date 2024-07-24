<?php
declare(strict_types=1);
namespace app\adminapi\services\system;

use app\adminapi\dao\system\SystemCodeGenerationDao;
use base\BaseServices;
use think\facade\Db;
use Throwable;

class SystemCodeGenerationServices extends BaseServices
{
    public function __construct(SystemCodeGenerationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取crud记录表
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

    /**
     * 数据库字段类型
     * @return array
     */
    public function tableRules(): array
    {
        return [
            'types' => [
                'varchar',
                'char',
                'text',
                'longtext',
                'tinytext',
                'enum',
                'blob',
                'binary',
                'varbinary',

                'datetime',
                'timestamp',
                'time',
                'date',
                'year',

                'boolean',
                'tinyint',
                'int',
                'decimal',
                'float',

                'json',
            ],
            'form' => [
                [
                    'value' => 'input',
                    'label' => '输入框',
                    'field_type' => 'varchar',
                    'limit' => 255
                ],
                [
                    'value' => 'number',
                    'label' => '数字输入框',
                    'field_type' => 'int',
                    'limit' => 11
                ],
                [
                    'value' => 'textarea',
                    'label' => '多行文本框',
                    'field_type' => 'text',
                    'limit' => null
                ],
                [
                    'value' => 'dateTime',
                    'label' => '单选日期时间',
                    'field_type' => 'varchar',
                    'limit' => 200
                ],
                [
                    'value' => 'dateTimeRange',
                    'label' => '日期时间区间选择',
                    'field_type' => 'varchar',
                    'limit' => 200
                ],
                [
                    'value' => 'checkbox',
                    'label' => '多选框',
                    'field_type' => 'varchar',
                    'limit' => 200
                ],
                [
                    'value' => 'radio',
                    'label' => '单选框',
                    'field_type' => 'int',
                    'limit' => 11
                ],
                [
                    'value' => 'switches',
                    'label' => '开关',
                    'field_type' => 'int',
                    'limit' => 11
                ],
                [
                    'value' => 'select',
                    'label' => '下拉框',
                    'field_type' => 'int',
                    'limit' => 11
                ],
                [
                    'value' => 'frameImageOne',
                    'label' => '单图选择',
                    'field_type' => 'varchar',
                    'limit' => 200
                ],
                [
                    'value' => 'frameImages',
                    'label' => '多图选择',
                    'field_type' => 'varchar',
                    'limit' => 200
                ],
            ],
            'search_type' => [
                [
                    'value' => '=',
                    'label' => '等于搜索',
                ],
                [
                    'value' => '<=',
                    'label' => '小于等于搜索',
                ],
                [
                    'value' => '>=',
                    'label' => '大于等于搜索',
                ],
                [
                    'value' => '<>',
                    'label' => '不等于搜索',
                ],
                [
                    'value' => 'like',
                    'label' => '模糊搜索',
                ],
                [
                    'value' => 'between',
                    'label' => '用来时间区间搜索',
                ],
            ]
        ];
    }

    public function getAllTable()
    {
        $sql = "SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?";

        $tableList = Db::query($sql, [config('database.connections.mysql.database')]);
        $data = [];
        foreach ($tableList as $table) {
            $table['TABLE_NAME'] = str_replace(config('database.connections.mysql.prefix'), '', $table['TABLE_NAME']);
            $data[] = [
                'value' => $table['TABLE_NAME'],
                'label' => $table['TABLE_COMMENT']
            ];
        }
        return $data;
    }
}

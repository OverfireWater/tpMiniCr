<?php
declare(strict_types=1);
namespace app\adminapi\services\system;

use app\adminapi\dao\system\SystemCodeGenerationDao;
use base\BaseServices;
use exceptions\ApiException;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Adapter\AdapterWrapper;
use think\facade\Db;
use think\migration\db\Table;
use think\model\Collection;
use Throwable;

class SystemCodeGenerationServices extends BaseServices
{

    // 不可被创建的数据表
    protected const NO_CREAT_TABLES = [
        'lang_code', 'system_admin', 'system_config', 'system_crud', 'system_menus', 'system_role',
        'system_route', 'system_route_cate', 'user'
    ];

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
        $count = count($list);
        return compact('count', 'list');
    }

    /**
     * 数据库字段类型
     * @return array
     */
    public function tableRules(): array
    {
        $rule = [
            'varchar' => 'string',
            'int' => 'integer',
            'biginteger' => 'bigint',
            'tinyint' => 'boolean',
        ];
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
            ],
            'rule' => $rule
        ];
    }

    /**
     * 改变字段类型，兼容phinx所需的字段
     * @param string $type
     * @return string
     */
    public function changeTableRules(string $type): string
    {
        if (!in_array($type, $this->tableRules()['types'])) {
            throw new ApiException(500044);
        }

        return $this->tableRules()['rule'][$type] ?? $type;
    }

    /**
     * 获取所有表
     * @return array
     */
    public function getAllTable(): array
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

    /**
     * 获取表所有字段
     * @param string $tableName
     * @return array
     */
    public function getAllTableColumnName(string $tableName): array
    {
        $sql = 'SELECT * FROM `information_schema`.`columns` WHERE TABLE_SCHEMA = ? AND table_name = ? ORDER BY ORDINAL_POSITION';
        $tableName = config('database.connections.mysql.prefix') . $tableName;
        $tableNameList = Db::query($sql, [config('database.connections.mysql.database'), $tableName]);
        $data = [];
        foreach ($tableNameList as  $item) {
            $data[] = [
                'value' => $item['COLUMN_NAME'],
                'label' => $item['COLUMN_COMMENT'],
                'leaf' => true
            ];
        }
        return $data;
    }

    /**
     * 保存代码生成
     * @param array $data
     * @return Collection
     * @throws Throwable
     */
    public function saveCodeGeneration(array $data): Collection
    {
        if (in_array(strtolower($data['table_name']), self::NO_CREAT_TABLES)) {
            throw new ApiException(500041);
        }
        $cateName = $data['cate_name'];
        $tableName = $data['table_name'];
        $tableComment = $data['table_name'];
        $tableFields = $data['tableData'];
        $table = $this->makeDatabase($tableName, $tableComment, $tableFields);

        $menu_path = 'crud/' . $data['menu_name'];
        $uniqueAuth = 'admin-' . $data['model_name'];

        // 菜单
        $dataMenu = [
            'pid' => $data['pid'],
            'menu_name' => $data['menu_name'],
            'menu_path' => '/' . $tableName,
            'auth_type' => 1,
            'is_show' => 1,
            'is_show_path' => 1,
            'is_del' => 0,
            'unique_auth' => $uniqueAuth,
            'path' => implode('/', $data['menu_path']),
            'is_header' => $data['pid'] ? 0 : 1,
        ];

        // 事务
        $res = $this->transaction(function () use ($data, $dataMenu, $cateName, $tableName, $uniqueAuth) {
            // 创建菜单
            $menuInfo = app()->make(SystemMenusServices::class)->save($dataMenu);
            // 获取分类id
            $cate_id = app()->make(SystemRouteCateServices::class)->getCateId($cateName);
            // 后端路由创建
            $systemRoute = app()->make(SystemRouteServices::class)->saveResourceRoute($data['menu_name'], $tableName , $cate_id);
            //记录权限加入菜单表
            $menuData = [];
            $menuRoute_path = $data['menu_path'];
            $menuRoute_path[] = $menuInfo->id;
            foreach ($systemRoute as $item) {
                $menuData[] = [
                    'pid' => $menuInfo->id ?? 0,
                    'method' => $item['method'],
                    'api_url' => $item['path'],
                    'unique_auth' => '',
                    'menu_name' => $data['menu_name'],
                    'is_del' => 0,
                    'path' => $menuRoute_path,
                    'auth_type' => 2,
                ];
            }
            $routeRuleInfo = app()->make(SystemMenusServices::class)->saveRouteRule($menuData);

            // TODO: 生成文件

            return $systemRoute;
        });

        return $res;
    }

    /**
     * 生成数据库
     * @param string $tableName
     * @param string $tableComment
     * @param array $tableFields
     * @return Table
     */
    public function makeDatabase(string $tableName, string $tableComment, array $tableFields): Table
    {
        $table = new Table($tableName, ['comment' => $tableComment], $this->getAdapter());
        foreach ($tableFields as $item) {
            if ($item['is_primary_key']) {
                continue;
            }
            $options = [];
            if ($item['length']) {
                $options['limit'] = $item['length'];
            }
            if ($item['default_value']) {
                $options['default'] = $item['default_value'];
            }
            if ($item['comment']) {
                $options['comment'] = $item['comment'];
            }
            if (in_array($item['field_type'], ['text', 'longtext', 'tinytext'])) {
                unset($options['limit']);
            }
            $table->addColumn($item['field'], $this->changeTableRules($item['field_type']), $options);
            if ($item['is_index']) {
                $table->addIndex($item['field']);
            }
        }
        return $table;
    }

    /**
     * 生成文件
     * @return void
     */
    public function makeFile()
    {

    }

    /**
     * 获取phinx配置
     * @return AdapterWrapper|AdapterInterface
     */
    public function getAdapter(): AdapterWrapper|AdapterInterface
    {
        $options = $this->getDbConfig();

        $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);

        if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
            $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
        }

        return $adapter;
    }

    /**
     * 获取数据库配置
     * @return array
     */
    protected function getDbConfig(): array
    {
        $default = app()->config->get('database.default');

        $config = app()->config->get("database.connections.$default");

        if (0 == $config['deploy']) {
            $dbConfig = [
                'adapter' => $config['type'],
                'host' => $config['hostname'],
                'name' => $config['database'],
                'user' => $config['username'],
                'pass' => $config['password'],
                'port' => $config['hostport'],
                'charset' => $config['charset'],
                'table_prefix' => $config['prefix'],
            ];
        } else {
            $dbConfig = [
                'adapter' => explode(',', $config['type'])[0],
                'host' => explode(',', $config['hostname'])[0],
                'name' => explode(',', $config['database'])[0],
                'user' => explode(',', $config['username'])[0],
                'pass' => explode(',', $config['password'])[0],
                'port' => explode(',', $config['hostport'])[0],
                'charset' => explode(',', $config['charset'])[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
            ];
        }

        $table = app()->config->get('database.migration_table', 'migrations');

        $dbConfig['migration_table'] = $dbConfig['table_prefix'] . $table;

        return $dbConfig;
    }
}

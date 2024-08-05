<?php
declare(strict_types=1);
namespace app\adminapi\services\system;

use app\adminapi\dao\system\SystemCodeGenerationDao;
use base\BaseServices;
use crud\stubs\Controller;
use crud\stubs\Dao;
use crud\stubs\Make;
use crud\stubs\Model;
use crud\stubs\Route;
use crud\stubs\Service;
use crud\stubs\Validate;
use crud\stubs\ViewApi;
use crud\stubs\ViewPages;
use crud\stubs\ViewRouter;
use exceptions\ApiException;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Adapter\AdapterWrapper;
use services\FileService;
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
                    'value' => 'switches',
                    'label' => '开关',
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
        $filePath = $data['make_path'];
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
        //增加保存的绝对路径
        foreach ($filePath as $k => $i) {
            if (in_array($k, ['view', 'router', 'api'])) {
                $filePath[$k] = Make::adminTemplatePath() . $i;
            } else {
                $filePath[$k] = app()->getRootPath() . $i;
            }
        }

        // 事务
        $res = $this->transaction(function () use ($data, $dataMenu, $cateName, $tableName, $uniqueAuth, $filePath, $table) {
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

            $make = $this->makeFile($tableName, $tableName, true, $data, $filePath);
            $makePath = [];
            foreach ($make as $key => $item) {
                $makePath[$key] = $item['path'];
            }

            if ($table && isset($table['table']) && $table['table'] instanceof Table) {
                //创建数据库
                $table['table']->create();
            }

            $crudDate = [
                'pid' => $data['pid'],
                'name' => $data['menuName'],
                'model_name' => $data['modelName'],
                'table_name' => $tableName,
                'table_comment' => $tableComment,
                'table_collation' => self::TABLR_COLLATION,
                'field' => json_encode($data),//提交的数据
                'menu_ids' => json_encode($menuIds),//生成的菜单id
                'menu_id' => $menuInfo->id,//生成的菜单id
                'make_path' => json_encode($makePath),
                'route_ids' => json_encode($routeIds),
            ];

            if ($crudInfo) {
                $res = $this->dao->update($crudInfo->id, $crudDate);
            } else {
                $crudDate['add_time'] = time();
                //记录crud生成
                $res = $this->dao->save($crudDate);
            }

            return $res;
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
     * @param string $tableName
     * @param string $routeName
     * @param bool $isMake
     * @param array $options
     * @param array $filePath
     * @param string $basePath
     * @return array
     */
    public function makeFile(string $tableName, string $routeName, bool $isMake = false, array $options = [], array $filePath = [], string $basePath = ''): array
    {
        // 表单字段
        $fromField = [];
        // 数据库字段
        $columnField = [];
        // 搜索字段
        $searchField = [];
        // 关联字段
        $hasOneField = [];
        foreach ($options as $item) {
            $fromField[] = [
                'field' => $item['table_column_name'],
                'type' => $item['table_form_type'],
                'name' => $tableName,
                'required' => $item['is_required'],
                'option' => []
            ];
            if (!$item['is_primary_key']) {
                $columnField[] = [
                    'field' => $item['field'],
                    'name' => $tableName,
                    'type' => $item['field_type'],
                ];
            }
            if ($item['query_type']) {
                $searchField[] = [
                    'field' => $item['field'],
                    'type' => $item['table_form_type'],
                    'name' => $tableName,
                    'search' => $item['query_type'],
                    'options' => []
                ];
            }
            if ($item['hasOne'] && count($item['hasOne'])) {
                $hasOneField[] = [
                    'field' => $item['field'],
                    'hasOne' => $item['hasOne'],
                    'name' => $tableName,
                ];
            }
            if ($item['is_primary_key']) {
                $options['key'] = $item['field'];
                break;
            }
        }
        $options['fromField'] = $fromField;
        $options['columnField'] = $columnField;
        $options['searchField'] = $searchField;
        $options['hasOneField'] = $hasOneField;

        //生成模型
        $model = app()->make(Model::class);
        $model->setFilePathName($filePath['model'] ?? '')->setbasePath($basePath)->handle($tableName, $options);
        //生成dao
        $dao = app()->make(Dao::class);
        $dao->setFilePathName($filePath['dao'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'usePath' => $model->getUsePath(),
            'modelName' => $options['model_name'] ?? '',
            'searchField' => $options['searchField'] ?? [],
        ]);
        //生成service
        $service = app()->make(Service::class);
        $service->setFilePathName($filePath['service'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'field' => $options['fromField'],
            'columnField' => $options['columnField'],
            'key' => $options['key'],
            'usePath' => $dao->getUsePath(),
            'modelName' => $options['model_name'] ?? '',
            'hasOneField' => $options['hasOneField'] ?? [],
        ]);
        //生成验证器
        $validate = app()->make(Validate::class);
        $validate->setFilePathName($filePath['validate'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'field' => $options['fromField'],
            'modelName' => $options['model_name'] ?? '',
        ]);
        //生成控制器
        $controller = app()->make(Controller::class);
        $controller->setFilePathName($filePath['controller'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'usePath' => $service->getUsePath(),
            'modelName' => $options['model_name'] ?? '',
            'searchField' => $options['searchField'] ?? [],
            'columnField' => $options['columnField'] ?? [],
            'validateName' => '\\' . str_replace('/', '\\', $validate->getUsePath()) . 'Validate::class',
            'field' => array_column($options['fromField'], 'field'),
        ]);
        //生成路由
        $route = app()->make(Route::class);
        $route->setFilePathName($filePath['route'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'menus' => $options['model_name'] ?? $options['menu_name'],
            'route' => $routeName
        ]);
        //生成前台路由
        $viewRouter = app()->make(ViewRouter::class);
        $viewRouter->setFilePathName($filePath['router'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'route' => $routeName,
            'menuName' => $options['menu_name'],
            'modelName' => $options['model_name'] ?? $options['menu_name'],
        ]);
        //生成前台接口
        $viewApi = app()->make(ViewApi::class);
        $viewApi->setFilePathName($filePath['api'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'route' => $routeName,
        ]);

        //生成前台页面
        $viewPages = app()->make(ViewPages::class);
        $viewPages->setFilePathName($filePath['pages'] ?? '')->setbasePath($basePath)->handle($tableName, [
            'field' => $options['columnField'],
            'tableFields' => $options['tableField'] ?? [],
            'searchField' => $options['searchField'] ?? [],
            'route' => $routeName,
            'key' => $options['key'],
            'pathApiJs' => '@/' . str_replace('\\', '/', str_replace([Make::adminTemplatePath(), '.js'], '', $viewApi->getPath())),
        ]);

        //创建文件
        if ($isMake) {
            FileService::batchMakeFiles([$model, $validate, $dao, $service, $controller, $route, $viewApi, $viewPages, $viewRouter]);
        }
        return [
            'controller' => [
                'path' => $this->replace($controller->getPath()),
                'content' => $controller->getContent()
            ],
            'model' => [
                'path' => $this->replace($model->getPath()),
                'content' => $model->getContent()
            ],
            'dao' => [
                'path' => $this->replace($dao->getPath()),
                'content' => $dao->getContent()
            ],
            'route' => [
                'path' => $this->replace($route->getPath()),
                'content' => $route->getContent()
            ],
            'service' => [
                'path' => $this->replace($service->getPath()),
                'content' => $service->getContent()
            ],
            'validate' => [
                'path' => $this->replace($validate->getPath()),
                'content' => $validate->getContent()
            ],
            'router' => [
                'path' => $this->replace($viewRouter->getPath()),
                'content' => $viewRouter->getContent()
            ],
            'api' => [
                'path' => $this->replace($viewApi->getPath()),
                'content' => $viewApi->getContent()
            ],
            'pages' => [
                'path' => $this->replace($viewPages->getPath()),
                'content' => $viewPages->getContent()
            ]
        ];
    }

    protected function replace(string $path): array|string
    {
        return str_replace([app()->getRootPath(), Make::adminTemplatePath()], '', $path);
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

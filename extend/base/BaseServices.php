<?php
declare(strict_types=1);

namespace base;

use app\api\services\LoginServices;
use exceptions\ApiException;
use think\db\Query;
use think\facade\Db;
use think\facade\Config;
use think\facade\Route as Url;
use think\Model;
use think\model\Collection;
use utils\JwtAuth;

/**
 * @method array|Model get($id, ?array $field = []) 获取一条数据
 * @method array|Model|null getOne(array $where, ?string $field = '*') 获取一条数据（不走搜素器）
 * @method string|null batchUpdate(array $ids, array $data, ?string $key = null) 批量修改
 * @method Model|Query update($id, array $data, ?string $field = '') 修改数据
 * @method mixed value(array $where, string $field) 获取指定条件下的数据
 * @method int count(array $where = []) 读取数据条数
 * @method int getCount(array $where = []) 获取某些条件总数（不走搜素器）
 * @method array getColumn(array $where, string $field, string $key = '') 获取某个字段数组（不走搜素器）
 * @method int delete(int $id, ?string $key = null) 删除(不走模型删除)
 * @method bool destroy(mixed $data, bool $force = false) 删除记录
 * @method mixed save(array $data) 保存数据
 * @method Collection saveAll(array $data) 批量保存数据
 * @method Collection selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false) 获取列表
 */
abstract class BaseServices
{

    /**
     * 模型注入
     */
    protected object $dao;

    /**
     * 获取分页配置
     * @param bool $isPage
     * @param bool $isRelieve
     * @return array [page, limit, defaultLimit]
     */
    public function getPageValue(bool $isPage = true, bool $isRelieve = true): array
    {
        $page = $limit = 0;
        if ($isPage) {
            $page = app()->request->param(Config::get('app.paginate.pageKey', 'page'));
            $limit = app()->request->param(Config::get('app.paginate.limitKey', 'limit'));
        }
        $limitMax = Config::get('app.paginate.limitMax');
        $defaultLimit = Config::get('app.paginate.list_rows', 20);
        if ($limit > $limitMax && $isRelieve) {
            $limit = $limitMax;
        }
        return [(int)$page, (int)$limit, (int)$defaultLimit];
    }

    /**
     * 数据库事务操作
     * @param callable $closure
     * @param bool $isTran
     * @return mixed
     */
    public function transaction(callable $closure, bool $isTran = true): mixed
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    /**
     * 开始事务操作
     * @return void
     */
    public function startTrans(): void
    {
        Db::startTrans();
    }

    /**
     * 开始事务操作
     * @return void
     */
    public function commit(): void
    {
        Db::commit();
    }

    /**
     * 开始事务操作
     * @return void
     */
    public function rollback(): void
    {
        Db::rollback();
    }


    /**
     * 创建token
     * @param int $id
     * @param $type
     * @param string $pwd
     * @return array
     */
    public function createToken(int $id, $type, string $pwd = ''): array
    {
        $jwtAuth = app()->make(JwtAuth::class);
        if ($type == 'api' && !app()->make(LoginServices::class)->value(['uid' => $id], 'status')) {
            throw new ApiException(410027);
        }
        return $jwtAuth->createToken($id, $type, ['pwd' => md5($pwd)]);
    }

    /**
     * 获取路由地址
     * @param string $path
     * @param array $params
     * @param bool $suffix
     * @param bool $isDomain
     * @return string
     */
    public function url(string $path, array $params = [], bool $suffix = false, bool $isDomain = false): string
    {
        return Url::buildUrl($path, $params)->suffix($suffix)->domain($isDomain)->build();
    }

    /**
     * 密码hash加密
     * @param string $password
     * @return false|string|null
     */
    public function passwordHash(string $password): bool|string|null
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }
}

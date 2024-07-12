<?php
declare(strict_types=1);

namespace base;

use think\Collection;
use Throwable;
use think\db\Query;
use think\helper\Str;
use think\Model as BaseModel;

abstract class BaseDao
{
    /**
     * 当前表名别名
     * @var string
     */
    protected string $alias;

    /**
     * 获取当前模型
     * @return string
     */
    abstract protected function setModel(): string;

    /**
     * 读取数据条数
     * @param array $where
     * @param bool $search
     * @return int
     * @throws Throwable
     */
    public function count(array $where = [], bool $search = true): int
    {
        return $this->search($where, $search)->count();
    }

    /**
     * 获取某些条件数据
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param array $with
     * @param bool $search
     * @return Collection
     * @throws Throwable
     */
    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false): Collection
    {
        return $this->selectModel($where, $field, $page, $limit, $order, $with, $search)->select();
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param array $with
     * @param bool $search
     * @return BaseModel|Query
     * @throws Throwable
     */
    public function selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false): BaseModel|Query
    {
        if ($search) {
            $model = $this->search($where);
        } else {
            $model = $this->getModel()->where($where);
        }
        return $model->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when($order !== '', function ($query) use ($order) {
            $query->order($order);
        })->when($with, function ($query) use ($with) {
            $query->with($with);
        });
    }

    /**
     * 获取某些条件总数
     * @param array $where
     * @return int
     * @throws Throwable
     */
    public function getCount(array $where): int
    {
        return $this->getModel()->where($where)->count();
    }

    /**
     * 获取某些条件去重总数
     * @param array $where
     * @param $field
     * @param bool $search
     * @return int|mixed
     * @throws Throwable
     */
    public function getDistinctCount(array $where, $field, bool $search = true): mixed
    {
        if ($search) {
            return $this->search($where)->field('COUNT(distinct(' . $field . ')) as count')->select()->toArray()[0]['count'] ?? 0;
        } else {
            return $this->getModel()->where($where)->field('COUNT(distinct(' . $field . ')) as count')->select()->toArray()[0]['count'] ?? 0;
        }
    }

    /**
     * 获取模型
     * @return mixed
     */
    protected function getModel(): mixed
    {
        return app()->make($this->setModel());
    }

    /**
     * 获取主键
     * @return array|string
     */
    protected function getPk(): array|string
    {
        return $this->getModel()->getPk();
    }

    /**
     * 获取表名
     * @return array|string
     */
    public function getTableName(): array|string
    {
        return $this->getModel()->getName();
    }

    /**
     * 获取一条数据
     * @param $id
     * @param array|null $field
     * @param array|null $with
     * @return array|mixed|null
     * @throws Throwable
     */
    public function get($id, ?array $field = [], ?array $with = []): mixed
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$this->getPk() => $id];
        }
        return $this->getModel()->where($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->field($field ?? ['*'])->find();
    }

    /**
     * 根据条件获取一条数据
     * @param array $where
     * @param string|null $field
     * @param array $with
     * @return array|mixed
     * @throws Throwable
     */
    public function getOne(array $where, ?string $field = '*', array $with = []): mixed
    {
        $field = explode(',', $field);
        return $this->get($where, $field, $with);
    }

    /**
     * 获取单个字段值
     * @param $where
     * @param string|null $field
     * @return mixed
     * @throws Throwable
     */
    public function value($where, ?string $field = ''): mixed
    {
        $pk = $this->getPk();
        return $this->search($this->setWhere($where))->value($field ?: $pk);
    }

    /**
     * 获取某个字段数组
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getColumn(array $where, string $field, string $key = ''): array
    {
        return $this->getModel()->where($where)->column($field, $key);
    }


    /**
     * 删除(不走模型删除)
     * @param array|int|string $id
     * @param string|null $key
     * @return int
     */
    public function delete(array|int|string $id, ?string $key = null): int
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }
        return $this->getModel()->where($where)->delete();
    }

    /**
     * 删除记录
     * @param mixed $data
     * @param bool $force
     * @return bool
     */
    public function destroy(mixed $data, bool $force = false): bool
    {
        return $this->getModel()->destroy($data, $force);
    }

    /**
     * 更新数据
     * @param array|int|string $id
     * @param array $data
     * @param string|null $key
     * @return BaseModel|Query
     */
    public function update(array|int|string $id, array $data, ?string $key = null): BaseModel|Query
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }
        return $this->getModel()::update($data, $where);
    }

    /**
     * @param $where
     * @param string|null $key
     * @return array|mixed
     */
    protected function setWhere($where, ?string $key = null): mixed
    {
        if (!is_array($where)) {
            $where = [is_null($key) ? $this->getPk() : $key => $where];
        }
        return $where;
    }

    /**
     * 批量更新数据
     * @param array $ids
     * @param array $data
     * @param string|null $key
     * @return BaseModel|Query
     */
    public function batchUpdate(array $ids, array $data, ?string $key = null): BaseModel|Query
    {
        return $this->getModel()->whereIn(is_null($key) ? $this->getPk() : $key, $ids)->update($data);
    }

    /**
     * 插入数据
     * @param array $data
     * @return BaseModel|Query
     */
    public function save(array $data): BaseModel|Query
    {
        return $this->getModel()::create($data);
    }

    /**
     * 插入数据
     * @param array $data
     * @return Collection
     * @throws Throwable
     */
    public function saveAll(array $data): Collection
    {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 获取某个字段内的值
     * @param $value
     * @param string $filed
     * @param string|null $valueKey
     * @param array|string[] $where
     * @return mixed
     */
    public function getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = []): mixed
    {
        $model = $this->getModel()->where($filed, $value);
        if ($where) {
            $model->where(...$where);
        }
        return $model->value($valueKey ?: $filed);
    }

    /**
     * 获取搜索器和搜索条件key,以及不在搜索器的条件数组
     * @param array $where
     * @return array[]
     * @throws Throwable
     */
    private function getSearchData(array $where): array
    {
        $with = [];
        $otherWhere = [];
        $responses = new \ReflectionClass($this->setModel());
        foreach ($where as $key => $value) {
            $method = 'search' . Str::studly($key) . 'Attr';
            if ($responses->hasMethod($method)) {
                $with[] = $key;
            } else {
                if (!is_array($value)) {
                    $otherWhere[] = [$key, '=', $value];
                } else if (count($value) === 3) {
                    $otherWhere[] = $value;
                }
            }
        }
        return [$with, $otherWhere];
    }

    /**
     * 根据搜索器获取搜索内容
     * @param $where
     * @param $search
     * @return BaseModel|Query
     * @throws Throwable
     */
    protected function withSearchSelect($where, $search): BaseModel|Query
    {
        [$with, $otherWhere] = $this->getSearchData($where);
        return $this->getModel()->withSearch($with, $where)->when($search, function ($query) use ($otherWhere) {
            $query->where($this->filterWhere($otherWhere));
        });
    }

    /**
     * 过滤数据表中不存在的where条件字段
     * @param array $where
     * @return array
     */
    protected function filterWhere(array $where = []): array
    {
        $fields = $this->getModel()->getTableFields();
        foreach ($where as $key => $item) {
            if (!in_array($item[0], $fields)) {
                unset($where[$key]);
            }
        }
        return $where;
    }

    /**
     * 搜索
     * @param array $where
     * @param bool $search
     * @return BaseModel|Query
     * @throws Throwable
     */
    public function search(array $where = [], bool $search = true): BaseModel|Query
    {
        if ($where) {
            return $this->withSearchSelect($where, $search);
        } else {
            return $this->getModel();
        }
    }
}

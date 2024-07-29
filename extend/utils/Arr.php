<?php
declare(strict_types=1);
namespace utils;

/**
 * 操作数组帮助类
 */
class Arr
{
    /**
     * 对数组增加默认值
     * @param array $keys
     * @param array $configList
     * @return array
     */
    public static function getDefaultValue(array $keys, array $configList = []): array
    {
        $value = [];
        foreach ($keys as $val) {
            if (is_array($val)) {
                $k = $val[0] ?? '';
                $v = $val[1] ?? '';
            } else {
                $k = $val;
                $v = '';
            }
            $value[$k] = $configList[$k] ?? $v;
        }
        return $value;
    }

    /**
     * 获取菜单列表
     * @param array $data
     * @return array
     */
    public static function getMenuList(array $data): array
    {
        return Arr::ChangeMenuList(Arr::getTree($data));
    }

    /**
     * 转化el-ui需要的key值
     * @param $data
     * @return array
     */
    public static function ChangeMenuList($data): array
    {
        $newData = [];
        foreach ($data as $k => $v) {
            $temp = [];
            $temp['id'] = $v['id'];
            $temp['pid'] = $v['pid'];
            $temp['path'] = $v['menu_path'];
            $temp['title'] = $v['menu_name'];
            $temp['icon'] = $v['icon'];
            $temp['header'] = $v['header'];
            $temp['is_header'] = $v['is_header'];
            $temp['is_show'] = $v['is_show_path'];
            if ($v['is_show_path']) {
                $temp['auth'] = ['hidden'];
            }
            if (!empty($v['children'])) {
                $temp['children'] = self::ChangeMenuList($v['children']);
            }
            $newData[] = $temp;
        }
        return $newData;
    }

    /**
     * 获取树型菜单
     * @param mixed $data
     * @param int $pid
     * @param int $level
     * @return array
     */
    public static function getTree(mixed $data, int $pid = 0, int $level = 1): array
    {
        $childs = self::getChild($data, $pid, $level);
        $dataSort = array_column($childs, 'sort');
        array_multisort($dataSort, SORT_DESC, $childs);
        foreach ($childs as $key => $navItem) {
            $resChild = self::getTree($data, $navItem['id']);
            if (null != $resChild) {
                $childs[$key]['children'] = $resChild;
            }
        }
        return $childs;
    }

    /**
     * 获取子菜单
     * @param $arr
     * @param $id
     * @param $lev
     * @return array
     */
    private static function getChild(&$arr, $id, $lev): array
    {
        $child = [];
        foreach ($arr as  $value) {
            if ($value['pid'] == $id) {
                $value['level'] = $lev;
                $child[] = $value;
            }
        }
        return $child;
    }

    /**
     * 数组转字符串去重复
     * @param array $data
     * @return string[]
     */
    public static function unique(array $data): array
    {
        return array_unique(explode(',', implode(',', $data)));
    }
}

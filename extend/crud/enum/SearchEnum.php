<?php
declare(strict_types=1);
namespace crud\enum;

/**
 * 搜索方式枚举
 * Class SearchEnum
 */
enum SearchEnum: string
{
    //等于
    case SEARCH_TYPE_EQ = '=';
    //小于等于
    case SEARCH_TYPE_LTEQ = '<=';
    //大于等于
    case SEARCH_TYPE_GTEQ = '>=';
    //不等于
    case SEARCH_TYPE_NEQ = '<>';
    //模糊搜索
    case SEARCH_TYPE_LIKE = 'LIKE';
    //区间-用来时间区间搜索
    case SEARCH_TYPE_BETWEEN = 'BETWEEN';
}

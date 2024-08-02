<?php
declare(strict_types=1);
namespace crud\enum;

/**
 * 逻辑层方法枚举
 */
enum ServiceActionEnum: string
{
    //搜索
    case INDEX = 'index';
    //获取表单
    case FORM = 'form';
    //保存
    case SAVE = 'save';
    //更新
    case UPDATE = 'update';
}

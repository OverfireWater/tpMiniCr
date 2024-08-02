<?php
declare(strict_types=1);
namespace crud\enum;

/**
 *  访问方法枚举
 */
enum ActionEnum: string
{
    //列表
    case INDEX = 'index';
    //获取创建数据
    case CREATE = 'create';
    //保存
    case SAVE = 'save';
    //获取编辑数据
    case EDIT = 'edit';
    //修改
    case UPDATE = 'update';
    //状态
    case STATUS = 'status';
    //删除
    case DELETE = 'delete';
    //查看
    case READ = 'read';
}

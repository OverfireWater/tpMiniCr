<?php
declare(strict_types=1);
namespace crud\enum;

/**
 * 表单类型枚举
 */
enum FormTypeEnum: string
{
    //输入框
    case INPUT = 'input';
    //数字输入框
    case NUMBER = 'number';
    //多行文本框
    case TEXTAREA = 'textarea';
    //单选日期时间
    case DATE_TIME = 'dateTime';
    //日期时间区间选择
    case DATE_TIME_RANGE = 'dateTimeRange';
    //开关
    case SWITCH = 'switches';
    //单图选择
    case FRAME_IMAGE_ONE = 'frameImageOne';
    //多图选择
    case  FRAME_IMAGES = 'frameImages';
}

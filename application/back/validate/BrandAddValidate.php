<?php


namespace app\back\validate;


use think\Validate;

/**
 * Class brandAddValidate
 * 验证品牌添加的验证器类
 * @package app\back\validate
 */
class BrandAddValidate extends Validate
{
    // 定义规则
    protected $rule = [
        'title' => ['require', 'unique:brand'],
        'site' => ['url'],
        'sort' => ['require', 'number']
    ];

    // 定义字段翻译, 在不需要自定义错误信息时, 可以仅仅翻译字段, 其他的信息采用内置的默认信息
    protected $field = [
        'title' => '品牌',
        'site' => '官网',
        'sort' => '排序',
    ];

}
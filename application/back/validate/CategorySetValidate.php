<?php


namespace app\back\validate;


use think\Validate;
use app\back\model\Category as CategoryModel;

/**
 * Class CategorySetValidate
 * 设置验证器类
 * @package app\back\validate
 */
class CategorySetValidate extends Validate
{
    // 定义规则
    protected $rule = [
        'parent_id' => 'number'
    ];
    // 场景规则
    protected $scene = [
        'edit' => ['parent_id' => 'number|checkParent'],
    ];

    // 定义字段翻译, 在不需要自定义错误信息时, 可以仅仅翻译字段, 其他的信息采用内置的默认信息
    protected $field = [
        'parent_id' => '上级分类',
    ];

    /**
     * 验证parent_id字段的自定义规则代码
     * @param $value 当前值
     * @param $data 全部值
     * @return bool
     */
    protected function checkParent($value, $rule='', $data=[])
    {
        # 找当前分类的后代分类
        $model = new CategoryModel();
        $ids = $model->getChilds($data['id']);

        # 判断所选的parent_id 是否在 ids数组中
        return !in_array($value, $ids);
    }

}
<?php

namespace app\back\model;

use think\Model;

class Product extends Model
{
    /**
     * 自动填充
     */
    protected $auto = [
        'upc', 'date_available'
    ];

    /**
     * 属性设置器(修改器)
     */
    public function setUpcAttr($value)
    {
        //  如果value为空字符串, 则自动生成
        return '' !== $value ? $value : date('YmdHis') . mt_rand(100, 999) . mt_rand(100, 999);
    }
    public function setDateAvailableAttr($value)
    {
        return '' !== $value ? $value : date('Y-m-d H:i:00');
    }
}
<?php

namespace app\back\model;


use think\Model;

class Brand extends Model
{
    // 添加时间和更新时间字段配置
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
}
<?php

/**
 * @param string $url
 * @param string $vars
 * @param $field string, 需要生成排序URL的字段
 * @param $order array, 当前排序参数[type=>asc, field=>title]
 * @param bool $suffix
 * @param bool $domain
 * @return string
 */
function urlOrder($url = '', $vars = [], $field='', $order=[], $suffix = true, $domain = false)
{
    // 分析排序参数, 加入到URL生成的变量中
    $vars['order_field'] = $field;
        // 要按照该字段排序
        // 根据当前的排序方式, 确定是升序还是降序
    if(isset($order['type']) && isset($order['field']) && $order['field'] == $field && $order['type'] == 'asc')
        // 已经按照当前字段, 升序排序了.
        $vars['order_type'] = 'desc';
    else
        $vars['order_type'] = 'asc';

    return url($url, $vars, $suffix, $domain);
}

function classOrder($field, $order=[])
{
    if (isset($order['type']) && isset($order['field']) && $field==$order['field']) {
        // 使用该字段排序
        return $order['type'];
    } else {
        // 没有使用字段排序
        return '';
    }
}
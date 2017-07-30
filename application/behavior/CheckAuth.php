<?php
namespace app\behavior;


use think\Request;
use think\Session;
use think\response\Redirect;
use think\exception\HttpResponseException;
class CheckAuth
{
    /**
     * 固定的方法
     */
    public function run()
    {
        $request = Request::instance();

        // 如果不是后台back, 则自动跳过
        if ('back' != $request->module()) {
            return ;
        }

        # 获取当前的请求 标志
        $action = $request->module() . '/' . $request->controller() . '/' . $request->action();

        # 判断当前请求是否需要校验(后台登录操作不需要登录才能执行)
        // 特例列表
        $except = [
            'back/Admin/login',
        ];
        // 检验是否出现在特例列表中
        if (in_array($action, $except)) return ;

        # 认证(登录)校验
        if (! Session::has('admin')) {
            // 没有登录, 重定向到登录
            redirectU('back/admin/login');
        }

        # 校验权限
        $auth = new \auth\Auth();
        if (! $auth->check([$action], Session::get('admin.id'))) {
            // 没有权限
            redirectU('back/admin/login', [], 302, ['message'=>'没权限, 请使用其他用户登录']);
        }
    }
}
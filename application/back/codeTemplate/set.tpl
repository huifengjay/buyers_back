{extend name="common/layout" /}

{block name="content"}
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-set" data-toggle="tooltip" title="保存" class="btn btn-primary">
                    <i class="fa fa-save"></i>
                </button>
                <a href="{:url('index')}" data-toggle="tooltip" title="取消" class="btn btn-default">
                    <i class="fa fa-reply"></i>
                </a>
            </div>
            <h1>%title%</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="{:url('Manage/index')}">首页</a>
                </li>
                <li>
                    <a href="{:url('index')}">%title%</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-pencil"></i>
                    设置%title%
                </h3>
            </div>
            <div class="panel-body">
                <form action="{:url('set', ['id'=>isset($data['id'])?$data['id']:null])}" method="post" enctype="multipart/form-data" id="form-set" class="form-horizontal">
                    {if condition="isset($data['id'])"}
                        <input type="hidden" id="input-id" name="id" value="{$data['id']}">
                    {/if}
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab-general">

                            %field_list%

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}

{block name="appendJs"}
    <script type="text/javascript" src="__STATIC__/validate/jquery.validate.min.js"></script>
    <script type="text/javascript" src="__STATIC__/validate/additional-methods.min.js"></script>
    <!--初始化模板数据到js中-->
    <script>
        // 初始化JS变量的方式, 存储URL
    </script>

    <!--表单验证-->
    <script>
        $(function() {
            $('#form-set').validate({
                // 规则
                rules:{
                },
                // 消息
                messages:{
                },
                // 错误类
                errorClass: 'text-danger'
            });
        });
    </script>
{/block}
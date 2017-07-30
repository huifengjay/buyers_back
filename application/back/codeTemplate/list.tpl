{extend name="common/layout" /}

{block name="content"}
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <a href="{:url('set')}" data-toggle="tooltip" title="新增" class="btn btn-primary">
                    <i class="fa fa-plus"></i>
                </a>
                <button type="button" data-toggle="tooltip" title="删除" class="btn btn-danger" onclick="confirm('确认？') ? $('#form-index').submit() : false;">
                    <i class="fa fa-trash-o"></i>
                </button>
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
                    <i class="fa fa-list"></i>
                    %title%列表
                </h3>
            </div>
            <div class="panel-body">

                <form action="{:url('multi')}" method="post" enctype="multipart/form-data" id="form-index">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <td style="width: 1px;" class="text-center">
                                    <input type="checkbox" id="checkbox-all"/>
                                </td>

%list_head%

                                <td class="text-right">操作</td>
                            </tr>
                            </thead>
                            <tbody>
                            {volist name="rows" id="row"}
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="selected[]" value="{$row['id']}" />
                                    </td>

%list_body%

                                    <td class="text-right">
                                        <a href="{:url('set', ['id'=>$row['id']])}" data-toggle="tooltip" title="编辑" class="btn btn-primary">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            {/volist}
                            </tbody>
                        </table>
                    </div>
                </form>
                <div class="row">
                    {$rows->render()}
                </div>
            </div>
        </div>
    </div>

{/block}

{block name="appendJs"}
    <!-- 全选  -->
    <script>
        $(function() {
            $('#checkbox-all').click(function() {
                // 找到
                // 控制checked属性, 与全选checkbox一致即可
                $(':checkbox[name="selected[]"]').prop('checked', $(this).prop('checked'));
            }) ;
        });
    </script>
{/block}
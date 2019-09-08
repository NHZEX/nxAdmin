@extends('layouts.master')
@section('title', '人员管理')
@section('content')
<div style="margin: 5px;">
    <div class="layui-tab layui-tab-brief" lay-filter="tab-brief">
        <ul class="layui-tab-title">
            @foreach($manager_types as $key=>$val)
                <li lay-id="{{ $loop->index }}" data-type="{{ $key }}" class="@if($loop->first) layui-this @endif">{{ $val }}</li>
            @endforeach
        </ul>
        <div class="layui-tab-content">
            <div class="layui-btn-group">
                <button class="layui-btn" id="btn-refresh">
                    <i class="layui-icon layui-icon-refresh"></i>刷新
                </button>
                <button class="layui-btn layui-btn-normal" id="btn-add">
                    <i class="layui-icon layui-icon-add-1"></i>添加
                </button>
            </div>
            <table id="table-main" lay-filter="table-node"></table>
        </div>
    </div>
</div>
@endsection
@section('javascript')
<script type="text/html" id="table-toolbar-password">
    <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="password">更改密码</a>
</script>
<script type="text/html" id="table-toolbar-tool">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delete">删除</a>
</script>
<script type="text/html" id="window-change-password">
    <form class="layui-form layui-form-pane" style="margin:10px" lay-filter="form-change-password" id="form-change-password">
        <div class="layui-form-item">
            <label class="layui-form-label" for="username">账号</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input layui-disabled" id="username" title="账号" readonly>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">密码</label>
            <div class="layui-input-block">
                <input type="password" class="layui-input" lay-verify="required" name="password" title="密码" placeholder="不更改" autocomplete="new-password">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="submit-form-change-password">确认更改</button>
            </div>
        </div>
    </form>
</script>
<script>
    const FORM_NAME_CHANGE_PASSWORD = 'form-change-password';

    let table_where = {
        type: null
    };

    require([
        'jquery', 'lodash', 'axios', 'layui', 'helper', 'table-tpl'
    ], ($, _, axios, layui, helper, Tpl) => {

        layui.use(['util', 'form', 'layer', 'table', 'element'], () => {
            let layer = layui.layer;
            let table = layui.table;
            let form = layui.form;
            let element = layui.element;
            let tableIns;

            element.on('tab(tab-brief)', function(data){
                table_where.type = $(data.elem).find('ul > li').eq(data.index).data('type');
                if(tableIns) {
                    if(!_.isEqual(table_where, tableIns.config.where)) {
                        tableIns.setParams($.extend(true, {}, table_where));
                        tableIns.refresh();
                    }
                }
            });
            element.tabChange('tab-brief', 0);

            tableIns = table.render({
                elem: '#table-main'
                , url: '{{ $url_table }}'
                , cellMinWidth: 100
                , loading: true
                , method: 'post'
                , page: true
                , where: $.extend(true, {}, table_where)
                , cols: [[
                    {field: 'id', width: 100, title: 'id'}
                    , {title: '类型', field: 'genre_desc', width: 150}
                    , {title: '角色', field: 'role_name', width: 130}
                    , {title: '账号', field: 'username', width: 170}
                    , {title: '昵称', field: 'nickname', width: 100}
                    , {title: '密码', width: 100, toolbar: '#table-toolbar-password'}
                    , {title: '邮箱', field: 'email', width: 180}
                    , {title: '状态', field: 'status_desc', width: 100}
                    , {title: '添加时间', field: 'create_time', width: 150, templet: Tpl.toDateString('create_time')}
                    , {title: '更新时间', field: 'update_time', width: 150, templet: Tpl.toDateString('update_time')}
                    , {fixed: 'right', width:150, align:'center', toolbar: '#table-toolbar-tool'}
                ]]
            });
            table.on('tool(table-node)', function(obj){
                let pk_id = obj.data.id;
                switch (obj.event) {
                    case 'edit':
                        loadWindowEdit(pk_id);
                        break;
                    case 'password':
                        changePassword(pk_id, obj.data.username);
                        break;
                    case 'delete':
                        deleteData(pk_id);
                        break;
                }
                return false;
            });
            // 更改数据
            function loadWindowEdit(pk_id, type) {
                layer.load(2);

                let params = {};
                pk_id && (params.base_pkid = pk_id);
                type && (params.type = type);

                helper.formModal()
                    .load('{{ $url_page_edit }}', params, '编辑窗口', '500px')
                    .end(() => {
                        tableIns.refresh();
                    });

            }
            // 更改密码
            function changePassword(pk_id, username)
            {
                layer.open({
                    type: 1
                    ,title: '请输入密码'
                    ,content: $('#window-change-password').text()
                    ,area: '400px'
                    ,success: function(layero, index){
                        layero.find('#username').val(username);
                        form.render(null, FORM_NAME_CHANGE_PASSWORD);
                        form.on('submit(submit-form-change-password)', function(fo) {
                            let data = fo.field;
                            data.action = 'password';
                            layer.load(2);
                            axios.put(
                                '{{ $url_save }}', data, {
                                    layer_elem: fo.elem
                                    , csrf: {url: '{{$url_change_password}}', pkid: pk_id}
                                }
                            ).then(function (response) {
                                if(0 === response.data.code) {
                                    layer.tips('操作完成', $(fo.elem), {
                                        tips: [2, '#01AAED'],
                                        end: function() {
                                            layer.close(index);
                                        }
                                    })
                                }
                            }).catch(function (error) {
                            }).then(function() {
                                layer.closeAll('loading');
                            });
                            return false;
                        });

                    }
                    ,end: function () {
                        tableIns.refresh();
                    }
                });
            }
            // 删除数据
            function deleteData(data_id)
            {
                layer.confirm('确定删除数据', {icon: 3, title:'提示'}, function(index){
                    axios.delete(
                        '{{ $url_delete }}', {params: {id: data_id}}
                    ).then(function (response) {
                        if(0 === response.data.code) {
                            layer.msg('删除成功');
                            layer.close(index);
                        }
                    }).catch(function (error) {
                    }).then(function () {
                        tableIns.refresh();
                    })
                });

            }
            // 添加数据
            $('#btn-add').on('click', function () {
                loadWindowEdit(null, table_where.type);
                return false;
            });
            // 刷新数据
            $('#btn-refresh').on('click', function () {
                tableIns.refresh();
                return false;
            });
        })
    });
</script>
@endsection
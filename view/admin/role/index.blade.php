@extends('layouts.master')
@section('title', '角色管理')
@section('content')
    <div style="margin: 5px;">
        <div class="layui-tab layui-tab-brief" lay-filter="tab-brief">
            <ul class="layui-tab-title">
                @foreach($manager_types as $key=>$val)
                    <li lay-id="{{ $loop->index }}" data-type="{{ $key }}"
                        class="@if($loop->first) layui-this @endif">{{ $val }}</li>
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
    <script type="text/html" id="table-toolbar-tool">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs" lay-event="permission">权限分配</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delete">删除</a>
    </script>
    <script>
        let table_where = {
            type: null
        };

        require([
            'jquery', 'lodash', 'axios', 'layui', 'helper', 'table-tpl'
        ], ($, _, axios, layui, helper, Tpl) => {

            layui.use(['util', 'layer', 'table', 'element'], () => {
                let layer = layui.layer;
                let tableNode = layui.table;
                let element = layui.element;
                let tableIns;

                element.on('tab(tab-brief)', function (data) {
                    table_where.type = $(data.elem).find('ul > li').eq(data.index).data('type');
                    if (tableIns) {
                        if (!_.isEqual(table_where, tableIns.config.where)) {
                            tableIns.setParams($.extend(true, {}, table_where));
                            tableIns.refresh();
                        }
                    }
                });
                element.tabChange('tab-brief', 0);

                tableIns = tableNode.render({
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
                        , {title: '名称', field: 'name', width: 170}
                        , {title: '状态', field: 'status_desc', width: 100}
                        , {title: '添加时间', field: 'create_time', width: 150, templet: Tpl.toDateString('create_time')}
                        , {title: '更新时间', field: 'update_time', width: 150, templet: Tpl.toDateString('update_time')}
                        , {fixed: 'right', width: 300, align: 'center', toolbar: '#table-toolbar-tool'}
                    ]]
                });

                tableNode.on('tool(table-node)', function (obj) {
                    let pk_id = obj.data.id;
                    let name = obj.data.name;
                    switch (obj.event) {
                        case 'edit':
                            loadWindowEdit(pk_id);
                            break;
                        case 'permission':
                            toPermission(pk_id, name);
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

                //打开权限分配界面
                function toPermission(id, name) {
                    layer.load(2);
                    axios.get('{{ $url_permission }}', {params: {id: id}})
                        .then((res) => {
                            if (res.data.code) {
                                return false;
                            }
                            //弹出页面
                            layer.open({
                                type: 1,
                                title: name + '权限分配',
                                area: ['380px', '90%'],
                                content: res.data
                                , end: function () {
                                    // tableIns.refresh();
                                    layer.closeAll('loading');
                                }
                            });
                        }).catch((e) => {
                            throw e
                        }).then(() => {
                            layer.closeAll('loading');
                        });
                }

                // 删除数据
                function deleteData(data_id) {
                    layer.confirm('确定删除数据', {icon: 3, title: '提示'}, function (index) {
                        axios.delete(
                            '{{ $url_delete }}', {params: {id: data_id}}
                        ).then(function (response) {
                            if (0 === response.data.code) {
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
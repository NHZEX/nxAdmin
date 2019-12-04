@extends('layouts.master')
@section('title', 'apk分类')
@section('content')
    <div style="margin: 5px;">
        <table id="table-node" lay-filter="table-node"></table>
    </div>
@endsection
@section('javascript')
    <script type="text/html" id="table-main-toolbar">
        <div style="display:flex;">
            <button class="layui-btn" id="btn-refresh">
                <i class="layui-icon layui-icon-refresh"></i>刷新
            </button>
            @can('menu.edit')
            <button class="layui-btn layui-btn-normal" id="btn-add">
                <i class="layui-icon layui-icon-add-1"></i>添加
            </button>
            @endcan
            <button class="layui-btn" id="btn-export">
                <i class="layui-icon"></i>导出菜单
            </button>
        </div>
    </script>
    <script type="text/html" id="table-toolbar">
        @can('menu.edit')
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        @endcan
        @can('menu.del')
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delete">删除</a>
        @endcan
    </script>
    <script>
        require([
            'jquery', 'axios', 'helper', 'layui'
        ], function (
            $, axios, helper, layui
        ) {
            layui.use(['util', 'form', 'layer', 'table', 'treeGrid'], function () {
                let layer = layui.layer;
                let treeGrid = layui.treeGrid;

                let treeId = 'table-node';
                let tableIns = treeGrid.render({
                    id: treeId
                    , elem: '#' + treeId
                    , url: "{{ $url_table }}"
                    , cellMinWidth: 100
                    , idField: 'id'         //必須字段
                    , treeId: 'id'          //必須字段
                    , treeUpId: 'pid'       //树形父id字段名称
                    , treeShowName: 'title'  //以树形式显示的字段
                    , isFilter: false
                    , iconOpen: false       //是否显示图标 默认显示
                    , isOpenDefault: false  //节点默认是展开还是折叠 默认展开
                    , isPage: false
                    , loading: true
                    , method: 'post'
                    , toolbar: '#table-main-toolbar'
                    , cols: [[
                        {field: 'id', width: 100, title: 'id'}
                        , {field: 'title', width: 350, title: '名称'}
                        , {field: 'url', width: 350, title: 'URL'}
                        , {field: 'status_desc', width: 100, title: '状态'}
                        , {width: 200, title: '操作', 'toolbar': '#table-toolbar'}
                    ]]
                    , parseData: function (res) {
                        return res;
                    }
                    , done: function () {
                        treeGrid.treeOpenAll(treeId, true);
                    }
                    , onClickRow: function (index, o) {
                        // 单击
                    }
                    , onDblClickRow: function (index, o) {
                        // 双击
                    }
                });

                treeGrid.on('tool(table-node)', function(obj){
                    let pk_id = obj.data.id;
                    switch (obj.event) {
                        case 'edit':
                            loadWindowEdit(pk_id);
                            break;
                        case 'delete':
                            deleteData(pk_id);
                            break;
                    }
                    return false;
                });
                // 删除数据
                function deleteData(data_id)
                {
                    layer.confirm('确定删除数据', {icon: 3, title:'提示'}, function(index){
                        axios.delete(
                            '{{ $url_delete }}', {params: {pkid: data_id}}
                        ).then(function (response) {
                            if(0 === response.data.code) {
                                layer.msg('删除成功');
                                layer.close(index);
                            }
                        }).then(function () {
                            tableIns.refresh();
                        })
                    });

                }
                $('#btn-add').on('click', function () {
                    loadWindowEdit();
                });

                // 更改数据
                function loadWindowEdit(pk_id) {
                    let params = {};
                    pk_id && (params.pkid = pk_id);
                    helper.formModal()
                        .load('{{ $url_page_edit }}', params, '添加菜单', '740px')
                        .end(() => {
                            tableIns.refresh();
                        });
                }

                // 导出生成节点
                $('#btn-export').on('click', function () {
                    layer.load(2);
                    axios.post("{{ $url_export }}")
                        .then(() => {
                            tableIns.refresh();
                            layer.closeAll('loading');
                        });
                    return false;
                });
                // 刷新数据
                $('#btn-refresh').on('click', function () {
                    tableIns.refresh();
                    return false;
                });

            });
        });
    </script>
@endsection
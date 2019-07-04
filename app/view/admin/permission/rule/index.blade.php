@extends('layouts.master')
@section('title', '权限组管理')
@section('content')
    <div style="margin: 5px;">
        <div class="layui-fluid">
            <div class="layui-row">
                <div class="layui-col-md3" style="border-width: 1px; border-style: solid; border-color: #e6e6e6;">
                    <div id="group-tree" class="ztree"></div>
                </div>
                <div class="layui-col-md9" style="border-width: 1px; border-style: solid; border-color: #e6e6e6;">
                    <table id="table-permission" lay-filter="table-permission"></table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script type="text/html" id="table-main-toolbar">
        <div style="display:flex;">
            <button class="layui-btn" id="btn-refresh">
                <i class="layui-icon layui-icon-refresh"></i>刷新
            </button>
            <button class="layui-btn layui-btn-normal" id="btn-open-tree">
                <i class="layui-icon layui-icon-add-1"></i>展开/折叠全部
            </button>
            <button class="layui-btn layui-btn-normal" id="btn-generate">
                <i class="layui-icon layui-icon-add-1"></i>重新生成节点
            </button>
            <button class="layui-btn" id="btn-save">
                <i class="layui-icon"></i>保存修改
            </button>
            <button class="layui-btn" id="btn-export">
                <i class="layui-icon"></i>导出节点
            </button>
        </div>
    </script>
    <script type="text/html" id="table-permission-toolbar">
        <div style="width: 140%;display:flex">
            <strong>可选权限</strong>
            <button style="margin-left: 5px" class="layui-btn layui-btn-normal" id="open-all-tree">
                <i class="layui-icon layui-icon-add-1"></i>展开/折叠全部
            </button>
            <button class="layui-btn" id="btn-save">
                <i class="layui-icon"></i>保存修改
            </button>
        </div>
    </script>
    <script>
        require([
            'jquery', 'axios', 'zTree', 'layui', 'helper'
        ], function (
            $, axios, zTree, layui, helper
        ) {
            layui.use(['util', 'form', 'layer', 'treeGrid'], function () {
                let form = layui.form,
                    layer = layui.layer,
                    tree_grid = layui.treeGrid;
                let $groupTree = $('#group-tree');

                // 动态跳转控件尺寸
                let resizeId, lastSize = 0, doneResizing = function (targetHeight) {
                    $groupTree.height(targetHeight - 26);
                };
                $(window).unbind('.layui-resize').on('resize.layui-resize', function (e) {
                    let $this = $(e.target);
                    if (lastSize !== $this.height()) {
                        lastSize = $this.height();
                        clearTimeout(resizeId);
                        resizeId = setTimeout(doneResizing, 100, lastSize);
                    }
                });

                // zTree 的参数配置
                let setting = {};
                // zTree 的数据属性
                let zNodes = [
                    {
                        name: "权限组",
                        open: true,
                        children: [
                            {
                                name: "test1",
                                open: true,
                                children: [
                                    {name: "test1_1"},
                                    {name: "test1_2"}
                                ]
                            },
                            {
                                name: "test2",
                                open: true,
                                children: [
                                    {name: "test2_1"},
                                    {name: "test2_2"}
                                ]
                            }
                        ]
                    }
                ];
                // zTree 实例对象
                let zTreeObj = $.fn.zTree.init($groupTree, setting, zNodes);


                // 渲染权限组
                let treeId = 'table-permission';
                tree_grid.render({
                    id: treeId,
                    elem: '#' + treeId,
                    url: '{{ $url_permission_node }}',
                    cellMinWidth: 100,
                    height: 'full-15',
                    idField: 'id',
                    treeId: 'id',
                    treeUpId: 'pid',
                    treeShowName: 'nkey',
                    isFilter: false,
                    iconOpen: false,
                    isOpenDefault: false,
                    isPage: false,
                    loading: true,
                    method: 'post',
                    toolbar: '#table-permission-toolbar',
                    where: {queryMap: {flags: 'permission'}},
                    cols: [[
                        {type: 'checkbox'}
                        , {field: 'id', width: 100, title: 'id'}
                        , {field: 'nkey', width: 350, title: '节点命名'}
                        , {field: 'alias_name', width: 150, title: '别名'}
                        , {field: 'description', width: 200, title: '注释'}
                    ]],
                    done: () => {
                        //选中已有的权限
                        // tree_grid.setCheckStatus(treeId, "hash", hashArr.join(","));
                    }
                });
            });
        });
    </script>
@endsection
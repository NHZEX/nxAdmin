@extends('layouts.master')
@section('title', 'apk分类')
@section('content')
<div style="margin: 5px;">
    <table id="table-node" lay-filter="table-node"></table>
</div>
@endsection
@section('javascript')
<script type="text/html" id="table-toolbar-tool">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-xs" lay-event="apk_classify">apk分类</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>
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
@verbatim
<script type="text/html" id="checkLogin">
    <input type="checkbox" name="login_flag" value="{{d.id}}"
           {{#  if(d.login_flag){ }} checked {{#  } }}
           id="login_flag{{d.id}}" data-level="{{d.level}}"
           data-pid="{{d.pid}}" title="启用"
           lay-filter="login_flag">
</script>
<script type="text/html" id="checkPermission">
    <input type="checkbox" name="permission_flag" value="{{d.id}}"
           {{#  if(d.permission_flag){ }} checked {{#  } }}
           id="permission_flag{{d.id}}" data-level="{{d.level}}"
           data-pid="{{d.pid}}" title="启用"
           lay-filter="permission_flag">
</script>
<script type="text/html" id="checkMenu">
    <input type="checkbox" name="menu_flag" value="{{d.id}}"
           {{#  if(d.menu_flag){ }} checked {{#  } }}  id="menu_flag{{d.id}}" data-level="{{d.level}}"
           data-pid="{{d.pid}}" title="启用"
           lay-filter="menu_flag">
</script>
@endverbatim
<script>
    require([
        'jquery', 'axios', 'layui', 'helper'
    ], function (
        $, axios, layui, helper
    ) {
        layui.use(['util', 'form', 'layer', 'table', 'treeGrid'], function () {
            let form = layui.form,
                layer = layui.layer,
                tree_grid = layui.treeGrid;

            let tree_id = 'table-node';
            let tableIns = tree_grid.render({
                id: tree_id
                , elem: '#' + tree_id
                , url: "{{ $url_table }}"
                , cellMinWidth: 100
                , idField: 'id'         //必須字段
                , treeId: 'id'          //必須字段
                , treeUpId: 'pid'       //树形父id字段名称
                , treeShowName: 'nkey'  //以树形式显示的字段
                , isFilter: false
                , iconOpen: false       //是否显示图标 默认显示
                , isOpenDefault: false  //节点默认是展开还是折叠 默认展开
                , isPage: false
                , loading: true
                , method: 'post'
                , toolbar: '#table-main-toolbar'
                , cols: [[
                    {field: 'id', width: 100, title: 'id'}
                    , {field: 'nkey', width: 350, title: '节点命名'}
                    , {field: 'alias_name', width: 150, title: '别名', edit: 'text'}
                    , {field: 'class_name', width: 300, title: '类名'}
                    , {field: 'description', width: 200, title: '注释', edit: 'text'}
                    , {field: 'login_flag', title: '登录后可用', width: 130, templet: '#checkLogin', unresize: true}
                    , {field: 'permission_flag', title: '加入权限控制', width: 130, templet: '#checkPermission', unresize: true}
                    , {field: 'menu_flag', title: '加入菜单控制', width: 130, templet: '#checkMenu', unresize: true}
                ]]
                , parseData: function (res) {
                    return res;
                }
                , onClickRow: function (index, o) {
                    // 单击
                }
                , onDblClickRow: function (index, o) {
                    // 双击
                }
            });

            //修改别名或备注
            tree_grid.on(`edit(${tree_id})`, function (obj) {
                let node_id = obj.data.id;

                let input = {};
                input['id'] = node_id;
                input[obj.field] = obj.value;
                axios.post("{{ $url_update }}", input)
                    .then((res) => {});
                return false;
            });

            //监听锁定操作
            form.on('checkbox(login_flag)', function (obj) {
                checkTree(this, 'login_flag');
            });

            //监听锁定操作
            form.on('checkbox(permission_flag)', function (obj) {
                checkTree(this, 'permission_flag');
            });

            //监听锁定操作
            form.on('checkbox(menu_flag)', function (obj) {
                checkTree(this, 'menu_flag');
            });

            //checkbox联动
            function checkTree(objThis, name) {
                if (objThis.dataset.level === '1') {
                    let flags = $(`input[data-pid=${objThis.value}][name=${name}]`);

                    $.each(flags, (index, item) => {
                        //选中子节点
                        item.checked = objThis.checked;
                    });
                } else {
                    let flags = $(`input[data-pid=${objThis.dataset.pid}][name=${name}]`);
                    let pFlag = $(`#${name}${objThis.dataset.pid}`)[0];
                    $.each(flags, (index, item) => {
                        if (item.checked === true) {
                            pFlag.checked = true;
                        }
                    });
                }
                form.render();
            }

            // 保存修改
            $('#btn-save').on('click', function () {
                let data = getTreeLockList();
                let load = layer.load(2);
                axios.post("{{ $url_save_flags }}", {data: data}).then(
                    (res) => {
                        tableIns.refresh();
                        layer.close(load);
                    });
                return false;
            });

            function getTreeLockList(){
                let flagData = [];
                let dataList = tree_grid.getDataList(tree_id);
                $.each(dataList, (index, item)=>{
                    let loginCheck = $(`input[value=${item.id}][name='login_flag']`)[0].checked;
                    let permissionCheck = $(`input[value=${item.id}][name='permission_flag']`)[0].checked;
                    let menuCheck = $(`input[value=${item.id}][name='menu_flag']`)[0].checked;
                    flagData.push({
                        id: item.id,
                        login: loginCheck,
                        permission: permissionCheck,
                        menu: menuCheck,
                    });
                });
                return flagData;
            }


            // 重新生成节点
            $('#btn-generate').on('click', function () {
                layer.load(2);
                axios.post("{{ $url_generate }}")
                    .then((res) => {
                        tableIns.refresh();
                        layer.closeAll('loading');
                    });
                return false;
            });
            // 导出生成节点
            $('#btn-export').on('click', function () {
                layer.load(2);
                axios.post("{{ $url_export }}")
                    .then((res) => {
                        tableIns.refresh();
                        layer.closeAll('loading');
                    });
                return false;
            });
            // 打开或折叠全部节点
            $('#btn-open-tree').on('click', function () {
                let treedata = tree_grid.getDataTreeList(tree_id);
                tree_grid.treeOpenAll(tree_id, !treedata[0][tree_grid.config.cols.isOpen]);
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
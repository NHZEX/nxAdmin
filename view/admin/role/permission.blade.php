<div class="layui-layer-content" style="margin: 10px;display: flex">
    <div>
        <table id="table-permission" lay-filter="table-permission"></table>
    </div>
</div>
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
        'jquery', 'axios', 'layui', 'helper'
    ], function (
        $, axios, layui, helper
    ) {
        layui.use(['form', 'layer', 'table', 'treeGrid'], function () {
            let $ = layui.jquery,
                form = layui.form,
                layer = layui.layer,
                tree_grid = layui.treeGrid;

            let roleID = '{{ $role_id }}';
            let hashArr = Object({!! $hashArr !!});

            let treeId = 'table-permission';
            tree_grid.render({
                id: treeId,
                elem: '#' + treeId,
                url: '{{ $url_table }}',
                cellMinWidth: 100,
                height: 'full-60',
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
                    , {field: 'class_name', width: 300, title: '类名'}
                    , {field: 'description', width: 200, title: '注释'}
                ]],
                done: () => {
                    //选中已有的权限
                    tree_grid.setCheckStatus(treeId, "hash", hashArr.join(","));
                }
            });

            form.render();
            // 打开或折叠全部节点
            $('#open-all-tree').on('click', () => {
                let treedata = tree_grid.getDataTreeList(treeId);
                tree_grid.treeOpenAll(treeId, !treedata[0][tree_grid.config.cols.isOpen]);
                return false;
            });

            // 保存修改
            $('#btn-save').on('click', () => {
                layer.load(2);
                let checkList = tree_grid.checkStatus(treeId);
                let data = checkList.data.map(x => x.hash);
                axios.post('{{ $url_save }}', {id: roleID, hashArr: data})
                    .then((res) => {
                        layer.closeAll('loading');
                    });
                return false;
            });
        });
    });

</script>
<div style="margin: 5px">
    <div class="layui-btn-container">
        <button type="button" class="layui-btn layui-btn-sm" id="btn-save">保存权限</button>
    </div>
    <div id="permission"></div>
</div>
<script>
    require([
        'jquery', 'axios', 'layui', 'helper'
    ], function (
        $, axios, layui
    ) {
        layui.use(['layer', 'dtree'], function () {
            let $ = layui.jquery,
                layer = layui.layer,
                dtree = layui.dtree;

            let roleID = '{{ $role_id }}';
            let selected = Object(@json($selected));

            //渲染
            let data = [{
                'id': '__ROOT__',
                'title': '权限根',
                'spread': true,
                'children': Object(@json($permission))
            }];

            let formatter = {
                title: function(data) {
                    let s = data.title;
                    if (data.children){
                        if (data.desc) {
                            s += ' <span style=\'color:blue\'>(' + data.desc + ')</span>';
                        }
                    }
                    return s;
                }
            };

            dtree.render({
                elem: "#permission",
                checkbar: true,
                checkbarType: 'no-all',
                checkbarData: 'halfChoose',
                success: insertChecked,
                data: data, // 使用data加载
                formatter: formatter,
            });

            // 从树形结构推导父级选中状态
            function insertChecked(arrData){
                Array.isArray(arrData) && arrData.forEach(function (item, i) {
                    if(item.hasOwnProperty('children')) {
                        insertChecked(arrData[i]['children']);
                    }
                    arrData[i].basicData = {
                        'valid': arrData[i]['valid'],
                        'node': arrData[i]['id'],
                    };
                    let length = arrData[i]['children'].length;
                    if (item.hasOwnProperty('children') && length) {
                        let count = arrData[i]['children'].reduce((accumulator, currentValue) => {
                            return '0' !== currentValue['checkArr'] ? accumulator + 1 : accumulator;
                        }, 0);
                        if (!arrData[i]['checkArr']) {
                            arrData[i]['checkArr'] = (0 === count ? '0' : (count === length ? '1' : '2'));
                            arrData[i]['spread'] = true;
                        } else {
                            arrData[i]['spread'] = false;
                        }
                        if ('__ROOT__' === arrData[i]['id']) {
                            arrData[i]['spread'] = true;
                        }
                    } else {
                        arrData[i]['checkArr'] = selected.includes(arrData[i].id) ? '1' : '0';
                    }
                });
            }

            // 保存修改
            $('#btn-save').on('click', () => {
                let list = dtree.getCheckbarNodesParam('permission');
                layer.load(2);
                let data = list.filter(x => {
                    return JSON.parse(x.basicData).valid;
                }).map(x => x.nodeId);
                axios.post('{{ $url_save }}', {id: roleID, permission: data})
                    .then((res) => {
                        if (0 === res.data.code) {
                            layer.msg('保存成功');
                        }
                    })
                    .catch((e) => {
                        console.warn(e);
                    })
                    .then(() => {
                        layer.closeAll('loading');
                    });
                return false;
            });
        });
    });
</script>
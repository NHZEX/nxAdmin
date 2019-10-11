
<div id="toolbarDiv" style="overflow: auto;height: 100%;">
    <ul id="dtree-ul" class="dtree" data-id="0"></ul>
</div>

<script>
    require([
        'jquery', 'axios', 'layui'
    ], function (
        $, axios, layui
    ) {
        layui.use(['form', 'layer', 'dtree'], function () {
            let $ = layui.jquery,
                layer = layui.layer,
                dtree = layui.dtree;

            let checkIDs = Object({!! $check_ids !!}); //需要选中的ids
            let treeData = Object({!! $data !!}); //显示的数据
            let response = Object({!! $response !!}); //数据格式

            let treeID = 'dtree-ul';
            let treeIns = dtree.render({
                elem: `#${treeID}`,
                data: treeData,
                response: response,
                initLevel:"1",
                toolbarScroll: '#toolbarDiv',
                checkbar: true,
                checkbarType: 'no-all',
                checkbarData: 'halfChoose',
                success: dataFormat,
            });

            function dataFormat(arrData) {
                insertChecked(arrData);
            }

            function insertChecked(arrData){
                Array.isArray(arrData) && arrData.forEach(function (item, index) {
                    if(item.hasOwnProperty('children')) {
                        insertChecked(arrData[index]['children']);
                    }
                    arrData[index]['checkArr'] = checkIDs.includes(arrData[index].id) ? '1' : '0';

                    if (item.hasOwnProperty('children') && arrData[index]['children'].length) {
                        let isAll = arrData[index]['children'].reduce((accumulator, currentValue) => {
                            return accumulator && '0' !== currentValue['checkArr'];
                        }, true);
                        if ('0' !== arrData[index]['checkArr']) {
                            arrData[index]['checkArr'] = isAll ? '1' : '2';
                            arrData[index]['spread'] = true;
                        } else {
                            arrData[index]['spread'] = false;
                        }
                    }
                });
            }

            //选中事件
            dtree.on(`chooseDone(${treeID})`,function(obj){
                let checkParams = obj.checkbarParams;
                let saveIDs = checkParams.map(x => x.nodeId);

                axios.post("{{ $url_save }}",
                    {ids: saveIDs})
                    .then((res) =>{
                        if(res.data.code){
                            layer.msg(res.data.msg, {icon: 2});
                            return false;
                        }
                    });
            });
        });
    });
</script>
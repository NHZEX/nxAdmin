
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
                checkbarType: 'all',
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
                    arrData[index]['checkArr'] = {type: '0', isChecked: '0'};
                });
            }

            //选中已有的
            dtree.chooseDataInit(treeIns, checkIDs.join(','));

            //选中事件
            dtree.on(`chooseDone(${treeID})`,function(obj){
                let checkParams = obj.checkbarParams;
                let saveIDs = [];
                $.each(checkParams, (i, item)=>{
                    saveIDs.push(item.nodeId);
                });

                axios.post("{{ $url_save }}",
                    {id: '{{ $id }}', ids: saveIDs})
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
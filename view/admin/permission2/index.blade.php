@extends('layouts.master')
@section('title', '权限管理')
@section('content')
    <div id="app" style="margin: 5px">
        @verbatim
            <i-table size="small" border :loading="loading" :columns="columns" :data="data">
                <template slot-scope="{ row, index }" slot="action">
                    <i-button type="primary" size="small" @click="edit(row.id)">编辑</i-button>
                    <poptip
                            confirm
                            placement="top-end"
                            :transfer="true"
                            title="确认删除这条数据?"
                            @on-ok="del(index)">
                        <i-button type="error" size="small" :loading="row.__del_loading">删除</i-button>
                    </poptip>
                </template>
            </i-table>
        @endverbatim
    </div>
@endsection
@section('javascript')
    <script>
        require([
            'lodash', 'axios', 'iview', 'vue'
        ], (_, axios, iview, Vue) => {
            let v;

            const vueComponent = {};

            Vue.use(iview);

            loadMultiVueComponent(Vue, axios, vueComponent, () => {});

            window.vue = v = new Vue({
                el: '#app',
                data: {
                    loading: false,
                    columns: [
                        {title: '排序', key: 'sort', width: 90},
                        {title: '图标', key: 'icon', width: 80},
                        {title: '权限', key: 'name'},
                        {title: '备注', key: 'remarks', width: 200},
                        {title: '操作', slot: 'action', width: 200},
                    ],
                    data: [
                        {'sort': 1, 'name': 'user.get', 'remarks': '查询用户'},
                        {'sort': 1, 'name': 'user.add', 'remarks': '添加用户'},
                        {'sort': 1, 'name': 'user.del', 'remarks': '删除用户'},
                    ],
                },
                methods: {
                    render() {

                    },
                },
                watch: {
                },
                mounted: function () {

                },
            });
        });
    </script>
@endsection
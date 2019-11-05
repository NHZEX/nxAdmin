@extends('layouts.master')
@section('title', '权限管理')
@section('content')
    <div id="app" style="margin: 5px">
        @verbatim
            <div style="margin-bottom: 5px">
                <i-button type="primary" size="large" @click="edit()">添加权限</i-button>
                <i-button type="info" size="large" :loading="loading"
                          @click="render()">
                    <span v-if="!loading">刷新</span>
                    <span v-else>Loading...</span>
                </i-button>
            </div>
            <edit ref="edit" :permissions="data" @close="editClose"></edit>
            <i-table size="small" :loading="loading" :columns="columns" :data="data">
                <template slot-scope="{ row, index, column }" slot="sort">
                    <i-input size="small" placeholder="0" v-model.number="data[index][column.key]" type="number"></i-input>
                </template>
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

            const vueComponent = {
                'edit': '{{ url('edit')->build() }}'
            };

            Vue.use(iview);

            loadMultiVueComponent(Vue, axios, vueComponent, () => {});

            window.vue = v = new Vue({
                el: '#app',
                data: {
                    loading: false,
                    columns: [
                        {title: '排序', slot: 'sort', key: 'sort', width: 100},
                        {title: '权限', key: 'name'},
                        {title: '注释', key: 'desc', width: 300},
                        {title: '操作', slot: 'action', width: 200},
                    ],
                    data: [],
                },
                methods: {
                    render() {
                        this.loading = true;
                        axios.get('{{url('permissionTree')}}', {
                            params: {}
                        }).then((res) => {
                            this.data = res.data.data.map((v) => {
                                // 填充预设数据
                                v.__del_loading = false;
                                return v;
                            });
                        }).catch((error) => {
                            console.dir(error);
                            this.data = [];

                        }).then(() => {
                            this.loading = false;
                        });
                    },
                    edit(id) {
                        this.$refs['edit'].open(id)
                    },
                    editClose(isUpdate) {
                        if (isUpdate) {
                            this.render();
                        }
                    },
                    del(index) {
                        let row = this.data[index];
                        row.__del_loading = true;
                        axios.get('{{url('del')}}', {
                            params: {
                                id: row.id,
                            }
                        }).then((res) => {
                            this.$Notice.success({
                                title: '操作请求成功',
                                desc: `数据 ${row.id} 删除成功`,
                                duration: 6,
                            });
                        }).catch((error) => {
                            console.dir(error);
                            this.data = [];

                        }).then(() => {
                            row.__del_loading = false;
                            this.render();
                        });
                    }
                },
                watch: {
                },
                mounted: function () {
                    this.render();
                },
            });
        });
    </script>
@endsection
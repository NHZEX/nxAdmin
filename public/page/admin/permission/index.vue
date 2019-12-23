<div style="padding: 8px">
    <div style="margin-bottom: 5px">
        <edit v-if="auth.edit" :permissions="data" @on-submit-success="render">
            <i-button type="primary">添加权限</i-button>
        </edit>
        <i-button v-if="auth.scan" type="info" icon="md-refresh" :loading="loading.scan" @click="scan()">扫描权限</i-button>
        <i-button v-if="auth.lasting" type="info" icon="md-refresh" :loading="loading.lasting" @click="lasting()">导出权限</i-button>
    </div>
    <i-table size="small" :loading="loading.render" :columns="columns" :data="data">
        <template slot-scope="{ row, index, column }" slot="sort">
            <i-input size="small" placeholder="0" v-model.number="data[index][column.key]" type="number"></i-input>
        </template>
        <template slot-scope="{ row, index }" slot="action">
            <edit v-if="auth.edit" :id="row.id" :permissions="data" @on-submit-success="render">
                <i-button type="primary" size="small">编辑</i-button>
            </edit>
            <poptip
                    confirm
                    placement="top-end"
                    :transfer="true"
                    title="确认删除这条数据?"
                    @on-ok="del(index)">
                <i-button v-if="auth.del" type="error" size="small" :loading="row.__del_loading">删除</i-button>
            </poptip>
        </template>
    </i-table>
</div>
<script>
    export default {
        _require: {
            'lodash': '_',
            'axios': 'axios',
            'moment': 'moment',
        },
        _components: {
            'edit': '/page/admin/permission/edit.vue',
        },
        data: function () {
            return {
                auth: {
                    // todo 权限未完成
                    edit: true,
                    scan: true,
                    lasting: true,
                    del: true,
                },
                loading: {
                    lasting: false,
                    render: false,
                    scan: false,
                },
                columns: [
                    {title: '排序', slot: 'sort', key: 'sort', width: 100},
                    {title: '权限', key: '__name'},
                    {title: '注释', key: 'desc', width: 300},
                    {title: '操作', slot: 'action', width: 200},
                ],
                data: [],
            }
        },
        methods: {
            render() {
                this.loading.render = true;
                axios.get('/admin.permission/permissionTree', {
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
                    this.loading.render = false;
                });
            },
            del(index) {
                let row = this.data[index];
                row.__del_loading = true;
                axios.get('/admin.permission/del', {
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
            },
            scan() {
                this.loading.scan = true;
                axios.get('/admin.permission/scan', {
                    params: {}
                }).then((res) => {
                    this.$Notice.success({
                        title: '操作请求成功',
                        desc: `权限已经重新扫描`,
                        duration: 6,
                    });
                    this.render();
                }).catch((error) => {
                    console.dir(error);
                }).then(() => {
                    this.loading.scan = false;
                });
            },
            lasting() {
                this.loading.lasting = true;
                axios.get('/admin.permission/lasting', {
                    params: {}
                }).then((res) => {
                    this.$Notice.success({
                        title: '操作请求成功',
                        desc: `权限已经导出`,
                        duration: 6,
                    });
                }).catch((error) => {
                    console.dir(error);
                }).then(() => {
                    this.loading.lasting = false;
                });
            }
        },
        watch: {
        },
        mounted: function () {
            this.render();
        },
    };
</script>
@verbatim
    <modal
            v-model="display"
            :footer-hide="true"
            width="600"
            @on-visible-change="onVisibleChange"
            :styles="{top: '20px'}"
    >
        <tabs v-model="tabName">
            <tab-pane :label="title" name="permission">
                <i-form ref="FormItem" :model="formData" :rules="formRule" :disabled="formDisabled" :label-width="90">
                    <form-item prop="pid" label="父节点">
                        <i-input v-model="formData.pid" :readonly="isEdit" placeholder="Enter something..." ></i-input>
                    </form-item>
                    <form-item prop="name" label="权限名称">
                        <i-input v-model="formData.name" placeholder="Enter something..."></i-input>
                    </form-item>
                    <form-item prop="control" label="授权控制">
                        <div >
                            <i-table border size="small"
                                     :loading="controlLoading" :columns="controlColumns" :data="controlData"
                                     max-height="350" :show-header="false"
                            >
                                <template slot-scope="{ row, index }" slot="action">
                                    <i-button type="error" size="small" @click="delControl(row)" disabled>移除</i-button>
                                </template>
                            </i-table>
                        </div>
                    </form-item>
                    <form-item prop="desc" label="描述">
                        <i-input v-model="formData.desc" :maxlength="256" show-word-limit type="textarea" placeholder="Enter something..." ></i-input>
                    </form-item>
                    <form-item>
                        <i-button type="primary" :loading="loading" @click="submit()">提交</i-button>
                    </form-item>
                </i-form>
            </tab-pane>
            <tab-pane label="节点" name="control">
                <i-table size="small" max-height="550"
                         :loading="nodeLoading" :columns="nodeColumns" :data="nodeData"
                         @on-selection-change="controlSelectionChange"
                >
                    <template slot-scope="{ row, column }" slot="raw-html">
                        <span v-html="row[column.key]"></span>
                    </template>
                </i-table>
            </tab-pane>
        </tabs>
    </modal>
@endverbatim
<script>
    (function () {
        function pretreatNodeData(data) {
            return data.map(x => {
                // x['_disabled'] = 0 === x['__level'];
                x['_disabled'] = true;
                x['_checked'] = false;
                return x;
            });
        }
        return {
            props: {
                permissions: {
                    type: Array,
                    required: true,
                },
            },
            data: function () {
                return {
                    tabName: 'permission',
                    loading: false,
                    display: false,
                    title: '',
                    isChange: false,
                    isEdit: false,
                    formDisabled: false,
                    formData: {
                        id: 0,
                        pid: '',
                        name: '',
                        control: {},
                    },
                    formRule: {
                    },
                    controlLoading: false,
                    controlColumns: [
                        {title: '节点', key: 'name', width: 260},
                        {title: '注解', key: 'remarks'},
                        {title: '操作', slot: 'action', width: 80},
                    ],
                    controlData: [
                    ],
                    nodeLoading: false,
                    nodeColumns: [
                        {type: 'selection', width: 40, align: 'center'},
                        {title: '节点', slot: 'raw-html', key: '__name'},
                        {title: '注解', key: 'remarks'},
                    ],
                    nodeData: pretreatNodeData(Object(@json($node_tree, JSON_UNESCAPED_UNICODE))),
                }
            },
            methods: {
                onVisibleChange(visible) {
                    if (false === visible) {
                        this.reset();
                        this.$emit('close', this.isChange);
                    }
                },
                reset() {
                    // 重置表单
                    this.loading = false;
                    this.$refs['FormItem'].resetFields();
                    this.controlData = [];
                    this.formData.id = 0;
                },
                open(id) {
                    this.tabName = 'permission';
                    this.display = true;
                    this.isChange = false;
                    this.isEdit = _.isFinite(id);
                    this.title = this.isEdit ? '编辑权限' : '添加权限';
                    if (this.isEdit) {
                        this.render(id);
                    }
                },
                render(id) {
                    this.formDisabled = true;
                    axios.get('{{url('get')}}', {
                        params: {
                            id: id,
                        }
                    }).then(res => {
                        this.formData = res.data.data;
                        this.applyControlSelected(this.formData.control.allow);
                    }).catch((err) => {
                        console.warn(err);
                    }).then(() => {
                        this.formDisabled = false;
                    });
                },
                delControl(row) {
                    let index = _.findIndex(this.nodeData, {id: row.id});
                    if (-1 !== index) {
                        this.nodeData[index]._checked = false;
                    }
                    this.syncControlSelected(this.nodeData.filter(x => x._checked));
                },
                applyControlSelected(control) {
                    let selection = [];
                    this.nodeData.map(x => {
                        x._checked = control.includes('node@' + x.name);
                        if (x._checked) {
                            selection.push(x);
                        }
                    });
                    this.syncControlSelected(selection);
                },
                controlSelectionChange(selection) {
                    let ids = selection.map(x => {
                        return x.id;
                    });
                    this.nodeData.map(x => {
                        x._checked = ids.includes(x.id);
                    });
                    this.syncControlSelected(selection);
                },
                syncControlSelected(selection) {
                    this.controlData = _.cloneDeep(selection).map(x => {
                        x.name = 'node@' + x.name;
                        return x;
                    });
                },
                submit() {
                    this.formData.control.allow = this.controlData.map(x => x.name);
                    this.$refs['FormItem'].validate((valid) => {
                        if (!valid) {
                            this.$Message.error('表单数据存在无效值');
                            return;
                        }

                        this.isChange = true;
                        this.loading = true;

                        axios.post('{{url('save')}}', this.formData)
                            .then(res => {
                                res.data.code;
                                this.$Notice.success({
                                    title: '操作请求成功',
                                    desc: `${this.isEdit ? '更新' : '添加'}权限: ${this.formData.name}`,
                                    duration: 6,
                                });
                            })
                            .catch(err => {
                                console.warn(err)
                            })
                            .then(() => {
                                this.loading = false;
                            })
                    });
                }
            }
        }
    })()
</script>
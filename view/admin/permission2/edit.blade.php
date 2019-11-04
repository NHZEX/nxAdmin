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
                <i-form ref="FormItem" :model="formData" :rules="formRule" :label-width="90">
                    <form-item prop="pid" label="父节点">
                        <i-select v-model.number="formData.pid">
                            <i-option :value="0">正常</i-option>
                        </i-select>
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
                                    <i-button type="error" size="small" @click="delControl(row)">移除</i-button>
                                </template>
                            </i-table>
                        </div>

                    </form-item>
                    <form-item prop="remarks" label="备注">
                        <i-input v-model="formData.remarks" type="textarea" placeholder="Enter something..." ></i-input>
                    </form-item>
                    <form-item>
                        <i-button type="primary" :loading="loading" @click="submit()">提交</i-button>
                        <i-button @click="reset()" style="margin-left: 8px">重置</i-button>
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
                x['_disabled'] = 0 === x['__level'];
                x['_checked'] = false;
                return x;
            });
        }
        return {
            data: function () {
                return {
                    tabName: 'permission',
                    loading: false,
                    display: false,
                    title: '',
                    isChange: false,
                    isEdit: false,
                    formData: {
                        id: 0,
                        pid: 0,
                        name: '',
                        control: '',
                    },
                    formRule: {
                        server_name: [
                            {required: true, trigger: 'blur'},
                        ],
                        server_ip: [
                            {required: true, trigger: 'blur'},
                            {pattern: /^((25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}$/, message: '不是有效的ipv4地址', trigger: 'blur'},
                        ],
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
                    nodeData: pretreatNodeData(Object(@json($node, JSON_UNESCAPED_UNICODE))),
                }
            },
            methods: {
                onVisibleChange(visible) {
                    if (false === visible) {
                        // this.reset();
                        this.$emit('on-close', this.isChange);
                    }
                },
                open(id) {
                    this.tabName = 'permission';
                    this.display = true;
                    this.isChange = false;
                    this.isEdit = _.isFinite(id);
                    this.title = this.isEdit ? '编辑权限' : '添加权限';
                    // this.render(id);
                },
                delControl(row) {
                    let index = _.findIndex(this.nodeData, {id: row.id});
                    if (-1 !== index) {
                        this.nodeData[index]._checked = false;
                    }
                    this.syncControlSelected(this.nodeData.filter(x => x._checked));
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
                    this.formData.control = this.controlData.map(x => x.name).join(',');
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
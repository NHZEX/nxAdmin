@verbatim
    <modal
            v-model="display"
            :footer-hide="true"
            title="编辑权限"
            width="600"
            @on-visible-change="onVisibleChange"
            :styles="{top: '20px'}"
    >
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
    </modal>
@endverbatim
<script>
    (function () {
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
                        {title: '节点', key: 'name', width: 280},
                        {title: '注解', key: 'desc'},
                    ],
                    controlData: [
                    ],
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
                        this.syncControlSelected(this.formData.allow)
                    }).catch((err) => {
                        console.warn(err);
                    }).then(() => {
                        this.formDisabled = false;
                    });
                },
                syncControlSelected(selection) {
                    this.controlData = selection;
                },
                submit() {
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
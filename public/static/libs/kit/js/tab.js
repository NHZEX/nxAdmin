/**
 * Name:tab.js
 * Author:Van
 * E-mail:zheng_jinfan@126.com
 * Website:http://kit.zhengjinfan.cn/
 * LICENSE:MIT
 */
(function (Tab) {
    if (typeof define === 'function' && define.amd) {
        // 支持 AMD
        define(['jquery', 'vue', 'axios', 'lodash', 'iview'], Tab);
    } else {
        throw Error('not support, missing amd dependence')
    }
})(function ($, Vue, axios, _, iview) {
    var doc = $(document),
        win = $(window);

    var Tab = {
        config: {
            elem: undefined,
            mainUrl: 'main.html',
            renderType: 'iframe',
            openWait: false,
            delayOpen: false,
        },
        vue: null,
    };
    /**
     * 配置
     * @param options
     */
    Tab.set = function (options) {
        var that = this;
        $.extend(true, that.config, options);
        return that;
    };
    /**
     * 渲染选项卡
     */
    Tab.render = function () {
        var that = this,
            configData = that.config;
        if (configData.elem === undefined) {
            layui.hint().error('Tab error:请配置选择卡容器.');
            return that;
        }

        Vue.use(iview);
        window.loadingBar = iview.LoadingBar;

        that.vue = new Vue({
            el: configData.elem,
            data: {
                logingBar: {
                    request: 0,
                    response: 0,
                },
                toolShow: false,
                boxHeight: 0,
                current: 0,
                list: [
                    {id: 0, title: '控制面板', uri: configData.mainUrl}
                ]
            },
            computed: {
                currTab() {
                    return this.list[this.find(this.current)];
                }
            },
            watch: {
                current() {
                    if (configData.onSwitch) {
                        let data = this.list[this.find(this.current)];
                        configData.onSwitch({
                            id: data.id
                        });
                    }
                }
            },
            methods: {
                find(id) {
                    return _.findIndex(this.list, ['id', id]);
                },
                open(data) {
                    let id = parseInt(data.id);
                    if (this.find(id) > 0) {
                        this.current = id;
                    } else {
                        this.add({
                            id: id, title: data.title, uri: data.url,
                        });
                    }
                },
                add(tab) {
                    let isVue = tab.uri.endsWith('.vue');
                    if (isVue) {
                        let call = loadVueComponent2(axios, tab.uri);
                        (new Promise(call)).then((template) => {
                            tab.template = template;
                            this.list.push(tab);
                            this.current = tab.id;
                        })
                    } else {
                        this.list.push(tab);
                        this.current = tab.id;
                    }
                },
                switch(id) {
                    this.current = id;
                },
                close(id, other) {
                    this.toolShow = false;
                    if (this.list.length === 1) {
                        return;
                    }
                    if (true === id) {
                        this.current = 0;
                        this.list.splice(1);
                        return;
                    }
                    if (true === other) {
                        for (let index = (this.list.length - 1); index > 0; index--) {
                            if (id !== this.list[index].id) {
                                this.list.splice(index, 1);
                            }
                        }
                        // this.list = [this.list[0], this.currTab];
                        return;
                    }
                    let index = this.find(id);
                    if (index > 0) {
                        this.current = this.list[index - 1].id;
                        this.list.splice(index, 1);
                    }
                },
                refresh() {
                    this.toolShow = false;
                    let tab = this.currTab;
                    let isVue = tab.uri.endsWith('.vue');
                    if (!isVue) {
                        this.$refs['tab-c' + tab.id][0].contentWindow.location.reload();
                    } else {
                        this.list.splice(this.find(tab.id), 1);
                        this.add(tab)
                    }
                },
                winResize: function () {
                    win.on('resize', () => {
                        this.boxHeight = $(this.$el).height() - 48;
                    }).resize();
                },
                winHashChange() {
                    win.on('hashchange', (e) => {
                        console.log('hashchange', e);
                    });
                },
            },
            mounted: function () {
                this.winResize();
                this.winHashChange();
            },
        });

        return that;
    };

    return Tab;
});

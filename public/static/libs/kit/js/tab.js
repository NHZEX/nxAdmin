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
        define(['jquery', 'nprogress', 'layui', 'kitutils', 'artTemplate', 'layelement', 'layer'], Tab);
    } else {
        throw Error('not support, missing amd dependence')
    }
})(function ($, NProgress, layui, kitutils, template) {
    var element = layui.element,
        layer = layui.layer,
        utils = kitutils,
        doc = $(document),
        win = $(window),
        renderType = {
            page: 'page',
            iframe: 'iframe'
        };

    let tplFrameRender = template.compile([
        '<div class="layui-tab layui-tab-card kit-tab" lay-filter="{{ iframeFilterName }}">'
        ,   '<ul class="layui-tab-title" style="width: calc(100% - 132px)">'
        ,       '<li class="layui-this" lay-id="-1" data-url="{{ firstPageUrl }}">'
        ,       '<i class="layui-icon layui-icon-home"></i> {{ firstPageTitle }}'
        ,       '</li>'
        ,   '</ul>'
        ,   '<div class="kit-tab-refresh"><i class="fa fa-refresh"></i></div>'
        ,   '<div class="kit-tab-tool">操作&nbsp;<i class="fa fa-caret-down"></i></div>'
        ,   '<div class="kit-tab-tool-body layui-anim layui-anim-upbit">'
        ,       '<ul>'
        ,           '<li class="kit-item" data-target="refresh">刷新当前选项卡</li>'
        ,           '<li class="kit-line"></li>'
        ,           '<li class="kit-item" data-target="closeCurrent">关闭当前选项卡</li>'
        ,           '<li class="kit-item" data-target="closeOther">关闭其他选项卡</li>'
        ,           '<li class="kit-line"></li>'
        ,           '<li class="kit-item" data-target="closeAll">关闭所有选项卡</li>'
        ,       '</ul>'
        ,   '</div>'
        ,   '<div class="layui-tab-content">'
        ,       '<div class="layui-tab-item layui-show" lay-item-id="-1">'
        ,       '<iframe src="{{ firstPageUrl }}"></iframe>'
        ,       '</div>'
        ,   '</div>'
        ,'</div>'
    ].join("\n"));
    let tplTitleRender = template.compile([
        '<li class="layui-this" lay-id="{{ id }}" data-url="{{ url }}">'
        ,   '{{if icon.indexOf(\'fa-\') !== -1}}'
        ,       '<i class="fa {{ icon }}" aria-hidden="true"></i>&nbsp;{{ title }}'
        ,   '{{else}}'
        ,       '<i class="layui-icon {{ icon }}"></i>&nbsp;{{ title }}'
        ,   '{{/if}}'
        ,   '<i class="layui-icon layui-unselect layui-tab-close">&#x1006;</i>'
        ,'</li>'
    ].join("\n"));
    let tplContentRender = template.compile([
        '<div class="layui-tab-item layui-show" lay-item-id="{{ id }}">'
        ,   '<iframe src="{{ url }}"></iframe>'
        ,'</div>'
    ].join("\n"));

    var Tab = {
        config: {
            elem: undefined,
            mainUrl: 'main.html',
            renderType: 'iframe',
            openWait: false,
            delayOpen: false,
        }
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
        tabPrivate.configData = configData;
        tabPrivate.createTabDom();
        return that;
    };
    /**
     * 添加一个选项卡
     */
    Tab.tabAdd = function (params) {
        params = $.extend({}, {
            delayOpen: false,
        }, params);
        tabPrivate.tabAdd(params);
    };
    /**
     * 关闭一个选项卡
     */
    Tab.close = function (layId) {
        tabPrivate.tabDelete(layId);
    };
    Tab.getId = function () {
        return tabPrivate.getCurrLayId();
    };
    //私用对象
    var tabPrivate = {
        configData: {},
        filterName: 'kitTab', //过滤器名
        titleDom: undefined,
        contentDom: undefined,
        parentElem: undefined, //要存放的容器
        //检查选项卡DOM是否存在
        tabDomExists: function () {
            var that = this;
            if (doc.find('div.kit-tab').length > 0) {
                that.titleDom = $('.kit-tab ul.layui-tab-title');
                that.contentDom = $('.kit-tab div.layui-tab-content');
                return true;
            }
            return false;
        },
        /**
         * 创建选项卡DOM
         */
        createTabDom: function () {
            var that = this,
                configData = that.configData;
            that.parentElem = configData.elem;
            if (that.tabDomExists())
                return;
            //渲染
            $(configData.elem).html(tplFrameRender({
                firstPageUrl: configData.mainUrl
                , firstPageTitle: '控制面板'
                , iframeFilterName: that.filterName
            }));
            that.titleDom = $('.kit-tab ul.layui-tab-title');
            that.contentDom = $('.kit-tab div.layui-tab-content');
            var toolDom = $('.kit-tab-tool'),
                toolBodyDom = $('.kit-tab-tool-body');
            //监听刷新点击事件
            $('.kit-tab-refresh').on('click', function () {
                var layId = that.titleDom.children('li[class=layui-this]').attr('lay-id');
                var item = that.contentDom.children('div[lay-item-id=' + layId + ']').children('iframe');
                item.attr('src', item.attr('src'));
                return false;
            });
            //监听操作点击事件
            toolDom.on('click', function () {
                toolBodyDom.toggle();
            });
            //监听操作项点击事件
            toolBodyDom.find('li.kit-item').each(function () {
                var $that = $(this);
                var target = $that.data('target');
                $that.off('click').on('click', function () {
                    var layId = that.titleDom.children('li[class=layui-this]').attr('lay-id');
                    switch (target) {
                        case 'refresh': //刷新
                            var item = that.contentDom.children('div[lay-item-id=' + layId + ']').children('iframe');
                            item.attr('src', item.attr('src'));
                            break;
                        case 'closeCurrent': //关闭当前
                            if (layId != -1)
                                that.tabDelete(layId);
                            break;
                        case 'closeOther': //关闭其他
                            that.titleDom.children('li[lay-id]').each(function () {
                                var curId = $(this).attr('lay-id');
                                if (curId != layId && curId != -1)
                                    that.tabDelete(curId);
                            });
                            break;
                        case 'closeAll': //关闭所有
                            that.titleDom.children('li[lay-id]').each(function () {
                                var curId = $(this).attr('lay-id');
                                if (curId != -1)
                                    that.tabDelete(curId);
                            });
                            that.tabChange(-1);
                            break;
                    }
                    toolDom.click();
                });
            });
            //监听浏览器窗口改变事件
            that.winResize();
            // 窗口切换事件
            if (configData.onSwitch) {
                element.on('tab(' + that.filterName + ')', function (data) {
                    configData.onSwitch({
                        index: data.index,
                        elem: data.elem,
                        layId: that.titleDom.children('li').eq(data.index).attr('lay-id')
                    });
                });
            }
        },
        load: function () {
            return layer.load(0, {shade: [0.3, '#333']});
        },
        closeLoad: function (index) {
            setTimeout(function () {
                index && layer.close(index);
            }, 500);
        },
        getBodyContent: function (url, callback) {
            return utils.getBodyContent(utils.loadHtml(url, callback));
        },
        /**
         * 监听浏览器窗口改变事件
         */
        winResize: function () {
            var that = this,
                configData = that.configData;
            win.on('resize', function () {
                var currBoxHeight = $(that.parentElem).height(); //获取当前容器的高度
                $('.kit-tab .layui-tab-content iframe').height(currBoxHeight - 47);
            }).resize();
        },
        /**
         * 检查选项卡是否存在
         */
        tabExists: function (layId) {
            var that = this;
            return that.titleDom.find('li[lay-id=' + layId + ']').length > 0;
        },
        /**
         * 删除选项卡
         */
        tabDelete: function (layId) {
            element.tabDelete(this.filterName, layId);
        },
        /**
         * 设置选中选项卡
         */
        tabChange: function (layId) {
            element.tabChange(this.filterName, layId);
        },
        /**
         * 获取选项卡对象
         */
        getTab: function (layId) {
            return this.titleDom.find('li[lay-id=' + layId + ']');
        },
        /**
         * 添加一个选项卡，已存在则获取焦点
         */
        tabAdd: function (options) {
            var that = this,
                configData = that.configData,
                loadIndex = undefined;
            options = options || {
                id: new Date().getTime(),
                title: '新标签页',
                icon: 'fa-file',
                url: '404.html'
            };
            // 已存在则切换
            let tabId = options.id;
            if (that.tabExists(tabId)) {
                that.tabChange(tabId);
                return;
            }
            // 初始动画
            NProgress.start();
            if (configData.openWait) {
                loadIndex = that.load();
            }
            let baseDelay = 20, npCount = 0, loadAnimation;
            let loadFun = () => {
                if (npCount < 90) {
                    npCount += 0.1;
                    baseDelay += baseDelay;
                    NProgress.set(npCount);
                    loadAnimation = window.setTimeout(loadFun, baseDelay);
                }
            };
            loadFun();
            // 渲染模板
            let titleHtm = $(tplTitleRender(options));
            let contentHtm = $(tplContentRender(options));
            // 挂载载入完毕事件
            contentHtm.find('iframe').on('load', function () {
                clearTimeout(loadAnimation);
                NProgress.done();
                configData.openWait && loadIndex && that.closeLoad(loadIndex);
            });
            // 挂载选项卡关闭事件
            titleHtm.find('i.layui-tab-close').on('click', function () {
                //关闭之前
                if (configData.closeBefore) {
                    if (configData.closeBefore(options)) {
                        that.tabDelete(tabId);
                    }
                } else {
                    that.tabDelete(tabId);
                }
            });
            // 开始加载
            that.titleDom.append(titleHtm);
            that.contentDom.append(contentHtm);
            // 选中选项卡
            that.tabChange(tabId);
            that.winResize();
        },
        /**
         * 获取当前选项卡的id
         */
        getCurrLayId: function () {
            return this.titleDom.find('li.layui-this').attr('lay-id');
        }
    };

    return Tab;
});

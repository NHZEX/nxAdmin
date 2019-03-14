/**
 * Name:navbar.js
 * Author:Van
 * E-mail:zheng_jinfan@126.com
 * Website:http://kit.zhengjinfan.cn/
 * LICENSE:MIT
 */
(function(Navbar) {
    if (typeof define === 'function' && define.amd) {
        // 支持 AMD
        define(['jquery', 'layui', 'artTemplate', 'layer', 'layelement'], Navbar);
    } else {
        throw Error('not support, missing amd dependence')
    }
})(function($, layui, template) {
    var doc = $(document),
        layer = layui.layer,
        element = layui.element;
    var tplRender = template.compile([
        '{{ each data item index }}',
            '{{ if item.spread }}',
                '<li class="layui-nav-item layui-nav-itemed">',
            '{{ else }}',
                '<li class="layui-nav-item">',
            '{{ /if}}',
            // 父
            '{{ if item.children !== undefined && item.children.length > 0 }}',
                '<a href="javascript:;">',
                '{{ if item.icon.indexOf("fa-") !== -1 }}',
                    '<i class="fa {{ item.icon }}" aria-hidden="true"></i>',
                '{{ else }}',
                '<i class="layui-icon {{ item.icon }}"></i>',
            '{{ /if }}',
            '<span>{{ item.title }}</span>',
            '</a>',
            // 子
            '{{ set children = item.children }}',
            '<dl class="layui-nav-child">',
            '{{ each children childItem childIndex }}',
                '<dd>',
                '<a data-nav-id="{{ childItem.id }}" href="javascript:;" kit-target data-options="{url:\'{{ childItem.url }}\',icon:\'{{ childItem.icon }}\',title:\'{{ childItem.title }}\',id:\'{{ childItem.id }}\'}">',
                '{{ if childItem.icon.indexOf("fa-") !== -1 }}',
                    '<i class="fa {{ childItem.icon }}" aria-hidden="true"></i>',
                '{{ else }}',
                    '<i class="layui-icon {{ childItem.icon }}"></i>', // 原文输出
                '{{ /if }}',
                '<span> {{ childItem.title }}</span>',
                '</a>',
                '</dd>',
            '{{ /each }}',
            '</dl>',
            '{{ else }}',
            '<a href="javascript:;" kit-target data-options="{url:\'{{ item.url }}\',icon:\'{{ item.icon }}\',title:\'{{ item.title }}\',id:\'{{ item.id }}\'}">',
            '{{ if item.icon.indexOf("fa-") !== -1 }}',
                '<i class="fa {{ item.icon }}" aria-hidden="true"></i>',
            '{{ else }}',
                '<i class="layui-icon {{ item.icon }}"></i>', // 原文输出
            '{{ /if }}',
            '<span> {{ item.title }}</span>',
            '</a>',
            '{{ /if }}',
            '</li>',
        '{{ /each }}'
    ].join("\n"));

    var Navbar = {
        config : {
            data: undefined, //静态数据（菜单数据）
            cached: false, //是否缓存
            elem: undefined, //容器
            filter: 'kitNavbar', //过滤器名称
        }
    };
    /**
     * 配置
     */
    Navbar.set = function(options) {
        var that = this;
        that.config.data = undefined;
        $.extend(true, that.config, options);
        return that;
    };
    // 确认容器'ul[kit-navbar]'是否存在?
    /**
     * 是否已设置了elem
     */
    Navbar.hasElem = function() {
        var that = this,
            configData = that.config;
        if (configData.elem === undefined && doc.find('ul[kit-navbar]').length === 0 && $(configData.elem)) {
            layui.hint().error('Navbar error:请配置Navbar容器.');
            return false;
        }
        return true;
    };
    /**
     * 获取容器的jq对象(这是要将模板内容写入到该容器内)
     */
    Navbar.getElem = function() {
        var configData = this.config;
        return (configData.elem !== undefined && $(configData.elem).length > 0) ? $(configData.elem) : doc.find('ul[kit-navbar]');
    };
    /**
     * 菜单被选中事件
     * navId对应tab的layid
     */
    Navbar.clicked = function(navId) {
        // （强行）扫描所的a[kit-target]元素，找出对应的切换
        doc.find('a[kit-target]').each(function() {
            var that = $(this);
            if (String(that.data('nav-id')) === navId) {
                that.addClass('kit-nav-here');
            } else {
                that.removeClass('kit-nav-here');
            }
        });
    };
    /**
     * 绑定特定a标签的点击事件
     */
    Navbar.bind = function(callback, params) {
        var that = this,
            configData = that.config;
        var defaults = {
            target: undefined,
            showTips: true
        };
        $.extend(true, defaults, params);

        var targetDom = defaults.target === undefined ? doc : $(defaults.target);

        targetDom.find('a[kit-target]').each(function() {
            var that = $(this),
                tipsId = undefined;
            if (defaults.showTips) {

                // a的鼠标经过浮动提示
                that.hover(function () {
                    tipsId = layer.tips($(this).children('span').text(), this);
                }, function () {
                    if (tipsId)
                        layer.close(tipsId);
                });
            }

            // 有data-options属性的dom元素
            that.off('click').on('click', function() {
                that.addClass('kit-nav-here');
                var options = that.data('options');
                var data;
                if (options !== undefined) {
                    try {
                        data = new Function('return ' + options)();
                    } catch (e) {
                        layui.hint().error('Navbar 组件a[data-options]配置项存在语法错误：' + options)
                    }
                } else {
                    data = {
                        icon: that.data('icon'),
                        id: that.data('id'),
                        title: that.data('title'),
                        url: that.data('url'),
                    };
                }
                typeof callback === 'function' && callback(data);
            });
        });

        // 点击，隐藏(显示)整个菜单栏，dom元素类为kit-side-fold
        $('.kit-side-fold').off('click').on('click', function() {
            var sideDom = doc.find('div.kit-side');
            if (sideDom.hasClass('kit-sided')) {
                sideDom.removeClass('kit-sided');
                doc.find('div.layui-body').removeClass('kit-body-folded');
                doc.find('div.layui-footer').removeClass('kit-footer-folded');
            } else {
                sideDom.addClass('kit-sided');
                doc.find('div.layui-body').addClass('kit-body-folded');
                doc.find('div.layui-footer').addClass('kit-footer-folded');
            }
        });
        return that;
    };
    /**
     * 渲染navbar
     */
    Navbar.render = function(callback) {
        var that = this,
            configData = that.config,
            menuData = [];
        var navbarLoadIndex = layer.load(2);
        if (!that.hasElem())
            return that;
        var elemDom = that.getElem();
        if (configData.data !== undefined && configData.data.length >= 0) {
            menuData = configData.data; // 菜单数据
        }
        // 菜单数据
        // console.log(menuData);
        var tIndex = setInterval(function() {
            if (menuData.length >= 0) {
                clearInterval(tIndex);
                //渲染模板
                elemDom.html(tplRender({data: menuData}));
                element.init();
                //绑定a标签的点击事件
                that.bind(function(data) {
                    typeof callback === 'function' && callback(data); // 将初始化时的callback也付给点击时调用
                });
                navbarLoadIndex && layer.close(navbarLoadIndex);
            }
        }, 50);

        return that;
    };
    return Navbar;
});

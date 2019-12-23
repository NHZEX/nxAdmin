/**
 * Name:app.js
 * Author:Van
 * E-mail:zheng_jinfan@126.com
 * Website:http://kit.zhengjinfan.cn/
 * LICENSE:MIT
 */
(function(App) {
    if (typeof define === 'function' && define.amd) {
        // 支持 AMD
        define(['jquery', 'layui', 'kitnavbar', 'kittab'], App);
    } else {
        throw Error('not support, missing amd dependence')
    }
})(function($, layui, kitnavbar, kittab) {
    var App = {
        config: {
            type: 'iframe',
            data: undefined, // 菜单数据
        }
    };
    /**
     * 设置
     * @param options 外部设置参数
     */
    App.set = function(options) {
        var that = this;
        $.extend(true, that.config, options);
        return that;
    };
    /**
     * 初始化
     */
    App.init = function() {
        // 菜单数据在外面传入，还是在window的全局取？
        // console.log(window.menu);
        var that = this,
            tmpConfig = that.config;
        if (tmpConfig.type === 'iframe') {
            kittab.set({
                mainUrl: tmpConfig.mainUrl,
                elem: '#container',
                onSwitch: function(data) { //选项卡切换时触发
                    kitnavbar.clicked(String(data.id)); //navbar菜单被选中
                },
                closeBefore: function(data) { //关闭选项卡之前触发
                    return true; //返回true则关闭
                }
            }).render();
            //navbar加载方式三，设置data本地数据
            kitnavbar.set({
                elem:'#navbarContainer', // 查找id='navbarContainer'的容器，用于写入模板内容
                data: tmpConfig.data,
            }).render(function(data) {
                kittab.vue.open(data);
            });
        }

        // ripple start 波纹效果
        var addRippleEffect = function(e) {
            // console.log(e);
            layui.stope(e);
            var target = e.target;
            if (target.localName !== 'button' && target.localName !== 'a') return false;
            var rect = target.getBoundingClientRect();
            var ripple = target.querySelector('.ripple');
            if (!ripple) {
                ripple = document.createElement('span');
                ripple.className = 'ripple';
                ripple.style.height = ripple.style.width = Math.max(rect.width, rect.height) + 'px';
                target.appendChild(ripple);
            }
            ripple.classList.remove('show');
            var top = e.pageY - rect.top - ripple.offsetHeight / 2 - document.body.scrollTop;
            var left = e.pageX - rect.left - ripple.offsetWidth / 2 - document.body.scrollLeft;
            ripple.style.top = top + 'px';
            ripple.style.left = left + 'px';
            ripple.classList.add('show');
            return false;
        };
        document.addEventListener('click', addRippleEffect, false);
        // ripple end

        return that;
    };
    layui.link(layui.cache.kitBase + 'css/app.css', function () {}, 'kitapp');
    return App;
});
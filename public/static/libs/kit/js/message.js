/**
 * Name:message.js
 * Author:Van
 * E-mail:zheng_jinfan@126.com
 * Website:http://kit.zhengjinfan.cn/
 * LICENSE:MIT
 */
(function(Message) {
    if (typeof define === 'function' && define.amd) {
        // 支持 AMD
        define(['jquery', 'layui'], Message);
    } else {
        throw Error('not support, missing amd dependence')
    }
})(function($, layui) {
    var body = $('body'),
        messageClass = '.kit-message';
    var Message = {
        times : 1
    };
    /**
     * 信息
     */
    Message.message = function() {
        // 有对应的dom元素
        var msgDom = $(messageClass);
        if (msgDom.length > 0)
            return msgDom;
        body.append('<div class="kit-message"></div>');
        return $(messageClass);
    };
    Message.show = function(options) {
        var that = this,
            messageDom = that.message(),
            id = that.times,
            options = options || {},
            skin = options.skin === undefined ? 'default' : options.skin,
            msg = options.msg === undefined ? '请输入一些提示信息!' : options.msg,
            autoClose = options.autoClose === undefined ? true : options.autoClose;
        var tpl = [
            '<div class="kit-message-item layui-anim layui-anim-upbit" data-times="' + id + '">',
            '<div class="kit-message-body kit-skin-' + skin + '">',
            msg,
            '</div>',
            '<div class="kit-close kit-skin-' + skin + '"><i class="fa fa-times" aria-hidden="true"></i></div>',
            '</div>'
        ];
        messageDom.append(tpl.join(''));
        var timesDom = messageDom.children('div[data-times=' + id + ']').find('i.fa-times');
        timesDom.off('click').on('click', function() {
            var t = $(this).parents('div.kit-message-item').removeClass('layui-anim-upbit').addClass('layui-anim-fadeout');
            setTimeout(function() {
                t.remove();
            }, 1000);
        });
        if (autoClose) { //是否自动关闭
            setTimeout(function() {
                timesDom.click();
            }, 3000);
        }
        that.times++;
    };

    layui.link(layui.cache.kitBase + 'css/message.css');
    return Message;
});
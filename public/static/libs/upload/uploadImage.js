/**
 @ Name：文件上传
 @ Author：xxx
 @ License：xxx
 */
;(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        let preloading = ['layupload',];
        define(['jquery', 'artTemplate', 'layui', ...preloading], factory);
    } else {
        throw Error('not support, missing amd dependence')
    }

}(function ($, template, layui) {
    let upload = layui.upload;

    // 开启模板调试
    template.defaults.debug = layui.cache.debug;
    // BODY
    let $body = $('body');
    // 字符常量
    let MOD_NAME = 'uploadImage'
        , ELEM = '.layui-uploadImage'
        , UPLOAD_DD_PROMPT = '.layui-uploadImage-prompt'
        , TPLID_LIST = '__layer-upload-image-list-tpl'
        , TPLID_DD = '__layer-upload-image-dd-tpl';

    let TPL_DD = '<div class="dd">\n' +
        '  <img alt="">\n' +
        '  <div class="operate">\n' +
        // '      <i class="toleft layui-icon"></i>\n' +
        // '      <i class="toright layui-icon"></i>\n' +
        '      <i class="close layui-icon" style="color: #FF5722;"></i>\n' +
        '  </div>\n' +
        '  <i class="layui-icon layui-icon-upload-drag layui-uploadImage-background"></i>\n' +
        '  <span class="layui-uploadImage-prompt">请选择</span>\n' +
        '</div>';
    // 写入模板到页面
    if (false === $body.find('#' + TPLID_DD).is('script')) {
        $body.append('<script type="text/html" id="' + TPLID_DD + '">\n' + TPL_DD + '</script>');
    }
    if (false === $body.find('#' + TPLID_LIST).is('script')) {
        $body.append(
            '<script type="text/html" id="' + TPLID_LIST + '">\n' +
            '<span class="layui-uploadImage-upload-{{updateId}}"></span>' +
            '<div class="layui-uploadImage-list" data-updateId="{{updateId}}">\n' +
            '{{include "' + TPLID_DD + '"}}' +
            '</div>\n' +
            '</script>');
    }

    //外部接口
    let uploadImage = {
        index: layui.uploadImage ? (layui.uploadImage.index + 10000) : 0

        //设置全局项
        , set: function (options) {
            let that = this;
            that.config = $.extend({}, that.config, options);
            return that;
        }

        //事件监听
        , on: function (events, callback) {
            return layui.onevent.call(this, MOD_NAME, events, callback);
        }
    };
    //操作当前实例
    let thisIns = function () {
        let that = this
            , options = that.config
            , id = options.id || options.index;

        that.fileList = {};

        return {
            reload: function (options) {
                that.reload.call(that, options);
            }
            , config: options
        }
    };
    //构造器
    let Class = function (options) {
        let that = this;
        that.index = ++uploadImage.index;
        that.config = $.extend(true, {}, that.config, uploadImage.config, options);
        that.render();
    };

    //默认配置
    Class.prototype.config = {
        name: 'upload_images'
        , multiple: false
        , fileSize: 512
        , number: 3
        , uploadUrl: null
        , parseData: null
    };

    //渲染视图
    Class.prototype.render = function () {
        let that = this;
        let options = that.config;

        options.elem = $(options.elem);
        options.elem.html(template(TPLID_LIST, {updateId: that.index}));

        let $upload = options.elem.find('span.layui-uploadImage-upload-' + that.index);
        options.elem.on('click', 'div.dd', function (event) {
            let $target = $(event.target);
            let $dd = $target.is('div.dd') ? $target : $target.parents('div.dd');
            if (!$dd.data('upload')) {
                $upload.trigger('click');
                return false;
            }
            if ($target.is('i.close')) {
                options.multiple || $dd.parent('div').append(TPL_DD);
                $dd.remove();
                return false;
            }
            return false;
        });
        let uploadChoose = function (obj) {
            obj.preview(function (index, file, result) {
                // [index = 文件索引(本次对话有效), file = 文件对象, result = base64编码]
                // var fileKey = [file.name, file.size, file.type].join('|')
                let $dd = options.elem.find('div.dd:last');
                $dd.find('img').prop('src', result);
                $dd.find('img').prop('alt', file.name);
                $dd.find(UPLOAD_DD_PROMPT).text('准备上传');
                $dd.data('upload', true);
                $dd.data('file-index', index);
                options.multiple && $dd.parent('div').append(TPL_DD);
                that.fileList[index] = $dd;
            });
        };
        let uploadBefore = function (obj) {
            obj.preview(function (index, file, result) {
                // [index = 文件索引(本次对话有效), file = 文件对象, result = base64编码]
                if (that.fileList[index]) {
                    let $dd = that.fileList[index];
                    $dd.find(UPLOAD_DD_PROMPT).text('上传中...').css('color', '#007ced');
                }
            })
        };
        let uploadDone = function (res, index, upload) {
            let $dd = that.fileList[index], result;
            if (0 === res.code) {
                let form_name = options.multiple ? options.name + '[]' : options.name;
                if ($.isFunction(options.parseData)) {
                    result = options.parseData(res.data);
                } else {
                    result = JSON.stringify(result);
                }
                $dd.append('<input type="text" name="' + form_name + '" value="' + result + '">');
                $dd.find(UPLOAD_DD_PROMPT).text('上传完成').css('color', '#1eb813');
            } else {
                $dd.find(UPLOAD_DD_PROMPT).text('上传失败').css('color', '#FF5722');
                layer.msg('上传失败-' + res.code + '-' + res.msg);
            }
        };
        let uploadError = function (index, upload) {
            let $dd = that.fileList[index];
            if ($dd) {
                $dd.find(UPLOAD_DD_PROMPT).text('上传失败').css('color', '#FF5722');
            }
            layer.msg('上传请求失败');
        };
        let uploadAllDone = function () {

        };
        let uploadField = "__layui_tmp_" + options.name + '_' + that.index;
        let uploadIns = upload.render({
            elem: $upload
            , url: options.uploadUrl
            , data: {field: uploadField}
            , accept: 'images'
            , acceptMime: 'image/*'
            , field: uploadField
            , size: options.fileSize
            , multiple: options.multiple
            , number: options.number
            , choose: uploadChoose
            , before: uploadBefore
            , done: uploadDone
            , allDone: uploadAllDone
            , error: uploadError
        });

        that.renderImg();
    };

    Class.prototype.renderImg = function () {
        let that = this;
        let options = that.config;
        let img_src = options.elem.data('img'); // 格式：.png#local:/upload/.png;
        let $dd, form_name, imgs;
        if (img_src) {
            imgs = img_src.split(';');
            let temp_imgs = [];
            if (imgs) {
                $.each(imgs, function (t_i, t_src) {
                    temp_imgs.push(t_src.split(':'));
                });
            }
            $.each(temp_imgs, function (i, src) {
                $dd = options.elem.find('div.dd:last');
                $dd.find('img').prop('src', src[1]);
                $dd.find(UPLOAD_DD_PROMPT).hide();
                $dd.data('upload', true);
                $dd.data('file-index', false);
                form_name = options.multiple ? options.name + '[]' : options.name;
                $dd.append('<input type="text" name="' + form_name + '" value="' + src[0] + '">');
                options.multiple && $dd.parent('div').append(TPL_DD);
            })
        }
    };

    //核心入口
    uploadImage.render = function (options) {
        let ins = new Class(options);
        return thisIns.call(ins);
    };

    return uploadImage;
}));
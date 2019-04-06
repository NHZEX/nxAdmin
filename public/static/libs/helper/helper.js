;(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        let preloading = ['layer', 'layform'];
        define(['jquery', 'lodash', 'crc32', 'axios', 'verify', 'layui', ...preloading], factory);
    } else {
        throw Error('not support, missing amd dependence')
    }

}(function ($, _, crc32, axios, verify, layui) {
    let helper = {};

    helper.noop = function() {};

    /**
     * 时间字符串转换时间戳
     * @param data
     * @returns {number}
     */
    helper.dateTimeToUnixTimestamp = function(data) {
        let value = new Date(data);
        if(value.toString() === 'Invalid Date') {
            return 0;
        }
        return value.getTime() / 1000;
    };

    /**
     * 获取随机字符串
     * @param {int} len
     * @returns {string}
     * @see https://stackoverflow.com/a/1349426/10242420
     */
    helper.randomString = function(len) {
        let text = '';
        let possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for (let i = 0; i < len; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    };

    /**
     * 是否是一个数据对象
     * @param obj
     * @returns {boolean | * | jQuery}
     */
    helper.isDataObject = function (obj) {
        return $.isPlainObject(obj) && !$.isEmptyObject(obj);
    };

    /**
     * 解析Url
     * @param url
     * @returns {{protocol: (null|string|RTCIceProtocol|*), hostname: *, search: *, port: (null|*|string|MessagePort|number), host: *, hash: *, pathname: (null|string|string|*)}}
     */
    helper.urlParse = function (url) {
        let parser = document.createElement('a');
        parser.href = url || '/';
        return {
            protocol: parser.protocol, // => "http:"
            hostname: parser.hostname, // => "example.com"
            port: parser.port,     // => "3000"
            pathname: parser.pathname, // => "/pathname/"
            search: parser.search,   // => "?search=test"
            hash: parser.hash,     // => "#hash"
            host: parser.host,     // => "example.com:3000"
        }
    };

    /**
     * 获取URL Hash
     * @param url
     * @param prefix
     * @returns {string}
     */
    helper.urlHash = function (url, prefix = 'page-') {
        let path = helper.urlParse(url).pathname || '/';
        // 修复crc32结果出现负数问题
        return prefix + (crc32.str(path) >>> 0);
    };

    /**
     * 加载表单模态窗口
     * TODO 分离表单模态框
     * @param url
     * @param params
     * @param title
     * @param area
     * @param option
     * @param success
     * @param end
     */
    helper.modal = function (url, params, title, area, option, success, end) {
        let layer = layui.layer;
        let load_index = layer.load(2);
        axios.get(url, {params: params})
            .then(function (response) {
                if (_.isString(response.data)) {
                    let URL_HASH = helper.urlHash(url);
                    let layer_option = $.extend({}, {
                        type: 1
                        , id: URL_HASH
                        , title: title || '窗口'
                        , content: response.data
                        , area: area || '500px'
                        , success: function (layero, index) {
                            $.isFunction(success) && success(layero, index);
                        }
                        , end: function () {
                            $.isFunction(end) && end();
                            if(window.hasOwnProperty(URL_HASH)) {
                                delete window[URL_HASH];
                            }
                        }
                    }, option);
                    layer.open(layer_option);
                }
            })
            .then(function () {
                layer.close(load_index);
            });
    };

    helper.swapDefaultOption = function (opt) {
        // 窗口初始化前
        opt.initBefore || (opt.initBefore = null);
        // 窗口初始化后
        opt.initAfter || (opt.initAfter = null);
        // TODO 表单初始化前
        opt.formInitBefore || (opt.formInitBefore = null);
        // TODO 表单初始化后
        opt.formInitAfter || (opt.formInitAfter = null);
        // 表单提交前
        opt.formSubmitBefore || (opt.formSubmitBefore = null);
        // TODO 表单提交后
        opt.formSubmitAfter || (opt.formSubmitAfter = null);
        // 处理默认编辑数据 {data}
        opt.funHandleEditData || (opt.funHandleEditData = null);
        // 处理表单提交数据 {form, data} 弃用
        opt.funFormSubmit || (opt.funFormSubmit = null);
        // 处理表单提交数据 {form, serialize, next}
        opt.handleFormSubmit || (opt.handleFormSubmit = null);
        // 模态窗口加载完成 {layero, modalIndex}
        opt.funModalSucceed || (opt.funModalSucceed = null);
        // 模态窗口已经关闭 {}
        opt.funModalEnd || (opt.funModalEnd = null);
        return opt;
    };

    /**
     * 加载表单模态窗口
     * @returns {modal}
     */
    helper.formModal = function () {
        let layer = layui.layer;
        let layform = layui.form;

        let modal = function() {
            let self = this;
            self.$form = null;
            self.form = null;
            self.modalHash = null;
            // 窗口加载完成 {layero, modalIndex}
            self.callSuccess = helper.noop;
            // 窗口已经销毁 {}
            self.callEnd = helper.noop;
        };
        modal.fn = modal.prototype;
        /**
         * 设置表单数据
         * @param editDate
         * @param $form
         */
        modal.fn.formSetData = function(editDate, $form) {
            let $formInput = $form.find('input, textarea, select');
            $formInput.each(function (id, input) {
                let $input = $(input), inputName, inputType = $input.attr('type');
                // 是否别名映射
                inputName = $input.is('[data-as-name]') ? $input.data('as-name') : $input.attr('name');
                // 表单组件赋值
                if(editDate.hasOwnProperty(inputName)) {
                    let value = editDate[inputName];
                    if(inputType === 'checkbox') {
                        $input.prop('checked', !!value);
                    } else if(inputType === 'radio') {
                        $input.prop('checked', $input.val() === String(value));
                    } else {
                        $input.val(value);
                    }
                }
            });
        };
        /**
         * 模态窗口加载完毕
         * @param call
         * @returns {modal}
         */
        modal.fn.success = function(call) {
            $.isFunction(call) && (this.callSuccess = call);
            return this;
        };
        /**
         * 模态窗口以销毁
         * @param call
         * @returns {modal}
         */
        modal.fn.end = function(call) {
            $.isFunction(call) && (this.callEnd = call);
            return this;
        };
        /**
         * 加继模态窗口
         * @param url
         * @param params
         * @param title
         * @param area
         * @param option
         * @returns {modal}
         */
        modal.fn.load = function(url, params, title, area, option) {
            let that = this;
            let loadIndex = layer.load(2);
            that.modalHash = helper.urlHash(url);
            axios.get(url, {params: params})
                .then(function (response) {
                    if (!_.isString(response.data)) {
                        return false;
                    }
                    that._loadModal(response.data, title, area, option);
                })
                .catch(function (error) {
                    throw error;
                })
                .then(function () {
                    layer.close(loadIndex);
                });
            return this;
        };
        /**
         * 加载模态&表单
         * @param pageCode
         * @param title
         * @param area
         * @param option
         * @private
         */
        modal.fn._loadModal = function(pageCode, title, area, option) {
            let that = this;
            let layerOption = $.extend({}, {
                type: 1
                , id: that.modalHash
                , title: title || '窗口'
                , content: pageCode
                , area: area || ['500px', '500px']
                , success: function (layero, modalIndex) {
                    let $pageMain = $(`#find-${that.modalHash}`);
                    let $content = $pageMain.parent('div');
                    let $forms = $pageMain.find('> form');
                    let editData = $pageMain.data('edit-data');
                    let currSwap = helper.swapDefaultOption(window['swap'][that.modalHash]);

                    if($pageMain.length === 0) {
                        console.warn('无法初始化窗口');
                    }

                    // 编辑数据预处理
                    if($.isFunction(currSwap.funHandleEditData)) {
                        editData = currSwap.funHandleEditData(editData, $content);
                    }

                    // 窗口初始化
                    let init = function() {
                        // 表单初始化
                        $forms.each(function (id, form) {
                            let $form = $(form);
                            // 自动赋值
                            if(helper.isDataObject(editData)) {
                                that.formSetData(editData, $form);
                            }
                            $form.attr('lay-filter') || $form.attr('lay-filter', helper.randomString(16));
                            // 刷新layui组件
                            layform.render(null, $form.attr('lay-filter'));

                            // 绑定表单验证器
                            (new verify(form, $content))
                                .pass(function (form, target) {
                                    let that = this;
                                    // 数据提交
                                    let loadIndex = layer.load(2);
                                    let $form = $(form);
                                    let method = ($form.attr('method') || 'post').toLowerCase();
                                    let options = {
                                        url: $form.prop('action')
                                        , method: method
                                        , enctype: $form.attr('enctype') || 'json' //TODO 未完成
                                        , layer_elem: target
                                    };

                                    let formSubmit = function(options) {
                                        let serialize;
                                        switch (options.enctype) {
                                            case "multipart/form-data":
                                                serialize = that.serializeFormData();
                                                break;
                                            case "json":
                                                serialize = that.serializeObject();
                                                break;
                                            default:
                                                serialize = that.serializeURLSearchParams();
                                        }

                                        // 数据回调处理
                                        if ($.isFunction(currSwap.funFormSubmit)) {
                                            console.warn('funFormSubmit-弃用');
                                            serialize = currSwap.funFormSubmit(form, serialize);
                                            if (false === serialize) {
                                                layer.close(loadIndex);
                                                return false;
                                            }
                                        }

                                        let goForm = () => {
                                            // 选择适当的方法
                                            if (method === 'get' || method === 'head') {
                                                options.params = serialize;
                                            } else {
                                                options.data = serialize;
                                            }
                                            axios.request(options)
                                                .then(function (response) {
                                                    if(0 === response.data.code) {
                                                        layer.tips('操作完成', $(target), {
                                                            tips: [2, '#01AAED'],
                                                            end: function() {
                                                                layer.close(modalIndex);
                                                            }
                                                        })
                                                    }
                                                }).catch(function (error) {
                                                throw error;
                                            }).then(function() {
                                                layer.close(loadIndex);
                                            });
                                        };

                                        if ($.isFunction(currSwap.handleFormSubmit)) {
                                            currSwap.handleFormSubmit(form, serialize, goForm);
                                        } else {
                                            goForm();
                                        }
                                    };

                                    if($.isFunction(currSwap.formSubmitAfter)) {
                                        currSwap.formSubmitAfter.call(that, form, options, modalIndex, () => {formSubmit(options)});
                                    } else {
                                        formSubmit(options);
                                    }
                                })
                        });
                        $forms.find('button[data-close]').off('click').on('click', function () {
                            let confirm = $(this).attr('data-confirm') || '确定要取消编辑吗';
                            let confirmIndex = layer.confirm(confirm, {icon: 3, title:'提示'}, function () {
                                layer.close(confirmIndex);
                                layer.close(modalIndex);
                            }, ()=>{});
                            return false;
                        });


                        if($.isFunction(currSwap.initAfter)) {
                            currSwap.initAfter($content, editData, modalIndex, done);
                        } else {
                            done();
                        }
                    };

                    // 窗口完成初始化
                    let done = function () {
                        if($.isFunction(currSwap.funModalSucceed)) {
                            currSwap.funModalSucceed(layero, modalIndex);
                        }
                        that.callSuccess(layero, modalIndex);
                    };

                    if($.isFunction(currSwap.initBefore)) {
                        currSwap.initBefore($content, editData, modalIndex, init);
                    } else {
                        init();
                    }
                }
                , end: function () {
                    let currSwap = helper.swapDefaultOption(window['swap'][that.modalHash]);

                    if($.isFunction(currSwap.funModalEnd)) {
                        currSwap.funModalEnd();
                    }
                    that.callEnd();

                    // 清理痕迹
                    if(window.hasOwnProperty(that.modalHash)) {
                        delete window[that.modalHash];
                    }
                }
            }, option);
            layer.open(layerOption);
        };
        return new modal();
    };
    
    return helper;
}));
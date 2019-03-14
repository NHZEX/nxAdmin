;(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        let preloading = ['layer', 'layform'];
        define(['jquery', 'lodash', 'crc32', 'axios', 'layui', ...preloading], factory);
    } else {
        throw Error('not support, missing amd dependence')
    }

}(function ($, _, crc32, axios, layui) {
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
        // 处理默认编辑数据 {data}
        opt.funHandleEditData || (opt.funHandleEditData = null);
        // 处理表单提交数据 {form, data}
        opt.funFormSubmit || (opt.funFormSubmit = null);
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
                    let editDate = $pageMain.data('edit-data');
                    let currSwap = helper.swapDefaultOption(window['swap'][that.modalHash]);

                    if($pageMain.length === 0) {
                        console.warn('无法初始化窗口');
                    }

                    // 编辑数据预处理
                    if($.isFunction(currSwap.funHandleEditData)) {
                        editDate = currSwap.funHandleEditData(editDate, $content);
                    }

                    // 窗口初始化
                    let init = function() {
                        // 表单初始化
                        $forms.each(function (id, form) {
                            let $form = $(form);
                            // 自动赋值
                            if(helper.isDataObject(editDate)) {
                                that.formSetData(editDate, $form);
                            }
                            $form.attr('lay-filter') || $form.attr('lay-filter', helper.randomString(16));
                            // 刷新layui组件
                            layform.render(null, $form.attr('lay-filter'));

                            // 绑定表单验证器
                            helper.vali2(form, $content)
                                .pass(function (form, target) {
                                    let that = this;
                                    // 数据提交
                                    let loadIndex = layer.load(2);
                                    let $form = $(form);
                                    let method = ($form.attr('method') || 'put').toLowerCase();
                                    let options = {
                                        url: $form.prop('action')
                                        , method: method
                                        , enctype: $form.attr('enctype') || 'json' //TODO 未完成
                                        , layer_elem: target
                                    };

                                    // 数据回调处理
                                    let serialize = that.serialize('obj');
                                    if ($.isFunction(currSwap.funFormSubmit)) {
                                        serialize = currSwap.funFormSubmit(form, serialize);
                                        if (false === serialize) {
                                            layer.close(loadIndex);
                                            return false;
                                        }
                                    }
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
                            currSwap.initAfter($content, editDate, modalIndex, done);
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
                        currSwap.initBefore($content, editDate, modalIndex, init);
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

    /**
     * 表单验证器
     * @param form
     * @param content
     * @returns {vali2}
     */
    helper.vali2 = function (form, content) {

        const STYLE_DANGER = 'layui-form-danger2';
        const MARK_VALIDATE_ERROR = 'validate-error';
        const REGEXP_0 = /^(.*?)\[/;
        const REGEXP_1 = /\[(.*?)]/g;

        let vali = function () {
            let that = this;
            that.html5 = true;
            that.gForm = null;
            that.gContent = null;
            that.gFormItem = null;
            that.passCall = helper.noop;
            // 表单元素
            this.tags = 'input,textarea,select';
            // 检测元素事件
            this.checkEvent = {change: true, blur: false, keyup: true};
            // 去除字符串两头的空格
            this.trim = function (str) {
                return str.replace(/(^\s*)|(\s*$)/g, '');
            };
            // 检测元素是否忽略
            this.isIgnore = function (ele) {
                let $ele = $(ele);
                // 兼容layui组件渲染
                if($ele.is('select, [type=checkbox], [type=radio]') && !$ele.is('[lay-ignore]')) {
                    return !$ele.next().is('.layui-unselect, .layui-form-select, .layui-form-radio, .layui-form-checkbox');
                }
                return $ele.is('[disabled]:visible');
            };
            // 检测属性是否有定义
            this.hasProp = function (ele, prop) {
                if (typeof prop !== "string") {
                    return false;
                }
                let attrProp = ele.getAttribute(prop);
                return (typeof attrProp !== 'undefined' && attrProp !== null && attrProp !== false);
            };
            // 判断表单元素是否为空
            this.isEmpty = function (ele, value) {
                let trimValue = this.trim(ele.value);
                value = value || ele.getAttribute('placeholder');
                return (trimValue === "" || trimValue === value);
            };
            // 正则验证表单元素
            this.isRegexPass = function (ele, regex, params) {
                let inputValue = ele.value, dealValue = this.trim(inputValue);
                regex = regex || ele.getAttribute('pattern');
                if (dealValue === "" || !regex) {
                    return true;
                }
                if (dealValue !== inputValue) {
                    (ele.tagName.toLowerCase() !== "textarea") ? (ele.value = dealValue) : (ele.innerHTML = dealValue);
                }
                return new RegExp(regex, params || 'i').test(dealValue);
            };
            // 检侧所的表单元素
            this.isAllpass = function (elements) {
                if (!elements) {
                    return true;
                }
                let allpass = true, that = this, first = null;
                if (elements.size && elements.size() === 1 && elements.get(0).tagName.toLowerCase() === "form") {
                    elements = $(elements).find(that.tags);
                } else if (elements.tagName && elements.tagName.toLowerCase() === "form") {
                    elements = $(elements).find(that.tags);
                }
                elements.each(function () {
                    if (that.checkInput(this) === false) {
                        first instanceof HTMLElement || (first = this);
                        allpass = false;
                    }
                });
                if(first instanceof HTMLElement) {
                    that.focus(first);
                }
                return allpass;
            };
            /**
             * 定位一个元素
             * @param ele
             */
            this.focus = function (ele) {
                let $ele = $(ele);
                let scroll = false;
                // 适配layui元素
                if (!$ele.is('[lay-ignore]') && $ele.is('select, [type=checkbox], [type=radio]')){
                    $ele = $(ele).next();
                    scroll = true;
                } else if($ele.is('[type=submit]')) {
                    scroll = true;
                }
                if (scroll) {
                    // offset().top innerHeight outerHeight height
                    let position = $ele.offset().top - $ele.innerHeight();
                    if (position < 100) {
                        that.gContent.animate({
                            scrollTop: position + (that.gContent.innerHeight() / 2)
                        }, 500);
                    }
                    if (position > that.gContent.height() - 100) {
                        that.gContent.animate({
                            scrollTop: position - (that.gContent.innerHeight() / 2)
                        }, 500);
                    }
                } else {
                    $ele.focus();
                }
            };
            // 验证标志
            this.remind = function (input) {
                this.showError(input, input.getAttribute('title') || '');
                return false;
            };
            /**
             * 检测表单单元
             * @param input
             * @returns {boolean}
             */
            this.checkInput = function (input) {
                let that = this;
                let $input = $(input);
                let type = $input.prop('type').toLowerCase();
                let tag = $input.prop('tagName').toLowerCase(), isRequired = $input.is('[required]');
                let allpass = true;
                if ($input.is('[data-auto-none]')
                    || type === 'submit' || type === 'reset'
                    || (type === 'file' && !that.html5)
                    || type === 'image'
                    || this.isIgnore(input)
                ) {
                    return true;
                }
                if (that.html5) {
                    if(input.validity && !input.validity.valid) {
                        if (type === 'radio') {
                            let radioGroup = that.gFormItem.filter('[type=radio][name=' + input.name + ']');
                            if(!radioGroup.is('.'+MARK_VALIDATE_ERROR)) {
                                that.showError(input, input.validationMessage);
                            }
                        } else {
                            that.showError(input, input.validationMessage);
                        }
                        allpass = false;
                    }
                } else {
                    if (type === "radio" && !$input.is(':checked')) {
                        let radioGroup = that.gFormItem.filter('[type=radio][name=' + input.name + ']');
                        if(radioGroup.is('[required]')) {
                            if(!radioGroup.is('.'+MARK_VALIDATE_ERROR)) {
                                input.title = '请从这些选项中选择一个。';
                                this.remind(input);
                            }
                            allpass = false;
                        } else {
                            allpass = true;
                        }
                    } else if (type === "checkbox" && isRequired && !$input.is(':checked')) {
                        input.title = '请勾选该选项。';
                        this.remind(input);
                        allpass = false;
                    } else if (tag === "select" && isRequired && !$input.val()) {
                        input.title = '请选择一个选项。';
                        this.remind(input);
                        allpass = false;
                    } else if (isRequired && this.isEmpty(input)) {
                        input.title = '请填写此字段。';
                        this.remind(input);
                        allpass = false;
                    } else if(!this.isRegexPass(input)) {
                        input.title = '请与所请求的格式保持一致';
                        this.remind(input);
                        allpass = false;
                    }
                }
                if (allpass) {
                    if (type === 'radio') {
                        that.gFormItem
                            .filter('[type=radio][name=' + input.name + '].' + MARK_VALIDATE_ERROR)
                            .each(function (index, input) {
                                that.hideError(input);
                            })

                    } else {
                        this.hideError(input);
                    }
                }
                return allpass;
            };
            // 错误消息显示
            this.showError = function (ele, content) {
                let $ele = $(ele);
                let $targetEle;
                // 适配layui元素
                if (!$ele.is('[lay-ignore]') && $ele.is('select, [type=checkbox], [type=radio]')){
                    $targetEle = $ele.next();
                } else {
                    $targetEle = $ele;
                }
                // 显示错误信息
                $ele.addClass(STYLE_DANGER);
                $ele.addClass(MARK_VALIDATE_ERROR);
                this.insertError($targetEle.get(0)).addClass('fadeInRight animated').css({width: 'auto'}).html(content);
                return false;
            };
            // 错误消息消除
            this.hideError = function (ele) {
                let $ele = $(ele);
                let $targetEle;
                // 适配layui元素
                if (!$ele.is('[lay-ignore]') && $ele.is('select, [type=checkbox], [type=radio]')){
                    $targetEle = $ele.next();
                } else {
                    $targetEle = $ele;
                }
                // 隐藏错误信息
                $ele.removeClass(STYLE_DANGER);
                $ele.removeClass(MARK_VALIDATE_ERROR);
                this.insertError($targetEle.get(0)).removeClass('fadeInRight').css({width: '30px'}).html('');
            };
            // 错误消息标签插入
            this.insertError = function (ele) {
                let $html, $ele = $(ele);
                if (!$ele.data('insert')) {
                    $html = $('<span style="-webkit-animation-duration:.2s;animation-duration:.2s;padding-right:20px;color:#a94442;position:absolute;right:0;font-size:12px;z-index:2;display:block;width:34px;text-align:center;pointer-events:none"></span>');
                    $html.css({top: $(ele).position().top + 'px', paddingBottom: $(ele).css('paddingBottom'), lineHeight: $(ele).css('height')});
                    $html.insertAfter(ele);
                    $ele.data('insert', true);
                } else {
                    $html = $ele.next('.animated');
                }
                return $html;
            };
            /**
             * 寻找全部表项
             * @param form
             * @returns {jQuery|*}
             */
            this.findInputs = function (form) {
                return $(form).find(that.tags).not('.layui-unselect');
            };
            /**
             * 对象自动转换数组
             * @param {Object} data
             * @returns {Object|Array}
             */
            this.objectAutoArray = function (data) {
                let that = this;
                let initial = -1, increment = true;
                if (Array.isArray(data)) {
                    return data;
                }
                for (let prop in data) {
                    if (!data.hasOwnProperty(prop)) {
                        continue;
                    }
                    if ($.isPlainObject(data[prop])) {
                        data[prop] = that.objectAutoArray(data[prop]);
                    }
                    if (increment && !Number.isNaN(Number(prop)) && prop - initial === 1) {
                        initial = Number(prop);
                    } else {
                        increment = false;
                    }
                }
                return increment ? Object.values(data) : data;
            };
            /**
             * 序列化表单表项值
             * @param {HTMLElement} form
             * @param {string} type
             * @returns {Object|URLSearchParams|String|null}
             */
            this.formSerialize = function (form, type = 'qs') {
                let that = this, $form = $(form);
                if (type === 'qs') {
                    return $form.serialize();
                } else if (type === 'qs2') {
                    let urlSearchParams = new URLSearchParams();
                    let formItem = that.findInputs(form).not('input[type=radio]:not(:checked)');
                    formItem.map(function (index, input) {
                        let $input = $(input);
                        let type = $input.prop('type').toLowerCase();
                        let tag = $input.prop('tagName').toLowerCase();
                        let name = $input.is('[data-as-name]') ? $input.data('as-name') : $input.prop('name');

                        let value = $input.val();
                        if (tag === 'input' && type === 'checkbox') {
                            value = $input.is(':checked') ? $input.val() : 0;
                        }
                        urlSearchParams.set(name, value);
                    });
                    return urlSearchParams;
                } else if (type === 'obj') {
                    let formItem = that.findInputs(form).not('input[type=radio]:not(:checked)');
                    let data = {};
                    formItem.map(function (index, input) {
                        let $input = $(input);
                        let type = $input.prop('type').toLowerCase();
                        let tag = $input.prop('tagName').toLowerCase();
                        let name = $input.is('[data-as-name]') ? $input.data('as-name') : $input.prop('name');

                        let value = $input.val();
                        if (tag === 'input' && type === 'checkbox') {
                            value = $input.is(':checked') ? $input.val() : 0;
                        }
                        if (tag === 'input' && type === 'number' && !Number.isNaN(Number(value))) {
                            value = Number(value);
                        }
                        if ($input.data('type') === 'number' && !Number.isNaN(Number(value))) {
                            value = Number(value);
                        }
                        // 嵌套变量名
                        if (REGEXP_0.test(name)) {
                            let mainName = REGEXP_0.exec(name)[1], arr, currLevel;
                            // 初始首级
                            data[mainName] || (data[mainName] = {});
                            currLevel = data[mainName];

                            // 初始次级
                            while ((arr = REGEXP_1.exec(name)) !== null && REGEXP_1.global) {
                                // 下一次匹配开始的位置 等于 字符串长度即代表 匹配即将完成
                                if (REGEXP_1.lastIndex === arr.input.length) {
                                    // 最后一级执行赋值
                                    currLevel[arr[1]] = value;
                                } else {
                                    currLevel[arr[1]] || (currLevel[arr[1]] = {});
                                    currLevel = currLevel[arr[1]];
                                }
                            }
                        } else {
                            data[name] = value;
                        }
                    });
                    return that.objectAutoArray(data);
                }
                return null;
            };
        };
        vali.fn = vali.prototype;
        vali.fn.pass = function (call) {
            let that = this;
            that.passCall = $.isFunction(call) ? call : helper.noop;
            return that;
        };
        vali.fn.serialize = function(type) {
            return this.formSerialize(form, type || 'obj');
        };
        // 表单验证入口
        vali.fn.check = function (form, content) {
            let that = this, $form = $(form);
            that.gForm = form;
            that.gContent = content;
            // 关闭浏览器的表单自动验证
            $form.attr("novalidate", true);

            that.gFormItem = that.findInputs(form);
            that.gFormItem.each(function () {
                for (let event in that.checkEvent) {
                    if (that.checkEvent.hasOwnProperty(event) && that.checkEvent[event] === true) {
                        $(this).off(event, funcEvent).on(event, funcEvent);
                    }
                    $(this).on('invalid', function () {
                        that.showError(this, this.validationMessage);
                        return false;
                    })
                }
                function funcEvent() {
                    that.checkInput(this);
                }
            });
            // params || {}
            $form.bind("submit", function (event) {
                // 阻止默认事件
                event.preventDefault();
                // 表单校验
                that.gFormItem = that.findInputs(form);
                if (that.isAllpass(that.gFormItem)) {
                    let target = $form.find('button[type=submit], input[type=submit]').first() || event.target;
                    // 提前定位，防止提示被遮挡
                    that.focus(target);
                    that.passCall.call(that, form, target);
                }
                return false;
            });
            $(form).data('validate', this);

            return that;
        };

        return (new vali()).check(form, content);
    };

    return helper;
}));
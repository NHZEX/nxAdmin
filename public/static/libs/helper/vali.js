;(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        throw Error('not support, missing amd dependence')
    }

}(function ($) {
    const STYLE_DANGER = 'layui-form-danger2';
    const MARK_VALIDATE_ERROR = 'validate-error';
    const REGEXP_0 = /^(.*?)\[/;
    const REGEXP_1 = /\[(.*?)]/g;

    let vail = function () {
        let that = this;
        that.html5 = true;
        that.gForm = null;
        that.gContent = null;
        that.gFormItem = null;
        that.passCall = () => {};
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
                vail.focus(this.gContent, first);
            }
            return allpass;
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
         * 枚举全部表项
         * @param form
         * @param {Function} call
         * @param {boolean} isFile
         * @returns {jQuery|*}
         */
        this.echoInputs = function (form, call, isFile) {
            let formItem = that.findInputs(form);
            formItem = formItem.not('input[type=radio]:not(:checked)');
            formItem = formItem.not('input[type=button], input[type=reset], input[type=submit]');
            isFile || (formItem = formItem.not('input[type=file]'));
            formItem.map(function (index, input) {
                let $ele = $(input);
                let type = $ele.prop('type').toLowerCase();
                let tag = $ele.prop('tagName').toLowerCase();
                let name = $ele.is('[data-as-name]') ? $ele.data('as-name') : $ele.prop('name');

                let value = $ele.val();
                if (tag === 'input' && type === 'checkbox') {
                    value = $ele.is(':checked') ? $ele.val() : 0;
                }

                call($ele, type, tag, name, value)
            });
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
                that.echoInputs(form, ($ele, type, tag, name, value) => {
                    urlSearchParams.set(name, value);
                }, true);
                return urlSearchParams;
            } else if (type === 'fd') {
                let param = new FormData();
                that.echoInputs(form, ($ele, type, tag, name, value) => {
                    console.log(name, value);
                    param.append(name, value);
                }, true);
                return param;
            } else if (type === 'obj') {
                let data = {};
                that.echoInputs(form, ($input, type, tag, name, value) => {
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
                }, true);
                return that.objectAutoArray(data);
            }
            return null;
        };
        /**
         * 设置验证通过事件
         * @param {Function} call
         * @returns {vail}
         */
        this.pass = function (call) {
            // noinspection JSUnusedGlobalSymbols
            this.passCall = $.isFunction(call) ? call : () => {};
            return this;
        };
        /**
         * 获取序列化书籍
         * @param type
         * @returns {Object|URLSearchParams|String}
         */
        this.serialize = function(type) {
            return this.formSerialize(this.gForm, type || 'obj');
        };
        // 序列化常量
        this.serialize.tObj = 'obj';
        this.serialize.tFd = 'fd';
        this.serialize.tSq = 'sq';
        this.serialize.tSq2 = 'sq2';
    };
    vail.fn = vail.prototype;
    /**
     * 定位一个元素
     * @param content
     * @param ele
     */
    vail.focus = function (content, ele) {
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
                content.animate({
                    scrollTop: position + (content.innerHeight() / 2)
                }, 500);
            }
            if (position > content.height() - 100) {
                content.animate({
                    scrollTop: position - (content.innerHeight() / 2)
                }, 500);
            }
        } else {
            $ele.focus();
        }
    };
    /**
     * 表单验证器入口
     * @param form
     * @param content
     * @returns {vail}
     */
    vail.check = function (form, content) {
        let that = new vail(), $form = $(form);
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
        let submit = function(target) {
            // 表单校验
            that.gFormItem = that.findInputs(form);
            if (that.isAllpass(that.gFormItem)) {
                // 提前定位，防止提示被遮挡
                vail.focus(that.gContent, target);
                that.passCall.call(that, form, target);
            }
        };
        // 一级截取
        $form.find('[type=submit]').bind('click', function (event) {
            // 阻止默认事件
            event.preventDefault();
            // 表单校验
            submit(event.target);
            return false;
        });
        // 二级截取
        $form.bind('submit', function (event) {
            // 阻止默认事件
            event.preventDefault();
            // 表单校验
            submit($form.find('button[type=submit], input[type=submit]').first() || event.target);
            return false;
        });
        $(form).data('validate', this);

        return that;
    };

    return vail;
}));
;(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        throw Error('not support, missing amd dependence')
    }
}(function ($) {
    const STYLE_DANGER = 'layui-form-danger2';
    const NAMESPACES = 'verify-nx-zero';
    const MARK_VALIDATE_ERROR = 'validate-error';
    const REGEXP_0 = /^(.*?)\[/;
    const REGEXP_1 = /\[(.*?)]/g;

    // 序列化常量
    // noinspection JSUnusedLocalSymbols
    const SERIALIZE_OBJ = 'obj';
    // noinspection JSUnusedLocalSymbols
    const SERIALIZE_FD = 'fd';
    // noinspection JSUnusedLocalSymbols
    const SERIALIZE_SQ = 'sq';
    // noinspection JSUnusedLocalSymbols
    const SERIALIZE_SQ2 = 'sq2';

    class Verify {
        /**
         * 构造函数
         * @param {jQuery|HTMLFormElement} [form=null]
         * @param {jQuery|HTMLElement} content
         */
        constructor(form, content)
        {
            this.html5 = true;
            /** @type {String} 监听元素 */
            this.tags = 'input,textarea,select';
            /** @type {Object} 事件检测设定 */
            this._checkEvent = {change: true, blur: false, focus: true, keyup: true};

            /** @type {Function} */
            this._gPassCall = () => {};
            /** @type {jQuery|jQuery.fn.init} */
            this._gFormItem = null;
            /** @type {jQuery|HTMLFormElement} */
            this._gForm = form;
            /** @type {jQuery|HTMLElement} */
            this._gContent = content || window.document.body;

            let $form = $(form);

            // 禁用表单自动验证
            $form.attr("novalidate", true);

            this._check(this._gForm, this._gContent);
        }

        /**
         * 设置验证通过事件
         * @param {Function} call
         * @returns {Verify}
         */
        pass (call) {
            this._gPassCall = $.isFunction(call) ? call : () => {};
            return this;
        };

        /**
         * 表单验证器入口
         * @param {HTMLFormElement|jQuery} form
         * @param {HTMLElement|jQuery} content
         * @returns {Verify}
         */
        _check (form, content) {
            let $form = $(form);
            let namespaces = `.${NAMESPACES}`;
            this._gFormItem = this.findInputs(form);
            $(this._gFormItem).each((i, input) => {
                let funcEvent = () => {
                    this.checkInput(input);
                };
                $(input).off(namespaces);
                for (let event in this._checkEvent) {
                    if (this._checkEvent.hasOwnProperty(event) && this._checkEvent[event] === true) {
                        $(input).on(event + namespaces, funcEvent);
                    }
                }
            });
            let submit = (target) => {
                // 表单校验
                this._gFormItem = this.findInputs(form);
                if (this._isAllpass(this._gFormItem)) {
                    // 提前定位，防止提示被遮挡
                    Verify.focus(content, target);
                    this._gPassCall.call(this, form, target);
                }
            };
            // 一级截取
            $form.find('[type=submit]').off(namespaces).bind('click' + namespaces, function (event) {
                // 阻止默认事件
                event.preventDefault();
                // 表单校验
                submit(event.target);
                return false;
            });
            // 二级截取
            $form.off(namespaces).bind('submit' + namespaces, function (event) {
                // 阻止默认事件
                event.preventDefault();
                // 表单校验
                submit($form.find('button[type=submit], input[type=submit]').first() || event.target);
                return false;
            });
            $(form).data('validate', this);
            return this;
        };

        /**
         * 寻找全部表项
         * @param {jQuery|HTMLFormElement} form
         * @returns {jQuery}
         */
        findInputs (form) {
            // 兼容 layui、form-selects
            return $(form).find(this.tags).not('.layui-unselect').not('.xm-hide-input, .xm-input').not('.eleTree-hideen');
        }

        /**
         * 枚举全部表项
         * @param form
         * @param {Function} call
         * @param {boolean} isFile
         */
        echoInputs (form, call, isFile) {
            let formItem = this.findInputs(form);
            formItem = formItem.not('input[type=radio]:not(:checked)');
            formItem = formItem.not('input[type=button], input[type=reset], input[type=submit]');
            isFile || (formItem = formItem.not('input[type=file]'));
            formItem.map(function (index, input) {
                let $ele = $(input);
                let type = $ele.prop('type').toLowerCase();
                let tag = $ele.prop('tagName').toLowerCase();
                let name = $ele.is('[data-as-name]') ? $ele.data('as-name') : $ele.prop('name');

                if (name === ''){
                    return ;
                }

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
        _objectAutoArray (data) {
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
                    data[prop] = that._objectAutoArray(data[prop]);
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
         * @param {HTMLFormElement} form
         * @param {String} type
         * @returns {Object|URLSearchParams|FormData|String|null}
         */
        formSerialize (form, type) {
            let $form = $(form);
            if (type === SERIALIZE_SQ) {
                return $form.serialize();
            } else if (type === SERIALIZE_SQ2) {
                let urlSearchParams = new URLSearchParams();
                this.echoInputs(form, ($ele, type, tag, name, value) => {
                    urlSearchParams.set(name, value);
                }, true);
                return urlSearchParams;
            } else if (type === SERIALIZE_FD) {
                let param = new FormData();
                this.echoInputs(form, ($ele, type, tag, name, value) => {
                    if (type === 'file') {
                        let files = $ele.get(0).files;
                        //多文件上传
                        if (name.indexOf("[]") === -1 && files.length > 1){
                            name = name + "[]";
                        }

                        for (let i = 0; i < files.length; i++) {
                            param.append(name, files[i]);
                        }
                    } else {
                        param.append(name, value);
                    }
                }, true);
                return param;
            } else if (type === SERIALIZE_OBJ) {
                let data = {};
                this.echoInputs(form, ($input, type, tag, name, value) => {
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
                return this._objectAutoArray(data);
            }
            return null;
        };

        // noinspection JSUnusedGlobalSymbols
        /**
         * 获取序列化字符串
         * @returns {String}
         */
        serializeString () {
            return this.formSerialize(this._gForm, SERIALIZE_SQ);
        };

        // noinspection JSUnusedGlobalSymbols
        /**
         * 获取序列化字符串
         * @returns {Object}
         */
        serializeObject () {
            return this.formSerialize(this._gForm, SERIALIZE_OBJ);
        };

        // noinspection JSUnusedGlobalSymbols
        /**
         * 获取序列化字符串
         * @returns {URLSearchParams}
         */
        serializeURLSearchParams () {
            return this.formSerialize(this._gForm, SERIALIZE_SQ2);
        };

        // noinspection JSUnusedGlobalSymbols
        /**
         * 获取序列化字符串
         * @returns {FormData}
         */
        serializeFormData () {
            return this.formSerialize(this._gForm, SERIALIZE_FD);
        };

        /**
         * 去除字符串两头的空格
         * @param {string} str
         * @returns {string}
         */
        static trim (str) {
            return str.replace(/(^\s*)|(\s*$)/g, '');
        };

        /**
         * 检测元素是否忽略
         * @param {jQuery|HTMLElement} ele
         * @returns {boolean}
         * @private
         */
        static _isIgnore (ele) {
            let $ele = $(ele);
            // 兼容layui组件渲染
            if($ele.is('select, [type=checkbox], [type=radio]') && !$ele.is('[lay-ignore]')) {
                return !$ele.next().is('.layui-unselect, .layui-form-select, .layui-form-radio, .layui-form-checkbox');
            }
            return $ele.is('[disabled]:visible');
        };

        /**
         * 判断表单元素是否为空
         * @param {HTMLElement} ele
         * @param value
         * @returns {boolean}
         * @private
         */
        static _isEmpty (ele, value) {
            let trimValue = Verify.trim(ele.value);
            value = value || ele.getAttribute('placeholder');
            return (trimValue === "" || trimValue === value);
        };

        /**
         * 正则验证表单元素
         * @param {HTMLElement} ele
         * @param {string} [regex=null]
         * @param {string} [params=null]
         * @returns {boolean}
         * @private
         */
        static _isRegexPass (ele, regex, params) {
            let inputValue = ele.value, dealValue = Verify.trim(inputValue);
            regex = regex || ele.getAttribute('pattern');
            if (dealValue === "" || !regex) {
                return true;
            }
            if (dealValue !== inputValue) {
                (ele.tagName.toLowerCase() !== "textarea") ? (ele.value = dealValue) : (ele.innerHTML = dealValue);
            }
            return new RegExp(regex, params || 'i').test(dealValue);
        };

        /**
         * 错误消息显示
         * @param {HTMLElement|jQuery} ele
         * @param {string} content
         * @returns {boolean}
         * @private
         */
        static _showError (ele, content) {
            let $ele = $(ele);
            let $targetEle;
            // 适配layui元素
            if (!$ele.is('[lay-ignore]') && $ele.is('select, [type=checkbox], [type=radio]')){
                $targetEle = $ele.next();
            } else if ($ele.is('select[xm-select]')){
                $targetEle = $ele.next();
            } else {
                $targetEle = $ele;
            }
            // 显示错误信息
            $ele.addClass(STYLE_DANGER);
            $ele.addClass(MARK_VALIDATE_ERROR);
            Verify._insertError($targetEle.get(0)).addClass('fadeInRight').css({width: 'auto'}).html(content);
            return false;
        };

        /**
         * 错误消息消除
         * @param {HTMLElement|jQuery} ele
         * @private
         */
        static _hideError (ele) {
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
            Verify._insertError($targetEle.get(0)).removeClass('fadeInRight').css({width: '30px'}).html('');
        };

        /**
         * 错误消息标签插入
         * @param {HTMLElement|jQuery} ele
         * @returns {*|jQuery|jQuery.fn.init|HTMLElement}
         * @private
         */
        static _insertError (ele) {
            let $html, $ele = $(ele);
            if (!$ele.data('insert')) {
                $html = $('<span class="animated" style="-webkit-animation-duration:.2s;animation-duration:.2s;padding-right:20px;color:#a94442;position:absolute;right:0;font-size:12px;z-index:2;display:block;width:34px;text-align:center;pointer-events:none"></span>');
                $html.insertAfter(ele);
                $ele.data('insert', true);
            } else {
                $html = $ele.next('.animated');
            }
            let parentPos = $(ele).parent().offset().left + $(ele).parent().innerWidth();
            let elePos = $(ele).offset().left + $(ele).innerWidth();
            let rightOffset = parentPos - elePos;

            // fixes form-selects 兼容性
            let fixes = {
                border: 'initial',
                backgroundColor: 'initial',
            };
            // 计算样式
            $html.css({
                top: $(ele).position().top + 'px',
                paddingBottom: $(ele).css('paddingBottom'),
                paddingRight: rightOffset + 'px',
                lineHeight: $(ele).css('height'),
                ...fixes
            });

            return $html;
        };

        /**
         * 验证标志
         * @param {HTMLElement} input
         * @returns {boolean}
         * @private
         */
        static _remind (input) {
            Verify._showError(input, input.getAttribute('title') || '');
            return false;
        };

        /**
         * 检测全部表单元素
         * @param elements
         * @returns {boolean}
         * @private
         */
        _isAllpass (elements) {
            if (!elements) {
                return true;
            }
            let allpass = true, first = null;
            if (elements.size && elements.size() === 1 && elements.get(0).tagName.toLowerCase() === "form") {
                elements = $(elements).find(this.tags);
            } else if (elements.tagName && elements.tagName.toLowerCase() === "form") {
                elements = $(elements).find(this.tags);
            }
            elements.each((i, el) => {
                if (this.checkInput(el) === false) {
                    first instanceof HTMLElement || (first = el);
                    allpass = false;
                }
            });
            if(first instanceof HTMLElement) {
                Verify.focus(this._gContent, first);
            }
            return allpass;
        };

        /**
         * 检测表单单元
         * @param {jQuery|HTMLInputElement} input
         * @returns {boolean}
         */
        checkInput (input) {
            let that = this;
            let $input = $(input);
            let type = $input.prop('type').toLowerCase();
            let tag = $input.prop('tagName').toLowerCase(), isRequired = $input.is('[required]');
            let allpass = true;
            if ($input.is('[data-auto-none]')
                || type === 'submit' || type === 'reset'
                || (type === 'file' && !this.html5)
                || type === 'image'
                || Verify._isIgnore(input)
            ) {
                return true;
            }

            if (this.html5) {
                if(input.validity instanceof ValidityState && !input.validity.valid) {
                    if (type === 'radio') {
                        let radioGroup = this._gFormItem.filter('[type=radio][name=' + input.name + ']');
                        if(!radioGroup.is(`.${MARK_VALIDATE_ERROR}`)) {
                            Verify._showError(input, input.validationMessage);
                        }
                    } else {
                        Verify._showError(input, input.validationMessage);
                    }
                    allpass = false;
                }
            } else {
                if (type === "radio" && !$input.is(':checked')) {
                    let radioGroup = this._gFormItem.filter('[type=radio][name=' + input.name + ']');
                    if(radioGroup.is('[required]')) {
                        if(!radioGroup.is(`.${MARK_VALIDATE_ERROR}`)) {
                            input.title = '请从这些选项中选择一个。';
                            Verify._remind(input);
                        }
                        allpass = false;
                    } else {
                        allpass = true;
                    }
                } else if (type === "checkbox" && isRequired && !$input.is(':checked')) {
                    input.title = '请勾选该选项。';
                    Verify._remind(input);
                    allpass = false;
                } else if (tag === "select" && isRequired && !$input.val()) {
                    input.title = '请选择一个选项。';
                    Verify._remind(input);
                    allpass = false;
                } else if (isRequired && Verify._isEmpty(input)) {
                    input.title = '请填写此字段。';
                    Verify._remind(input);
                    allpass = false;
                } else if(!Verify._isRegexPass(input)) {
                    input.title = '请与所请求的格式保持一致';
                    Verify._remind(input);
                    allpass = false;
                }
            }
            if (allpass) {
                if (type === 'radio') {
                    that.gFormItem
                        .filter(`[type=radio][name=${input.name}].${MARK_VALIDATE_ERROR}`)
                        .each(function (index, input) {
                            that.hideError(input);
                        })

                } else {
                    Verify._hideError(input);
                }
            }
            return allpass;
        };

        /**
         * 定位到一个元素
         * @param {jQuery|HTMLElement} content
         * @param {jQuery|HTMLElement} ele
         */
        static focus (content, ele) {
            let $ele = $(ele);
            let $content = $(content);
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
                    $content.animate({
                        scrollTop: position + ($content.innerHeight() / 2)
                    }, 500);
                }
                if (position > $content.height() - 100) {
                    $content.animate({
                        scrollTop: position - ($content.innerHeight() / 2)
                    }, 500);
                }
            } else {
                $ele.focus();
            }
        };
    }

    return Verify;
}));
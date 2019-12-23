
//从script传递的内容
let scripts = document.getElementsByTagName("script");
let scriptArgs = scripts[ scripts.length - 1 ].src.split("?")[1].split("&");
let args = {};
for (let index in scriptArgs) {
    let tmp = scriptArgs[index].split("=");
    args[tmp[0]] = tmp[1];
}

window.isDebug = args['debug'] === "1";

let paths = {
    'jquery': 'libs/jquery/jquery-2.2.4'
    , 'underscore': 'libs/underscore/underscore.min'
    , 'axios': 'libs/axios/axios'
    , 'artTemplate': 'libs/art-template/template-web.min'
    , 'js-cookie': 'libs/js-cookie/js.cookie-2.2.0.min'
    , 'crc32': 'libs/crc32/crc32.min'
    , 'lodash': 'libs/lodash/lodash.min'
    , 'require-css': '/static/require/css.min'
    , 'qs': 'libs/qs/qs'

    , 'bootstrap': 'libs/bootstrap3/js/bootstrap.min'
    , 'bootstrap.typeahead': 'libs/bootstrap3/js/bootstrap3-typeahead.min'

    , 'helper': 'libs/helper/helper'
    , 'vali': 'libs/helper/vali'
    , 'verify': 'libs/helper/verify'
    , 'table-tpl': 'libs/table-tpl/table-tpl'
    , 'communication': 'libs/window-communication/communication'

    , 'formSelects': 'libs/form-selects/formSelects-v4.min'
    , 'formSelects-css': 'libs/form-selects/formSelects-v4'

    , 'jsoneditor': 'libs/jsoneditor/jsoneditor.min'
    , 'jsoneditor-css': 'libs/jsoneditor/jsoneditor.min'
    , 'jsoneditor-fixlayui-css': 'libs/jsoneditor/fixlayui'

    , 'nprogress': 'libs/nprogress/nprogress'
    , 'nprogress-css': 'libs/nprogress/nprogress'

    , 'uploadImage': 'libs/upload/uploadImage'
    , 'uploadImage-css': 'libs/upload/uploadImage'

    , 'layui': 'libs/layui/layui'
    , 'layui-css': 'libs/layui/css/layui'
    , 'layer': 'libs/layui/lay/modules/layer'
    , 'layer-css': 'libs/layui/css/modules/layer/default/layer'
    , 'laydate': 'libs/layui/lay/modules/laydate'
    , 'layform': 'libs/layui/lay/modules/form'
    , 'layupload': 'libs/layui/lay/modules/upload'

    , 'layelement': 'libs/layui/lay/modules/element'
    , 'kitnavbar': 'libs/kit/js/navbar'
    , 'kitapp': 'libs/kit/js/app'
    , 'kittab': 'libs/kit/js/tab'
    , 'kitutils': 'libs/kit/js/utils'
    , 'kitmessage': 'libs/kit/js/message'

    , 'iview': 'libs/iview/iview'
    , 'iview-css': 'libs/iview/styles/iview'
    , 'vue': 'libs/vue/vue'

    , 'moment': 'libs/moment/2.24.0/moment'
    , 'form-create': 'libs/form-create/form-create'

    , 'download': 'libs/zx/download'
};

//非调试模式需要替换成min.js
let product_paths = {
    'jquery': 'libs/jquery/jquery-2.2.4.min'
    , 'axios': 'libs/axios/axios.min'

    , 'iview': 'libs/iview/iview.min'
    , 'vue': 'libs/vue/vue.min'
    , 'form-create': 'libs/form-create/form-create.min'
    , 'moment': 'libs/moment/2.24.0/moment.min'
};

//如果为非调试模式
if (!window.isDebug) {
    for (let key in product_paths) {
        paths[key] = product_paths[key];
    }
}

// noinspection JSFileReferences
require.config({
    baseUrl: '//'+window.location.host + '/static'
    , waitSeconds: 30
    , paths: paths
    , shim: {
        'jquery': {
            exports: '$'
        }
        , 'bootstrap': {
            deps: ['jquery']
        }
        , 'bootstrap.typeahead': {
            deps: ['bootstrap']
        }
        , 'layui': {
            deps: ['jquery', 'css!layui-css']
            , init: function () {
                let layui_dir = requirejs.s.contexts._.config.baseUrl;

                return layui.config({
                    dir: layui_dir + 'libs/layui/'
                    , base: layui_dir + 'libs/layui_exts/'
                    , kitBase: layui_dir + 'libs/kit/'
                }).extend({
                    treeGrid: 'treeGrid/treeGrid'
                    , eleTree: 'eleTree/eleTree'
                    , dtree: 'dtree/dtree'
                });
            }
        }
        , 'layelement': {
            deps: ['layui']
        }
        , 'layer': {
            deps: ['layui']
            , init: function () {
                return layui.layer;
            }
        }
        , 'laydate': {
            deps: ['layui']
            , init: function () {
                return layui.laydate;
            }
        }
        , 'layform': {
            deps: ['layui']
        }
        , 'layupload': {
            deps: ['layui']
        }
        , 'formSelects': {  // , 'formSelects': ['css!formSelects-css']
            'deps': ['jquery', 'css!formSelects-css']
        }
        , 'jsoneditor': {
            'deps': ['css!jsoneditor-css', 'css!jsoneditor-fixlayui-css']
        }
        , 'nprogress': {
            'deps': ['css!nprogress-css']
        }
        , 'uploadImage': {
            'deps': ['css!uploadImage-css']
        }
        , 'iview': {
            'deps': ['vue', 'css!iview-css']
        }
        , 'form-create': {
            'deps': ['iview']
        }
    }
    , map: {
        '*': {
            'css': 'require-css'
        }
    },

});

const regex_csrf = /[\/?]?(csrf|csrf_update)[\/=](\w+)/;
const isVue = '1' === args['vue']
    || getParameterByName('isVue')
    || getParameterByName('isvue')
    || -1 !== window.location.href.indexOf('/isVue/1')
    || -1 !== window.location.href.indexOf('/isvue/1');

if (window.isDebug) {
    console.info('isVue', isVue);
}

// axios global handle
require(['axios', 'qs'], function (axios, Qs) {
    // 全局默认设置
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.paramsSerializer = function (params) {
        return Qs.stringify(params, {arrayFormat: 'brackets'})
    };

    /**
     * 动态获取CSRF令牌
     * @param default_url
     * @returns {*}
     */
    function isUserInDatabase(default_url = null) {
        let headers = {};
        default_url || (default_url = {url: '/util/obtainCsrfToken', pkid: 0});
        return axios.get(default_url.url, {headers: headers, params: {pkid: default_url.pkid}});
    }

    // 请求拦截器
    axios.interceptors.request.use(async function (config) {
        // 处理CSRF令牌
        if (config.method === 'put' || config.method === 'post' || config.method === 'delete') {
            let csrf_token;
            // noinspection JSUnresolvedVariable
            let csrf_flag = config.csrf;
            if (!csrf_flag && (csrf_token = regex_csrf.exec(config.url))) {
                config.headers['XSRF-Token'] = csrf_token[2]
                    + ('csrf_update' === csrf_token[1] ? '.update' : '.default');
            } else if (csrf_flag) {
                let result = await isUserInDatabase(csrf_flag);
                config.headers['XSRF-Token'] = result.data.data;
            } else {
                let result = await isUserInDatabase(csrf_flag);
                config.headers['XSRF-Token'] = result.data.data;
            }
        }
        return config;
    }, function (error) {
        // Do something with request error
        return Promise.reject(error);
    });
});

if (isVue) {
    require(['axios', 'lodash'], function (axios, _) {
        // 响应拦截器
        axios.interceptors.response.use(function (response) {
            // Do something with response data
            if(response.data && response.data.hasOwnProperty('code') && 0 !== response.data.code) {
                let message = '['+response.data.code+'] '+response.data.msg;
                if(window.vue) {
                    window.vue.$Notice.error({
                        title: '操作请求错误',
                        desc: message,
                        duration: 6,
                    });
                } else {
                    console.warn(message)
                }
                throw message;
            }

            return response;
        }, function (error) {
            let call = () => {}, message;

            // Do something with response error
            message = '[HTTP-'+error.response.status+'] '+error.response.statusText;

            if (error.config['close_error_handle']) {
                return Promise.reject(error);
            }

            if(401 === error.response.status) {
                message = '[HTTP-'+error.response.status+'] '+(error.response.data ? error.response.data : error.response.statusText);
                call = function () {
                    if(error.response.headers['soft-location']) {
                        window.location.href = error.response.headers['soft-location'];
                    }
                }
            }
            if(403 === error.response.status) {
                message = '[HTTP-'+error.response.status+'] 你没有权限访问此内容';
            }

            if (window.vue) {
                window.vue.$Notice.error({
                    title: '操作请求失败',
                    desc: message,
                    duration: 3,
                    onClose: call,
                });
            } else {
                console.warn('请求发生错误', message);
            }

            return Promise.reject(error);
        });
    });
} else {
// jqueryAjaxHandle
    require(['jquery', 'layer'], function ($, layer) {

        $.ajaxSetup(true);

        $(document).ajaxSend(function (event, jqxhr, ajaxOptions) {
            // 处理CSRF令牌
            if(ajaxOptions.type.toLowerCase() === 'put' || ajaxOptions.type.toLowerCase() === 'post') {
                let csrf_token;
                if(!jqxhr.getResponseHeader('XSRF-Token') && (csrf_token = regex_csrf.exec(ajaxOptions.url))) {
                    jqxhr.setRequestHeader(
                        'XSRF-Token',
                        csrf_token[2] + ('csrf_update' === csrf_token[1] ? '.update' : '.default')
                    );
                } else {
                    console.info('jquery.ajax不支持自动拓展令牌', ajaxOptions.url)
                }
            }
        });

        // noinspection JSUnusedLocalSymbols
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            layer.closeAll('loading');
            if(401 === jqxhr.status) {
                layer.msg('['+jqxhr.status+'] '+jqxhr.responseText, {icon: 5, time: 3000, zIndex: 33554431}, function () {
                    let location = jqxhr.getResponseHeader('Soft-Location');
                    if(location) {
                        window.location.href = location;
                    }
                })
            } else if(403 === jqxhr.status) {
                layer.msg('['+jqxhr.status+'] 你没有权限访问此内容', {icon: 5, time: 3000, zIndex: 33554431})
            } else {
                !settings.silent && layer.msg('['+jqxhr.status+'] '+jqxhr.statusText, {icon: 5, time: 3000, zIndex: 33554431});
            }
            return false;
        });

        $(document).ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions) {
        });
    });

// axiosHandle
    require(['axios', 'layer'], function (axios, layer) {
        // 响应拦截器
        axios.interceptors.response.use(function (response) {
            // Do something with response data
            if(response.data.hasOwnProperty('code') && 0 !== response.data.code) {
                if(layer) {
                    layer.closeAll('loading');
                    let message = '['+response.data.code+'] '+response.data.msg;
                    if(response.config.layer_elem) {
                        layer.tips(message, $(response.config.layer_elem), {
                            tips: [2, '#FF5722']
                            , zIndex: 33554431
                        })
                    } else {
                        layer.msg(message, {icon: 5, time: 3000, zIndex: 33554431})
                    }

                } else {
                    alert(response.data.msg)
                }
            }
            return response;
        }, function (error) {
            // Do something with response error
            let message = '[HTTP-'+error.response.status+'] '+error.response.statusText;
            if(layer) {
                layer.closeAll('loading')
            }
            let call = function () {};
            if(401 === error.response.status) {
                message = '[HTTP-'+error.response.status+'] '+(error.response.data ? error.response.data : error.response.statusText);
                call = function () {
                    if(error.response.headers['soft-location']) {
                        window.location.href = error.response.headers['soft-location'];
                    }
                }
            }
            if(403 === error.response.status) {
                message = '[HTTP-'+error.response.status+'] 你没有权限访问此内容';
            }

            if(error.config.layer_elem) {
                layer.tips(message, $(error.config.layer_elem), {
                    tips: [2, '#CC0000']
                    , zIndex: 33554431
                    , end: call
                })
            } else if(!error.config.silent) {
                layer.msg(message, {icon: 5, time: 3000, zIndex: 33554431}, call)
            }

            return Promise.reject(error);
        });
    });
}

function getParameterByName (name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

// 已经加载的组件
window.loadVueComponents = {};

function loadVueComponent2(axios, uri) {
    const compile = /([\S\s]+?)<script>([\S\s]+?)<\/script>/gu;
    const vueCompile = /export default (\{[\S\s]+\});?/gu;

    if (window.isDebug) {
        console.info('load-vue-component2', uri);
    }

    function isEmptyObject(obj) {
        for (let prop in obj) {
            if(obj.hasOwnProperty(prop)) {
                return false;
            }
        }
        return false;
    }

    function loadComponent(axios, url) {
        return (resolve, reject) => {
            let done = (components, template) => {
                components.template = template;
                if (window.isDebug) {
                    console.info('lazy-vue-component', url, components);
                }
                if (components.hasOwnProperty('vueComponent') && !isEmptyObject(components.vueComponent)) {
                    components._components = components.vueComponent;
                    delete components.vueComponent;
                }
                if (components.hasOwnProperty('_components') && !isEmptyObject(components._components)) {
                    if (!components.hasOwnProperty('components')) {
                        components.components = {};
                    }
                    for (let tag in components._components) {
                        if (!components._components.hasOwnProperty(tag)) {
                            continue;
                        }
                        components.components[tag] = loadVueComponent2(axios, components._components[tag]);
                    }
                    delete components._components;
                }
                resolve(components);
            };
            let vueParsing = (code) => {
                let result = vueCompile.exec(code);
                let vueData = eval(`(function () {return ${result[1]};})()`);

                if (vueData.hasOwnProperty('_require') && !isEmptyObject(vueData._require)) {
                    let _require = vueData._require;
                    let libs = [];
                    let args = [];
                    for (let lib in _require) {
                        if (_require.hasOwnProperty(lib)) {
                            libs.push(`'${lib}'`);
                            args.push(_require[lib]);
                        }
                    }
                    let _code = `new Promise((resolve, reject) => {
                        require([${libs.join(', ')}], (${args.join(', ')}) => {
                          resolve(${result[1]});
                        });
                      });`;
                    vueData = eval(_code);
                }
                return vueData;
            };
            axios.get(url)
                .then((res) => {
                    let page = res.data;
                    let result = compile.exec(page);
                    let component;
                    if (url.endsWith('.vue')) {
                        component = vueParsing(result[2]);
                    } else {
                        // Function(`"use strict"; return (${result[2]})`)();
                        component = eval(`"use strict"; ${result[2]}`);
                    }

                    if (component instanceof Promise) {
                        component.then((value) => {
                            done(value, result[1]);
                        }).catch((err) => {
                            throw err;
                        })
                    } else {
                        done(component, result[1]);
                    }
                })
                .catch((error) => {
                    console.info('load-vue-component-error', url);
                    console.error(error);
                    reject(error)
                });
        };
    }

    return loadComponent(axios, uri);
}

/**
 * 异步加载Vue组件
 * @param vue
 * @param axios
 * @param list
 * @param done
 */
function loadMultiVueComponent (vue, axios, list, done) {
    if (window.isDebug) {
        console.info('load-multi-vue-component', list);
    }
    function isEmptyObject(obj) {
        for (let prop in obj) {
            if(obj.hasOwnProperty(prop)) {
                return false;
            }
        }
        return false;
    }
    let lmvc = function load(vue, axios, done) {
        this.loadCount = 0;
        this.vue = vue;
        this.axios = axios;
        this.done = done;
    };
    lmvc.prototype.start = function (list) {
        for (let tag in list) {
            if (!list.hasOwnProperty(tag)) {
                return;
            }
            let param = list[tag];
            this.loadVueComponent(tag, param);
        }
        if (this.done) {
            let t = setInterval(() => {
                if (0 === this.loadCount) {
                    clearInterval(t);
                    this.done();
                }
            }, 50);
        }
    };
    lmvc.prototype.loadVueComponent = function (tag, url) {
        if (window.loadVueComponents.hasOwnProperty(tag)) {
            if (window.loadVueComponents[tag] !== url) {
                throw 'components [' + tag + '] existed, duplicate definition: ' + url;
            }
            return;
        } else {
            window.loadVueComponents[tag] = url;
        }
        this.loadCount += 1;
        const compile = /([\S\s]+?)<script>([\S\s]+?)<\/script>/gu;
        const vueCompile = /export default (\{[\S\s]+\});?/gu;
        this.vue.component(tag, (resolve, reject) => {
            let done = (components, template) => {
                components.template = template;
                if (window.isDebug) {
                    console.info('load-vue-component', tag, components);
                }
                if (components.hasOwnProperty('vueComponent') && !isEmptyObject(components.vueComponent)) {
                    components._components = components.vueComponent;
                    delete components.vueComponent;
                }
                if (components.hasOwnProperty('_components') && !isEmptyObject(components._components)) {
                    loadMultiVueComponent(vue, axios, components._components, () => {});
                    delete components._components;
                }
                this.loadCount--;
                resolve(components);
            };
            let vueParsing = (code) => {
                let result = vueCompile.exec(code);
                let vueData = eval(`(function () {return ${result[1]};})()`);

                if (vueData.hasOwnProperty('_require') && !isEmptyObject(vueData._require)) {
                    let _require = vueData._require;
                    let libs = [];
                    let args = [];
                    for (let lib in _require) {
                        if (_require.hasOwnProperty(lib)) {
                            libs.push(`'${lib}'`);
                            args.push(_require[lib]);
                        }
                    }
                    let _code = `new Promise((resolve, reject) => {
                        require([${libs.join(', ')}], (${args.join(', ')}) => {
                          resolve(${result[1]});
                        });
                      });`;
                    vueData = eval(_code);
                }
                return vueData;
            };
            this.axios.get(url)
                .then((res) => {
                    let page = res.data;
                    let result = compile.exec(page);
                    let component;
                    if (url.endsWith('.vue')) {
                        component = vueParsing(result[2]);
                    } else {
                        // Function(`"use strict"; return (${result[2]})`)();
                        component = eval(`"use strict"; ${result[2]}`);
                    }

                    if (component instanceof Promise) {
                        component.then((value) => {
                            done(value, result[1]);
                        }).catch((err) => {
                            throw err;
                        })
                    } else {
                        done(component, result[1]);
                    }
                })
                .catch((error) => {
                    console.info('load-vue-component-error', tag);
                    console.error(error);
                    reject(error)
                });
        });
    };
    (new lmvc(vue, axios, done)).start(list);
}

/**
 * 监控窗口高度变化
 * @param {Function} actualResizeHandler
 * @param dom
 * @param {String} prop
 * @return {Number}
 */
function monitorWindowsHeightResize (actualResizeHandler, dom = window, prop = 'innerHeight') {
    let lastHeight = dom[prop];

    return setInterval(() => {
        if (lastHeight !== dom[prop]) {
            lastHeight = dom[prop];
            actualResizeHandler(lastHeight)
        }
    }, 33);
}
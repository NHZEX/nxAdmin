// noinspection JSFileReferences
require.config({
    baseUrl: '//'+window.location.host + '/static'
    , paths: {
        'jquery': 'libs/jquery/jquery-2.2.4.min'
        , 'underscore': 'libs/underscore/underscore-min'
        , 'axios': 'libs/axios/axios.min'
        , 'artTemplate': 'libs/art-template/template-web'
        , 'js-cookie': 'libs/js-cookie/js.cookie-2.2.0.min'
        , 'crc32': 'libs/crc32/crc32'
        , 'lodash': 'libs/lodash/lodash.min'

        , 'helper': 'libs/helper/helper'
        , 'vali': 'libs/helper/vali'
        , 'table-tpl': 'libs/table-tpl/table-tpl'
        , 'communication': 'libs/window-communication/communication'

        , 'bootstrap': 'libs/bootstrap3/js/bootstrap.min'
        , 'bootstrap.typeahead': 'libs/bootstrap3/js/bootstrap3-typeahead.min'

        , 'formSelects': 'libs/form-selects/formSelects-v4.min'
        , 'formSelects-css': 'libs/form-selects/formSelects-v4'

        , 'jsoneditor': 'libs/jsoneditor/jsoneditor'
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
    }
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
                // noinspection UnnecessaryLocalVariableJS
                let result = layui.config({
                    dir: layui_dir + 'libs/layui/'
                    , base: layui_dir + 'libs/layui/layui_exts/'
                    , kitBase: layui_dir + 'libs/kit/'
                }).extend({
                    requireJs: 'requireJs/requireJs'
                    , treeGrid: 'treeGrid/treeGrid'
                    , eleTree: 'eleTree/eleTree'
                    , dtree: 'dtree/dtree'
                });
                // console.log(result.cache);
                return result;
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
    }
    , map: {
        '*': {
            'css': 'require/css.min'
        }
    },

});

const regex_csrf = /\/(csrf|csrf_update)\/(\w+)/;

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
    // 全局默认设置
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

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
        if(config.method === 'put' || config.method === 'post' || config.method === 'delete') {
            let csrf_token;
            if(!config.csrf && (csrf_token = regex_csrf.exec(config.url))) {
                config.headers['XSRF-Token'] = csrf_token[2]
                    + ('csrf_update' === csrf_token[1] ? '.update' : '.default');
            } else if(config.csrf) {
                let result = await isUserInDatabase(config.csrf);
                config.headers['XSRF-Token'] = result.data.data;
            } else {
                let result = await isUserInDatabase(config.csrf);
                config.headers['XSRF-Token'] = result.data.data;
            }
        }
        return config;
    }, function (error) {
        // Do something with request error
        return Promise.reject(error);
    });

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
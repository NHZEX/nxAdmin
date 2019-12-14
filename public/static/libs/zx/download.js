(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // 支持 AMD
        define(['qs'], function (Qs) {
            return factory(Qs)
        });
    } else {
        window.Download = factory();
    }
})(function (Qs) {
    let communication = {};

    function Class() {
    }

    Class.prototype.url = function (url, target) {
        // target: _self 当前页面, 新的页面 _blank
        target = (typeof target !== 'undefined') ? target : '_self';
        window.open(url, target)
    };
    Class.prototype.blob = function (content, filename) {
        // 创建隐藏的可下载链接
        let eleLink = document.createElement('a');
        eleLink.download = filename;
        eleLink.style.display = 'none';
        // 字符内容转变成blob地址
        let blob = new Blob([content]);
        eleLink.href = URL.createObjectURL(blob);
        // 触发点击
        document.body.appendChild(eleLink);
        eleLink.click();
        // 然后移除
        document.body.removeChild(eleLink);
    };
    Class.prototype.args = function (url, param, method, target) {
        method = (typeof method !== 'undefined') ? method : 'get';
        // target: _self 当前页面, 新的页面 _blank
        target = (typeof target !== 'undefined') ? target : '_self';
        method = method.toUpperCase();

        // 创建一个 form
        let form = document.createElement('form');
        form.name = '__download_link';
        if (Array.isArray(param)) {
            param.forEach(function (value) {
                // 创建一个输入
                let input = document.createElement('input');
                // 设置相应参数
                input.type = 'hidden';
                input.name = value[0];
                input.value = value[1];
                form.appendChild(input);
            })
        } else if (typeof (param) == 'string') {
            param = Qs.parse(param, {ignoreQueryPrefix: true});
            for (let name in param) {
                if (!param.hasOwnProperty(name)) {
                    continue;
                }
                // 创建一个输入
                let input = document.createElement('input');
                // 设置相应参数
                input.type = 'hidden';
                input.name = name;
                input.value = param[name];
                form.appendChild(input);
            }
        }
        form.target = target;
        form.method = method;
        form.action = url;
        // 添加到 body 中
        document.body.appendChild(form);
        // 对该 form 执行提交
        form.submit();
        // 删除该 form
        document.body.removeChild(form);
    };

    communication.create = function (mark) {
        return new Class(mark);
    };

    return new Class();
});
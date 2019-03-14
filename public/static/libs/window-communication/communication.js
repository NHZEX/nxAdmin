(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // 支持 AMD
        define(factory);
    } else {
        window.Communication = factory();
    }
})(function () {

    let communication = {

    };

    function Class(mark) {
        if(!mark) {
            mark = randomString(16);
        }
        if(!window.hasOwnProperty('communication')) {
            window.communication = {};
        }

        this.mark = mark;
        if(!window.communication.hasOwnProperty(this.mark)) {
            window.communication[this.mark] = {}
        }
        this.data = window.communication[this.mark];

        /**
         * 获取随机字符串
         * @param {int} len
         * @returns {string}
         * @see https://stackoverflow.com/a/1349426/10242420
         */
        function randomString (len) {
            var text = '';
            var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

            for (var i = 0; i < len; i++)
                text += possible.charAt(Math.floor(Math.random() * possible.length));

            return text;
        }
    }

    Class.prototype.getMark = function () {
        return this.mark;
    };
    Class.prototype.destroy = function () {
        delete window.communication[this.mark];
    };

    communication.create = function (mark) {
        return new Class(mark);
    };

    return communication;
});

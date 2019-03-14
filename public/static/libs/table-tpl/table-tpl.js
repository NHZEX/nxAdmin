(function (tpl) {
    if (typeof define === 'function' && define.amd) {
        // 支持 AMD
        define(tpl);
    } else {
        window.Tpl = tpl();
    }
})(function () {
    Tpl = {
        toDateString: function (field, format) {
            format || (format = 'yyyy-MM-dd HH:mm')
            return function (d) {
                let value = d[field];
                return value ? layui.util.toDateString(value * 1000, format) : 'null';
            }
        },
        displayAvatar: function (field, fieldName) {
            return function (d) {
                let value = d[field], name = d[fieldName];
                return '<img style="width:30px;height:auto;" src="'+ value + '"  alt=""/>' + name;
            }

        },
        displayPic: function (field) {
            return function (d) {
                let value = d[field]; // 'xx/yy.png' 或 ['yy.png', 'xx.png']
                let data_output = '';
                if (!value) {
                    return data_output;
                }
                if (Array.isArray(value)) {
                    $.each(value, function (i, path) {
                        data_output += '<img style="width:auto;height:100%;" src="'+ path +'"  alt=""/>';
                    });
                } else {
                    data_output = '<img style="width:auto;height:100%;" src="'+ value +'"  alt=""/>';
                }
                return data_output;
            }
        },
        style: function (field, style) {
            return function (d) {
                let value, data_output;

                if(typeof field === "function") {
                    value = field(d);
                } else {
                    value = d[field];
                }

                data_output = '<span style="' + style + '">' + value + '</span>';
                return data_output;
            }
        }
    };

    return Tpl;
});
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ $info['title'] }} - 管理平台</title>
    <link rel="stylesheet" href="/static/libs/font-awesome/css/font-awesome.min.css" media="all"/>
    <link rel="stylesheet" href="/static/libs/kit/css/themes/default.css" media="all" id="skin" kit-skin/>
    <link rel="stylesheet" href="/static/css/main-loading.css"/>
</head>
<script>
    /* 来源: https://blog.csdn.net/qq576777915/article/details/78693240 */
    //获取浏览器页面可见高度和宽度
    var pageHeight = document.documentElement.clientHeight,
        pageWidth = document.documentElement.clientWidth;
    //计算loading框距离顶部和左部的距离（loading框小部件的宽度为90px，高度为90px）
    var loadingTop = pageHeight > 90 ? (pageHeight - 90) / 2 : 0,
        loadingLeft = pageWidth > 90 ? (pageWidth - 90) / 2 : 0;
    //在页面未加载完毕之前显示的loading Html自定义内容
    var loadingHtml = '<div id="loadingDiv" class="home-loading-div" style="height:' + pageHeight + 'px;">' +
        '<div class="home-spinner" style="position: top: 60px; margin:' + loadingTop + 'px auto ;"></div></div>';
    //呈现loading效果
    document.write(loadingHtml);
</script>
<script type="text/javascript">
    function imageError(img, nullimg) {
        nullimg || (nullimg = '/static/image/none.png');
        img.src = nullimg;
        img.title = '图片未找到.';
        img.onerror = null;
    }
</script>
<body class="kit-theme">
<div class="layui-layout layui-layout-admin kit-layout-admin">
    <div class="layui-header">
        <div class="layui-logo">{{ $info['title'] }}</div>
        <div class="layui-logo kit-logo-mobile">M</div>
        <ul class="layui-nav layui-layout-left kit-nav">
            <li class="layui-nav-item"><a href="javascript:;">控制台</a></li>
{{--            <li class="layui-nav-item">--}}
{{--                <a href="javascript:;">其它系统</a>--}}
{{--                <dl class="layui-nav-child">--}}
{{--                </dl>--}}
{{--            </li>--}}
        </ul>
        <ul class="layui-nav layui-layout-right kit-nav">
            <li class="layui-nav-item">
                <a href="javascript:;">
                    <i class="layui-icon">&#xe63f;</i> 皮肤
                </a>
                <dl class="layui-nav-child skin">
                    <dd><a href="javascript:;" data-skin="default" style="color:#393D49;"><i
                                    class="layui-icon">&#xe658;</i> 默认</a></dd>
                    <dd><a href="javascript:;" data-skin="orange" style="color:#ff6700;"><i
                                    class="layui-icon">&#xe658;</i> 橘子橙</a></dd>
                    <dd><a href="javascript:;" data-skin="green" style="color:#00a65a;"><i
                                    class="layui-icon">&#xe658;</i> 原谅绿</a></dd>
                    <dd><a href="javascript:;" data-skin="pink" style="color:#FA6086;"><i
                                    class="layui-icon">&#xe658;</i> 少女粉</a></dd>
                    <dd><a href="javascript:;" data-skin="blue.1" style="color:#00c0ef;"><i
                                    class="layui-icon">&#xe658;</i> 天空蓝</a></dd>
                    <dd><a href="javascript:;" data-skin="red" style="color:#dd4b39;"><i class="layui-icon">&#xe658;</i>
                            枫叶红</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item">
                <a href="javascript:;">
                    <img src="{{ $user['avatar'] }}" class="layui-nav-img" alt="avatar"
                         onerror="imageError(this)"> {{ $user['nickname'] }}
                </a>
                <dl class="layui-nav-child">
                    <dd><a href="javascript:;" id="basic_info"><span>基本资料</span></a></dd>
                    <dd><a href="javascript:;" id="clear_cache"><span>清除缓存</span></a></dd>
                </dl>
            </li>
            <li class="layui-nav-item"><a href="{{ $url['logout'] }}"><i class="fa fa-sign-out" aria-hidden="true"></i>
                    注销</a></li>
        </ul>
    </div>

    <div class="layui-side layui-bg-black kit-side">
        <div class="layui-side-scroll">
            <div class="kit-side-fold"><i class="fa fa-navicon" aria-hidden="true"></i></div>
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul id="navbarContainer" class="layui-nav layui-nav-tree" lay-filter="kitNavbar" kit-navbar>
            </ul>
        </div>
    </div>
    <div class="layui-body" id="container">
        <!-- 内容主体区域 -->
        <div style="padding: 15px;"><i class="layui-icon layui-anim layui-anim-rotate layui-anim-loop">&#xe63e;</i>
            请稍等...
        </div>
    </div>

    <div class="layui-footer">
        <!-- 底部固定区域 -->
        2017 &copy;
        <a href="http://kit.zhengjinfan.cn/">kit.zhengjinfan.cn/</a> MIT license
    </div>
</div>
<script type="text/javascript" src="/static/require/require.min.js"></script>
<script type="text/javascript" src="/static/main-config.js?_v={{ RESOURCE_VERSION }}&debug={{ app()->isDebug() }}"></script>
<script>
    require(['jquery', 'layui', 'kitapp', 'kitmessage', 'helper', 'axios'], function ($, layui, kitapp, kitmessage, helper, axios) {
        // （新）主入口
        kitapp.set({
            data: Object({!! $webmenu !!}),
            mainUrl: '{{ $url['mainpage'] }}'
        }).init();

        // 皮肤颜色切换
        $('dl.skin > dd').on('click', function () {
            let $that = $(this);
            let skin = $that.children('a').data('skin');
            switchSkin(skin);
        });

        $('#basic_info').on('click', function () {
            helper.formModal()
                .load('{{ $url['basic_info'] }}', [], '基本资料', '500px')
                .end(() => {
                    $(this).parents('dd').removeClass('layui-this');
                });
            return false;
        });
        $('#clear_cache').on('click', function () {
            axios.get('{{ $url['clear_cache'] }}').then(() => {
                layui.layer.msg('清除缓存成功');
                $(this).parents('dd').removeClass('layui-this');
            });
            return false;
        });
        let setSkin = function (value) {
                layui.data('kit_skin', {
                    key: 'skin',
                    value: value
                });
            },
            getSkinName = function () {
                return layui.data('kit_skin').skin;
            },
            switchSkin = function (value) {
                var targetDom = $('link[kit-skin]')[0];
                targetDom.href = targetDom.href.substring(0, targetDom.href.lastIndexOf('/') + 1) + value + targetDom.href.substring(targetDom.href.lastIndexOf('.'));
                setSkin(value);
            },
            initSkin = function () {
                var skin = getSkinName();
                switchSkin(skin === undefined ? 'default' : skin);
            }();

        $("#loadingDiv").fadeOut(1000, 'linear', function () {
            $(window).trigger('resize');
        });
    });
</script>
</body>

</html>
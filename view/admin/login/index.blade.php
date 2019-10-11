<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <title>管理平台</title>
    <link rel="stylesheet" href="/static/css/login.css" media="all" />
</head>
<body>
<div class="login-main " id="login">
    <header class="layui-elip">后台登录</header>
    <form class="layui-form" id="loginform" >
        <div class="layui-form-item">
            <div class="layui-input-inline">
                <input name="account" required placeholder="请输入用户名"  type="text" autocomplete="username" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-inline">
                <input name="password" required placeholder="请输入密码"  type="password" autocomplete="current-password" class="layui-input">
            </div>
        </div>
        @if (config('captcha.login'))
            <div class="layui-form-item">
                <div class="layui-input-inline">
                    <input id="input-verify-code"
                           name="captcha"
                           required
                           maxlength="4"
                           placeholder="请输入验证码"
                           type="text"
                           autocomplete="off"
                           class="layui-input"
                           style="width: 220px; float: left"
                    >
                    <img id="img-verify-code"
                         src="{{ $url_captcha }}"
                         alt="captcha"
                         height="38"
                         style="float: left"
                         onclick="refrushVerifyCode();"
                    />
                </div>
            </div>
        @endif
        <div class="layui-form-item" pane>
            <label for="lasting"></label>
            <div class="layui-input-inline">
                <input type="checkbox" name="lasting" id="lasting" title="记住我" value="1">
            </div>
        </div>
        <input type="hidden" name="#" value="{{ $login_token }}">
        <div class="layui-input-inline login-btn">
            <button type="submit" id="login-bth" class="layui-btn layui-btn-disabled" disabled>加载中</button>
        </div>
    </form>
</div>
</body>
<script type="text/javascript" src="/static/require/require.min.js"></script>
<script type="text/javascript" src="/static/main-config.js?_v={{ RESOURCE_VERSION }}&debug={{ app()->isDebug() }}"></script>
<script>
    function refrushVerifyCode() {
        let obj = document.getElementById('img-verify-code');
        if (obj) {
            obj.setAttribute('src', obj.getAttribute('src').toString().split('&')[0] + '&_t=' + Math.random());
            let captcha = document.getElementById('input-verify-code');
            captcha.value = '';
            captcha.focus();
        }
    }

    require([
        'jquery', 'js-cookie', 'verify', 'layui', 'layer', 'layform'
    ], ($, cookies, Verify, layui) => {

        function goMain() {
            window.location.href = '{{ $url_jump }}';
        }

        function testIsLogin() {
            return cookies.get('{{ $auto_login_name }}') > (new Date()).getTime() / 1000;
        }

        (function () {
            let $login_win = $('#login')
                ,login_top = 0
                ,old_log_top = 0
                ,login_height = $login_win.outerHeight();
            $(window).on('resize', function(){
                login_top = ($(this).height() - login_height) * 0.3;
            }).resize();
            setInterval(function () {
                if(old_log_top !== login_top){
                    old_log_top = login_top;
                    $login_win.css('margin-top',(login_top > 10 ? login_top : 10) + 'px');
                }
            },300);
        })();

        let checkLogin = setInterval(function () {
            // 会话访问令牌正确时跳转
            if(testIsLogin()){
                clearInterval(checkLogin);
                goMain();
            }
        }, 800);

        let layer = layui.layer;
        let $loginform = $('#loginform');
        let $pwd = $loginform.find('input[name=password]');

        let layui_checkbox = $('div.layui-form-checkbox');
        if(layui_checkbox.length) {
            layui_checkbox.on('keyup', function (event) {
                let key = event.which || 0;
                if (key === 32) {
                    $(this).trigger('click');
                    event.preventDefault();
                }
            }).prop('tabindex', 0)
        }

        $.get({
            'url': '{{ $url_check }}',
            'contentType': 'application/json; charset=utf-8',
            'global': false,
            'cache' : false
        }).done(function(data){
            if(0 === data.code){
                goMain();
            }
        }).fail(function(jqXHR){
            $('#login-bth').prop('disabled',false);
            //非401 没有权限才提示 通信失败处理流程
            if(jqXHR.status !== 401){
                layer.msg('通讯失败');
            }
        }).always(function () {
            $('#login-bth').removeClass('layui-btn-disabled').prop('disabled',false).text('登陆');
        });

        (new Verify($loginform)).pass(function (){
            let serialize = this.serializeObject();

            layer.msg('登陆中...', {icon: 16, shade: 0.01});
            $('#login-bth').prop('disabled',true);

            //提交登陆请求
            $.post('{{ $url_login }}', serialize).done(function(res){
                if(res.code === 0){
                    goMain();
                } else {
                    if(1001 === res.code) {
                        refrushVerifyCode();
                    }
                    if(1103 === res.code) {
                        refrushVerifyCode();
                        $pwd.focus().val('');
                    }
                    $('#loginform').find('');
                    layer.msg(res.msg);
                }
            }).fail(function(){
                //通讯错误处理
                layer.msg('通讯失败');
            }).always(function () {
                $('#login-bth').prop('disabled',false);
                layer.closeAll('loading');
            });
            return false;
        });
    });
</script>
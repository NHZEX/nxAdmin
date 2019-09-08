<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', '首页')</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="stylesheet" href="/static/libs/bootstrap3/css/bootstrap.css"/>
    <link rel="stylesheet" href="/static/css/admin-global.css"/>
    @yield('css')
</head>
<body>
@yield('content')
</body>
@section('base-javascript')
<script type="text/javascript" src="/static/require/require.min.js"></script>
<script type="text/javascript" src="/static/main-config.js?_v={{ RESOURCE_VERSION }}&debug={{ app()->isDebug() }}"></script>
@show
@yield('javascript')
</html>
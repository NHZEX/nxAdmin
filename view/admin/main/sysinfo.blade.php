@extends('layouts.master')
@section('title', '系统信息')
@section('css')
    <link rel="stylesheet" href="/static/libs/layui/css/layui.css"/>
@endsection
@section('content')
<div style="margin-left: 5px">
    <table class="layui-box layui-table" lay-even lay-skin="line" style="width: 700px">
        <colgroup>
            <col width="30%">
            <col>
        </colgroup>
        <thead>
        <tr><th colspan="2" style="text-align: center">系统信息</th></tr>
        </thead>
        <tbody>
        <tr>
            <td>CMS 系统版本</td>
            <td>1.1.0</td>
        </tr>
        <tr>
            <td>ThinkPHP 版本</td>
            <td>{{ \think\facade\App::version() }}</td>
        </tr>
        <tr>
            <td>服务器系统</td>
            <td>{{ php_uname() }}</td>
        </tr>
        <tr>
            <td>执行环境</td>
            <td>{{ $_SERVER['SERVER_SOFTWARE'] }}</td>
        </tr>
        <tr>
            <td>PHP接口类型</td>
            <td>{{ php_sapi_name() }}</td>
        </tr>
        <tr>
            <td>PHP版本</td>
            <td>{{ phpversion() }}</td>
        </tr>
        <tr>
            <td>MySQL版本</td>
            <td>{{ query_mysql_version() }}</td>
        </tr>
        <tr>
            <td>内存限制</td>
            <td>{{ ini_get('memory_limit') }}</td>
        </tr>
        <tr>
            <td>最长执行时间</td>
            <td>{{ ini_get('max_execution_time') }}s</td>
        </tr>
        <tr>
            <td>上传限制</td>
            <td>{{ ini_get('upload_max_filesize') }}</td>
        </tr>
        <tr>
            <td>POST限制</td>
            <td>{{ ini_get('post_max_size') }}</td>
        </tr>
        <tr>
            <td>路径缓存</td>
            <td>{{ ini_get('realpath_cache_size') }} ({{ realpath_cache_size() }})</td>
        </tr>
        </tbody>
    </table>
</div>
@endsection
@section('base-javascript')
@endsection
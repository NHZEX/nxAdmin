<?php /** @var array $genre_list */?>
<?php /** @var array $role_list */ ?>
<?php /** @var string[] $status_list */ ?>
@extends('layouts.subpage')
@section('content')
<form class="layui-form layui-form-pane" style="margin:10px" action="{{ $url_save }}" data-auto>
    <div class="layui-form-item">
        <label class="layui-form-label" for="genre">账户类型</label>
        <div class="layui-input-block">
            <!--suppress HtmlFormInputWithoutLabel -->
            <select name="genre" title="账户类型" required data-type="number">
                @foreach ($genre_list as $key=>$vo)
                    <option value="{{ $key }}">{{ $vo }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">账号</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" required minlength="4" name="username" title="账号" placeholder="无" autocomplete="">
        </div>
    </div><div class="layui-form-item">
        <label class="layui-form-label">昵称</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" required  minlength="4" name="nickname" title="昵称" placeholder="无" autocomplete="">
        </div>
    </div><div class="layui-form-item">
        <label class="layui-form-label">密码</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" required  minlength="6" name="password" title="密码" placeholder="无" autocomplete="">
        </div>
    </div><div class="layui-form-item">
        <label class="layui-form-label" for="role_id">角色</label>
        <div class="layui-input-block">
            <select name="role_id" id="role_id" title="角色" data-type="number">
                <option></option>
                @foreach ($role_list as $vo)
                    <option value="{{ $vo['id'] }}">{{ $vo['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div><div class="layui-form-item">
        <label class="layui-form-label" for="status">状态</label>
        <div class="layui-input-block">
            <select name="status" id="status" title="状态" required>
                @foreach($status_list as $key=>$vo)
                    <option value="{{ $key }}">{{ $vo }}</option>
                @endforeach
            </select>
        </div>
    </div><div class="layui-form-item">
        <label class="layui-form-label">头像</label>
        <div class="layui-input-block">
            <div class="compile_avatar_data" data-img=""></div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" type="submit">提交更改</button>
        </div>
    </div>
</form>
<script>
    const URL_HASH = '{{ urlHash(null) }}';
    let currSwap = window['swap'][URL_HASH];

    currSwap.initBefore = function ($content, data, index, next) {
        require(['jquery'], ($) => {
            if ($.isPlainObject(data)) {
                if (data['avatar_data'] && data['avatar']) {
                    $content.find('div.compile_avatar_data').data('img', data['avatar_data'] + ':' + data['avatar']);
                }
                let item = $content.find('input, select');
                item.filter('[name=genre]').parents('div.layui-form-item').remove();
                item.filter('[name=password]').parents('div.layui-form-item').remove();
                item.filter('[name=username]').prop('readonly', true).addClass('layui-disabled');
            }
            next();
        });
    };

    currSwap.initAfter = function ($content, data, index, next) {
        require(['jquery', 'uploadImage'], ($, uploadImage) => {
            uploadImage.render({
                elem: $content.find('div.compile_avatar_data')
                ,name: 'avatar'
                ,uploadUrl: '{{ $url_upload }}'
                ,fileSize: 1024
                ,parseData: (data) => {return data.path;}
                ,multiple: false
            });

            next();
        });
    };

</script>
@endsection
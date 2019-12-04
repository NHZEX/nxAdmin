@extends('layouts.subpage')
@section('content')
    <form class="layui-form layui-form-pane" action="{{ $url_save }}" style="margin:10px" data-auto>
        <div class="layui-form-item">
            <label class="layui-form-label">上级菜单</label>
            <div class="layui-input-block">
                <select name="pid" class="layui-select full-width" lay-ignore data-type="number">
                    <option selected value="0">顶级菜单</option>
                    @foreach($menu_data as $menu)
                        <option value="{{ $menu['id'] }}" @if($menu['status'] !== 0) disabled @endif>{!! $menu['title'] !!}</option>
                    @endforeach
                </select>
                <p class="help-block color-desc"><b>必填</b>，请选择上级菜单或顶级菜单</p>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label label-required">菜单标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" required value="" title="菜单标题" placeholder="请输入菜单名称" class="layui-input">
                <p class="help-block color-desc"><b>必填</b>，请填写菜单名称（如：系统管理）</p>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label label-required">菜单链接</label>
            <div class="layui-input-block">
                <div class="layui-input-inline" style="width: 100%; ">
                    <input type="text" name="url" autocomplete="off" required="required" title="请输入菜单链接" placeholder="请输入菜单链接" value="#" class="layui-input typeahead">
                </div>
                <p class="help-block color-desc">
                    <b>必填</b>，请填写系统节点（如：admin/user/index）；
                    <br>正常情况下，在输入的时候会有自动提示。如果是上级菜单时，请填写"#"符号，不要填写地址或节点地址
                </p>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label label-required">关联权限</label>
            <div class="layui-input-block">
                <div class="layui-input-inline" style="width: 100%; ">
                    <select name="node" class="layui-select full-width" lay-ignore>
                        <option selected value="">不关联</option>
                        @foreach($permission as $val)
                            <option value="{{ $val['name'] }}">{!! $val['__name'] !!}</option>
                        @endforeach
                    </select>
                </div>
                <p class="help-block color-desc">
                    <b>选填</b>
                </p>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">菜单图标</label>
            <div class="layui-input-block">
                <input placeholder="请输入图标代码" type="text" name="icon" value="" class="layui-input">
                <p class="help-block color-desc"><b>选填</b>，设置菜单选项前置图标</p>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">菜单排序</label>
            <div class="layui-input-block">
                <input placeholder="请输入" type="number" name="sort" value="0" min="-32768" max="32767" class="layui-input">
                <p class="help-block color-desc"><b>选填</b>，设置菜单在同级下的排序</p>
            </div>
        </div>
        <div class="hr-line-dashed"></div>
        <div class="layui-form-item text-center">
            <button class="layui-btn" type="submit" data-submit>保存数据</button>
            <button class="layui-btn layui-btn-danger" type="button" data-close data-confirm="确定要取消编辑吗？">取消编辑</button>
        </div>
    </form>
    <script>
        const URL_HASH = '{{ urlHash(null) }}';
        let curlSwap = window['swap'][URL_HASH];

        curlSwap.initBefore = function($content, data, index, next) {
            let autoFillOption = Object({!! json_encode_throw_on_error($node_data) !!});

            require(['jquery', 'bootstrap.typeahead'], function () {
                let $formInput = $content.find('form').find('input, textarea, select');

                $formInput.filter('[name=url]').on('blur',function () {
                    let find = false;
                    for(let value of autoFillOption) {
                        if(value.name === this.value) {
                            find = true;
                            break;
                        }
                    }
                    if(this.value === '') {
                        this.value = '#';
                    }
                    if(find === false || this.value === '') {
                        $formInput.filter('[name=node]').val('');
                    }
                }).typeahead({
                    source: autoFillOption,
                    items: 5,
                    afterSelect: function (val) {
                        $formInput.filter('[name=node]').val(val.id);
                    }
                });

                next();
            });
        };
    </script>
@endsection
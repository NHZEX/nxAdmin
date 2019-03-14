@extends('layouts.subpage')
@section('content')
    <form class="layui-form layui-form-pane" style="margin:10px" action="{{ $url_save }}" data-auto>
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input " required name="name" title="名称" placeholder="无" autocomplete="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" for="status">状态</label>
            <div class="layui-input-block">
                <select name="status" id="status" title="状态" required data-type="number">
                    @foreach($status_list as $key=>$vo)
                        <option value="{{ $key }}">{{ $vo }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" type="submit">提交更改</button>
            </div>
        </div>
    </form>
@endsection
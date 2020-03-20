<div data-main id="find-{{ url_hash(null) }}"
     data-layer-index="null"
     data-edit-data="{{ json_encode_throw_on_error($edit_data ?? null) }}"
>
    <script data-window-{{ \think\facade\Request::time() }}>
        const URL_HASH = '{{ url_hash(null) }}';
        let currScript, mainDiv, layerDiv;
        // 初始化数据交互
        window['swap'] || (window['swap'] = {});
        window['swap'][URL_HASH] || (window['swap'][URL_HASH] = {});
        // 初始化前置数据
        for (let script of document.scripts) {
            if(script.dataset.hasOwnProperty('window-{{ \think\facade\Request::time() }}')) {
                currScript = script;
                break;
            }
        }
        if (currScript instanceof HTMLElement
            && (mainDiv = currScript.parentNode) instanceof HTMLElement
            && mainDiv.dataset.hasOwnProperty('main')
        ) {
            // 获取页面索引
            layerDiv = mainDiv.parentNode.parentNode;
            if(layerDiv instanceof HTMLElement
               && layerDiv.tagName === 'DIV'
               && layerDiv.attributes.hasOwnProperty('times')
            ) {
                mainDiv.dataset.layerIndex = layerDiv.attributes.times.value;
            } else {
                throw Error('not layer, failed to find element')
            }
        } else {
            throw Error('not layer, failed to find element')
        }
    </script>
    @yield('content')
</div>

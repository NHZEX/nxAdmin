<?php

return [
    'restfull_definition' => [
        'index'  => ['get', '', 'index'],
        'select' => ['get', '/select', 'select'],
        'read'   => ['get', '/<id>', 'read'],
        'save'   => ['post', '', 'save'],
        'update' => ['put', '/<id>', 'update'],
//        'patch'  => ['patch', '/<id>', 'patch'],
        'delete' => ['delete', '/<id>', 'delete'],
    ],
    'route' => [
        'dump_path' => null,
        'only_load_dump' => !app()->isDebug(),
        'real_time_dump' => app()->isDebug(),
    ],
];

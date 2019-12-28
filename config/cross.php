<?php

return [
    'credentials' => true,
    'methods' => [
        'GET',
        'POST',
        'PATCH',
        'PUT',
        'DELETE',
        'OPTIONS'
    ],
    'headers' => [
        'Authorization',
        'Content-Type',
        'If-Match',
        'If-Modified-Since',
        'If-None-Match',
        'If-Unmodified-Since',
        'X-CSRF-TOKEN',
        'X-Requested-With',
        'X-Token'
    ],
];

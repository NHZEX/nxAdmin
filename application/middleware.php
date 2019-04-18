<?php
/**
 * Created by PhpStorm.
 * Date: 2019/1/5
 * Time: 10:54
 */

use app\Http\Middleware\Authorize;
use app\Http\Middleware\CrossSiteRequest;
use app\Http\Middleware\Exception;
use app\Http\Middleware\Validate;

return [
    CrossSiteRequest::class,
    Authorize::class,
    Validate::class,
    Exception::class,
];

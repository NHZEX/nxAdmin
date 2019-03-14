<?php
/**
 * Created by PhpStorm.
 * Date: 2019/1/5
 * Time: 10:54
 */

return [
    \app\http\middleware\CrossSiteRequest::class,
    \app\http\middleware\Authorize::class,
    \app\http\middleware\Validate::class,
    \app\http\middleware\Exception::class,
];

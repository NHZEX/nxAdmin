<?php

namespace app\controller\api;

use app\BaseController;
use app\Service\Validate\ValidateFilterTrait;

class Base extends BaseController
{
    use ValidateFilterTrait;
}

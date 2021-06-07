<?php

namespace app\Controller\api;

use app\BaseController;
use app\Service\Validate\ValidateFilterTrait;

class Base extends BaseController
{
    use ValidateFilterTrait;
}

<?php

namespace app\Controller;

use app\BaseController;
use app\Service\Validate\ValidateFilterTrait;

abstract class ApiBase extends BaseController
{
    use ValidateFilterTrait;
}

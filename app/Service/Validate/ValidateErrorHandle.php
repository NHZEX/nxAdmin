<?php

declare(strict_types=1);

namespace app\Service\Validate;

use think\Request;
use think\Response;
use Util\Reply;
use Zxin\Think\Validate\ErrorHandleInterface;
use Zxin\Think\Validate\ValidateContext;
use function is_array;
use function join;

class ValidateErrorHandle implements ErrorHandleInterface
{
    public function handle(Request $request, ValidateContext $context): Response
    {
        $validate = $context->getValidate();
        $message = is_array($validate->getError())
            ? join(',', $validate->getError())
            : $validate->getError();
        return Reply::bad(CODE_COM_PARAM, $message);
    }
}

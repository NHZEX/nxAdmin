<?php
declare(strict_types=1);

namespace app\Service\Auth\Access;

trait HandlesAuthorization
{
    /**
     * Create a new access response.
     *
     * @param string|null $message
     * @param mixed       $code
     * @return Response
     */
    protected function allow($message = null, $code = null)
    {
        return Response::allow($message, $code);
    }

    /**
     * Throws an unauthorized exception.
     *
     * @param string     $message
     * @param mixed|null $code
     * @return Response
     */
    protected function deny($message = null, $code = null)
    {
        return Response::deny($message, $code);
    }
}
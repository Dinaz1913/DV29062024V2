<?php

namespace Reelz222z\Cryptoexchange\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorController
{
    public function exception(\Throwable $exception)
    {
        $message = 'An error occurred';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new Response($message, $statusCode);
    }
}

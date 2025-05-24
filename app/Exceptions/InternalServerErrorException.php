<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InternalServerErrorException extends Exception
{
    public function __construct(Throwable $throwable)
    {
        parent::__construct($throwable->getMessage(), $throwable->getCode(), $throwable->getPrevious());
    }

    public function render(): JsonResponse
    {
        logger()->error($this->getMessage(), [
            'trace' => $this->getTraceAsString()
        ]);

        return response()->json(['message' => 'Internal Server Error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

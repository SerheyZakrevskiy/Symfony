<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

class RuntimeConstraintExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = $this->getCode($exception);
        $errors = $this->getErrors($exception);

        $event->setResponse(
            new JsonResponse([
                'data' => [
                    'code' => $statusCode,
                    'errors' => $errors
                ]
            ], $statusCode)
        );
    }

    private function getCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return Response::$statusTexts[$exception->getStatusCode()] ?? Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return Response::$statusTexts[$exception->getCode()] ?? Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    private function getErrors(Throwable $exception): array
    {
        if (method_exists($exception, 'getConstraintViolationList')) {
            return $this->mapConstraintViolations(
                $exception->getConstraintViolationList()
            );
        }

        if ($decoded = json_decode($exception->getMessage(), true)) {
            return $decoded['data']['errors'] ?? $decoded;
        }

        return [
            'error' => $exception->getMessage()
        ];
    }

    private function mapConstraintViolations(ConstraintViolationList $list): array
    {
        $errors = [];

        foreach ($list as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }
}

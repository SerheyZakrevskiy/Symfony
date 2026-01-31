<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestCheckerService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     *
     * @param mixed $content
     * @param array $fields
     */
    public function check(mixed $content, array $fields): void
    {
        if (!isset($content) || empty($content)) {
            throw new BadRequestException(
                'Empty request body',
                Response::HTTP_BAD_REQUEST
            );
        }

        $missingFields = [];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $content)) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new BadRequestException(
                'Required fields are missing: ' . implode(', ', $missingFields),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     *
     * @param array|object $data
     * @param array|null $constraints
     */
    public function validateRequestDataByConstraints(
        array|object $data,
        ?array $constraints = null
    ): void {
        $errors = $this->validator->validate(
            $data,
            $constraints ? new Collection($constraints) : null
        );

        if (count($errors) === 0) {
            return;
        }

        $validationErrors = [];

        foreach ($errors as $error) {
            $property = trim($error->getPropertyPath(), '[]');
            $validationErrors[$property] = $error->getMessage();
        }

        throw new UnprocessableEntityHttpException(
            json_encode($validationErrors)
        );
    }
}

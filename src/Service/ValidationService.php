<?php

namespace App\Service;

class ValidationService
{
    public function validate(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                throw new \Exception("Missing required field: $field");
            }
        }
    }
}

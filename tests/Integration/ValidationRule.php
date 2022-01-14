<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Countable;
use Symfony\Component\Validator\Constraint;

final class ValidationRule implements Countable
{
    /**
     * @var array<string, mixed>|object|null
     */
    private $data = [];

    /**
     * @var array<array-key, string>
     */
    private array $groups = [];

    /**
     * @var array<string, array<array-key, class-string>>
     */
    private array $errors = [];

    /**
     * @param array<string, mixed>|object|null              $data
     * @param array<array-key, string>                      $groups
     * @param array<string, array<array-key, class-string>> $errors
     */
    public function __construct(
        $data = null,
        array $groups = [],
        array $errors = []
    ) {
        $this->data = $data;
        $this->errors = $errors;
        $this->groups = $groups;
    }

    /**
     * @param array<array-key, string> $groups
     */
    public function addGroups(...$groups): self
    {
        /** @var string $group */
        foreach ($groups as $group) {
            if (!in_array($group, $this->groups, true)) {
                $this->groups[] = $group;
            }
        }

        return $this;
    }

    /**
     * @param array<string, mixed>|object $data
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param class-string<Constraint> $constraint
     */
    public function addError(string $propertyPath, string $constraint): self
    {
        if (!array_key_exists($propertyPath, $this->errors)) {
            $this->errors[$propertyPath] = [];
        }

        if (!in_array($constraint, $this->errors[$propertyPath], true)) {
            $this->errors[$propertyPath][] = $constraint;
        }

        return $this;
    }

    /**
     * @return array<string, mixed>|object|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array<array-key, string>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return array<string, array<array-key, class-string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function count(): int
    {
        return intval(array_sum(array_map('count', $this->errors)));
    }
}

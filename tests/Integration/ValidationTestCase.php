<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ValidationTestCase extends KernelTestCase
{
    /**
     * @dataProvider provideEntities
     */
    public function test(ValidationRule $validationRule): void
    {
        self::bootKernel();

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $constraintViolationList = $validator->validate($validationRule->getData(), null, $validationRule->getGroups());
        self::assertCount(count($validationRule), $constraintViolationList);

        /** @var array<string, array<array-key, class-string<Constraint>>> $errors */
        $errors = [];

        /** @var ConstraintViolation $violation */
        foreach ($constraintViolationList as $violation) {
            if (!isset($errors[$violation->getPropertyPath()])) {
                $errors[$violation->getPropertyPath()] = [];
            }

            if (($constraint = $violation->getConstraint()) !== null) {
                $errors[$violation->getPropertyPath()][] = get_class($constraint);
            }
        }

        self::assertEquals($validationRule->getErrors(), $errors);
    }

    /**
     * @return Generator<string, array<array-key, ValidationRule>>
     */
    abstract public function provideEntities(): Generator;

    /**
     * @param array<string, mixed>|object|null              $data
     * @param array<array-key, string>                      $groups
     * @param array<string, array<array-key, class-string>> $errors
     */
    protected static function createValidationRule(
        $data = null,
        array $groups = [],
        array $errors = []
    ): ValidationRule {
        return new ValidationRule($data, $groups, $errors);
    }
}

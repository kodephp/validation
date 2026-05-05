<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 整数验证规则，协程安全（无状态）
 *
 * 使用 filter_var(FILTER_VALIDATE_INT) 严格校验。
 */
class IntegerRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) === null) {
            return 'integer';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'integer';
    }
}

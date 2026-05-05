<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 浮点数验证规则，协程安全（无状态）
 *
 * 使用 filter_var(FILTER_VALIDATE_FLOAT) 严格校验。
 */
class FloatRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) === null) {
            return 'float';
        }

        return null;
    }

    public function getName(): string
    {
        return 'float';
    }
}

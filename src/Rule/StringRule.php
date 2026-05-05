<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 字符串验证规则，协程安全（无状态）
 */
class StringRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'string';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'string';
    }
}

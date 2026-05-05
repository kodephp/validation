<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 小于另一个字段验证规则，协程安全（无状态）
 *
 * 规则格式：lt:other_field
 */
class LtRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $otherField = $params[0] ?? '';
        if ($otherField === '' || !array_key_exists($otherField, $data)) {
            return 'lt';
        }

        if (!is_numeric($value) || !is_numeric($data[$otherField])) {
            return null;
        }

        if ((float) $value >= (float) $data[$otherField]) {
            return 'lt';
        }

        return null;
    }

    public function getName(): string
    {
        return 'lt';
    }
}

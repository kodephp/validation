<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 条件必填验证规则（如果），协程安全（无状态）
 *
 * 当指定字段等于给定值时，当前字段为必填。
 * 规则格式：required_if:other_field,value
 */
class RequiredIfRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $otherField = $params[0] ?? '';
        $triggerValue = $params[1] ?? null;

        if ($otherField === '' || $triggerValue === null) {
            return null;
        }

        $otherValue = $data[$otherField] ?? null;

        if ((string) $otherValue === (string) $triggerValue) {
            if ($value === null || $value === '' || $value === []) {
                return 'required_if';
            }
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'required_if';
    }
}

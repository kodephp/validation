<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 条件排除验证规则（如果），协程安全（无状态）
 *
 * 当条件满足时，从 validatedData 中排除该字段。
 * 规则格式：exclude_if:other_field,value
 */
class ExcludeIfRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $otherField = $params[0] ?? '';
        $triggerValue = $params[1] ?? null;

        if ($otherField === '' || $triggerValue === null) {
            return null;
        }

        $otherValue = $data[$otherField] ?? null;

        if ((string) $otherValue === (string) $triggerValue) {
            return 'exclude_if';
        }

        return null;
    }

    public function getName(): string
    {
        return 'exclude_if';
    }
}

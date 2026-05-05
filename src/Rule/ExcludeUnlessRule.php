<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 条件排除验证规则（除非），协程安全（无状态）
 *
 * 除非条件满足，否则从 validatedData 中排除该字段。
 * 规则格式：exclude_unless:other_field,value
 */
class ExcludeUnlessRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $otherField = $params[0] ?? '';
        $triggerValue = $params[1] ?? null;

        if ($otherField === '' || $triggerValue === null) {
            return null;
        }

        $otherValue = $data[$otherField] ?? null;

        if ((string) $otherValue !== (string) $triggerValue) {
            return 'exclude_unless';
        }

        return null;
    }

    public function getName(): string
    {
        return 'exclude_unless';
    }
}

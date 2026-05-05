<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 伴随必填验证规则，协程安全（无状态）
 *
 * 当指定字段存在于数据中时，当前字段为必填。
 * 规则格式：required_with:other_field
 */
class RequiredWithRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $otherField = $params[0] ?? '';

        if ($otherField === '') {
            return null;
        }

        if (array_key_exists($otherField, $data) && $data[$otherField] !== null && $data[$otherField] !== '' && $data[$otherField] !== []) {
            if ($value === null || $value === '' || $value === []) {
                return 'required_with';
            }
        }

        return null;
    }

    public function getName(): string
    {
        return 'required_with';
    }
}

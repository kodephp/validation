<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 唯一值验证规则，协程安全（无状态）
 *
 * 验证数组内所有值是否唯一（不重复）。
 * 规则格式：distinct 或 distinct:strict（严格类型模式）
 */
class DistinctRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_array($value)) {
            return 'distinct';
        }

        $strict = ($params[0] ?? '') === 'strict';

        if (count($value) !== count(array_unique($value, $strict ? SORT_REGULAR : SORT_STRING))) {
            return 'distinct';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'distinct';
    }
}

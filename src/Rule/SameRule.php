<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 相同验证规则，协程安全（无状态）
 *
 * 验证当前字段的值是否与指定字段的值完全相同。
 * 规则格式：same:other_field
 */
class SameRule implements RuleInterface
{
    /**
     * 执行相同验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为目标字段名
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $otherField = $params[0] ?? '';

        if ($otherField === '') {
            return 'same';
        }

        if (!array_key_exists($otherField, $data)) {
            return 'same';
        }

        if ($value !== $data[$otherField]) {
            return 'same';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[Override]
    public function getName(): string
    {
        return 'same';
    }
}

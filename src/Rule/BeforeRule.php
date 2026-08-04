<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 日期早于验证规则，协程安全（无状态）
 *
 * 验证当前日期字段是否早于指定字段。
 * 规则格式：before:other_date_field
 */
class BeforeRule implements RuleInterface
{
    /**
     * 执行日期早于验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为比较的目标字段名
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $otherField = $params[0] ?? '';

        if ($otherField === '' || !array_key_exists($otherField, $data)) {
            return 'before';
        }

        $otherValue = $data[$otherField];

        if ($otherValue === null || $otherValue === '') {
            return null;
        }

        try {
            $thisDate = new \DateTimeImmutable((string) $value);
            $thatDate = new \DateTimeImmutable((string) $otherValue);
        } catch (\DateMalformedStringException) {
            return 'before';
        }

        if ($thisDate >= $thatDate) {
            return 'before';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'before';
    }
}

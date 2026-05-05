<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 条件禁止字段验证规则，协程安全（无状态）
 *
 * 当指定字段的值等于给定值时，禁止当前字段存在。
 * 规则格式：prohibited_if:other_field,value
 */
class ProhibitedIfRule implements RuleInterface
{
    /**
     * 执行条件禁止验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，[目标字段名, 触发值]
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $otherField = $params[0] ?? '';
        $triggerValue = $params[1] ?? null;

        if ($otherField === '' || $triggerValue === null) {
            return null;
        }

        $otherValue = $data[$otherField] ?? null;

        if ((string) $otherValue === (string) $triggerValue && array_key_exists($field, $data)) {
            return 'prohibited_if';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'prohibited_if';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 时区验证规则，协程安全（无状态）
 *
 * 验证字符串是否为有效的 PHP 时区标识符。
 */
class TimezoneRule implements RuleInterface
{
    /**
     * 执行时区验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'timezone';
        }

        if (!in_array($value, timezone_identifiers_list(), true)) {
            return 'timezone';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'timezone';
    }
}

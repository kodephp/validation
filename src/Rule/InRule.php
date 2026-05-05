<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 枚举验证规则，协程安全（无状态）
 *
 * 验证值是否在给定的允许列表中。
 * 规则格式：in:apple,banana,orange
 */
class InRule implements RuleInterface
{
    /**
     * 执行枚举验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 允许的值列表
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    #[Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!in_array($value, $params, true)) {
            return 'in';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[Override]
    public function getName(): string
    {
        return 'in';
    }
}

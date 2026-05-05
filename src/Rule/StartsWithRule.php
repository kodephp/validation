<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 前缀验证规则，协程安全（无状态）
 *
 * 验证字符串是否以指定前缀开头（多字节安全）。
 * 规则格式：starts_with:prefix
 */
class StartsWithRule implements RuleInterface
{
    /**
     * 执行前缀验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为前缀字符串
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $prefix = (string) ($params[0] ?? '');

        if ($prefix === '' || !str_starts_with((string) $value, $prefix)) {
            return 'starts_with';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'starts_with';
    }
}

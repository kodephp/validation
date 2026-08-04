<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 全小写验证规则，协程安全（无状态）
 *
 * 规则格式：lowercase
 * 使用 mb_strtolower 比较，兼容多字节字符。
 */
class LowercaseRule implements RuleInterface
{
    /**
     * 执行小写验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'lowercase';
        }

        return mb_strtolower($value, 'UTF-8') === $value ? null : 'lowercase';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'lowercase';
    }
}

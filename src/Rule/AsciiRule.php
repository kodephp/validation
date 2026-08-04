<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 纯 ASCII 字符验证规则，协程安全（无状态）
 *
 * 规则格式：ascii
 * 仅允许可打印 ASCII 字符（0x20-0x7E）。
 */
class AsciiRule implements RuleInterface
{
    /**
     * 执行 ASCII 验证
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
            return 'ascii';
        }

        return preg_match('/^[\x20-\x7E]+$/', $value) === 1 ? null : 'ascii';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'ascii';
    }
}

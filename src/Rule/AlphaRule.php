<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 字母验证规则，协程安全（无状态）
 *
 * 验证字符串是否仅包含纯字母（支持多字节字符如中文）。
 * 如需仅 ASCII 字母，请使用 regex:/^[a-zA-Z]+$/ 。
 */
class AlphaRule implements RuleInterface
{
    /**
     * 执行纯字母验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value) || !preg_match('/^[\pL\pM]+$/u', (string) $value)) {
            return 'alpha';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'alpha';
    }
}

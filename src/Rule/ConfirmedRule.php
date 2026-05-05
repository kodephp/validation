<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 确认验证规则，协程安全（无状态）
 *
 * 验证当前字段的值是否与对应的 _confirmation 字段值一致。
 * 例如：password 字段需要与 password_confirmation 字段匹配。
 */
class ConfirmedRule implements RuleInterface
{
    /**
     * 执行确认验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $confirmationField = $field . '_confirmation';

        if (!array_key_exists($confirmationField, $data)) {
            return 'confirmed';
        }

        if ($value !== $data[$confirmationField]) {
            return 'confirmed';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'confirmed';
    }
}

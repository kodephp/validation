<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 区间验证规则，协程安全（无状态）
 *
 * 验证值是否在 [min, max] 区间内。
 * 支持数字和字符串（字符串按 mb_strlen 计算长度）。
 */
class BetweenRule implements RuleInterface
{
    /**
     * 执行区间验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，['min', 'max']
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $min = (float) ($params[0] ?? 0);
        $max = (float) ($params[1] ?? 0);

        if (is_string($value)) {
            $length = mb_strlen($value);
            if ($length < $min || $length > $max) {
                return 'between';
            }
        } elseif (is_numeric($value)) {
            $num = (float) $value;
            if ($num < $min || $num > $max) {
                return 'between';
            }
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'between';
    }
}

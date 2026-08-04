<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 最小值验证规则，协程安全（无状态）
 *
 * 支持数字和字符串（字符串按 mb_strlen 计算长度）。
 */
class MinRule implements RuleInterface
{
    /**
     * 执行最小值验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为最小值
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

        if (is_string($value)) {
            if (mb_strlen($value) < $min) {
                return 'min';
            }
        } elseif (is_numeric($value)) {
            if ((float) $value < $min) {
                return 'min';
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
        return 'min';
    }
}

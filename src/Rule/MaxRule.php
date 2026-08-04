<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 最大值验证规则，协程安全（无状态）
 *
 * 支持数字和字符串（字符串按 mb_strlen 计算长度）。
 */
class MaxRule implements RuleInterface
{
    /**
     * 执行最大值验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为最大值
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $max = (float) ($params[0] ?? 0);

        if (is_string($value)) {
            if (mb_strlen($value) > $max) {
                return 'max';
            }
        } elseif (is_numeric($value)) {
            if ((float) $value > $max) {
                return 'max';
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
        return 'max';
    }
}

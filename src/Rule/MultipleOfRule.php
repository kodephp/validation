<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 倍数验证规则，协程安全（无状态）
 *
 * 规则格式：multiple_of:5
 * 支持浮点数，使用极小误差容忍浮点精度问题。
 */
class MultipleOfRule implements RuleInterface
{
    /**
     * 浮点比较误差
     */
    private const float EPSILON = 1.0e-9;

    /**
     * 执行倍数验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为基数
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_numeric($value) || !isset($params[0]) || !is_numeric($params[0])) {
            return 'multiple_of';
        }

        $divisor = (float) $params[0];

        if ($divisor === 0.0) {
            return 'multiple_of';
        }

        $remainder = fmod((float) $value, $divisor);

        return abs($remainder) < self::EPSILON || abs(abs($remainder) - abs($divisor)) < self::EPSILON
            ? null
            : 'multiple_of';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'multiple_of';
    }
}

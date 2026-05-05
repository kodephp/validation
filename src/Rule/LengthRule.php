<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 精确字符串长度验证规则，协程安全（无状态）
 *
 * 验证字符串长度是否精确等于指定值（使用 mb_strlen 计算）。
 * 规则格式：length:10  表示字符串长度必须为 10 个字符
 */
class LengthRule implements RuleInterface
{
    /**
     * 执行精确长度验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为目标长度
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'length';
        }

        $expectedLength = (int) ($params[0] ?? 0);

        if (mb_strlen($value) !== $expectedLength) {
            return 'length';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'length';
    }
}

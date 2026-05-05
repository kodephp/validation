<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 数字验证规则，协程安全（无状态）
 *
 * 使用 is_numeric 判断值是否为数值或数值字符串。
 */
class NumericRule implements RuleInterface
{
    /**
     * 执行数值验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_numeric($value)) {
            return 'numeric';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[Override]
    public function getName(): string
    {
        return 'numeric';
    }
}

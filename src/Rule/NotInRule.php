<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 排除枚举验证规则，协程安全（无状态）
 *
 * 规则格式：not_in:admin,root,system
 * 值命中列表中任意一项即失败，比较时同时兼容字符串形态。
 */
class NotInRule implements RuleInterface
{
    /**
     * 执行排除枚举验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 禁止的值列表
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (in_array($value, $params, true)) {
            return 'not_in';
        }

        if (is_scalar($value) && in_array((string) $value, $params, true)) {
            return 'not_in';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'not_in';
    }
}

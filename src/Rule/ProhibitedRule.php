<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 禁止字段验证规则，协程安全（无状态）
 *
 * 验证指定字段必须不存在于数据中。
 * 若字段存在（无论值是什么），验证失败。
 */
class ProhibitedRule implements RuleInterface
{
    /**
     * 执行禁止字段验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if (array_key_exists($field, $data)) {
            return 'prohibited';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'prohibited';
    }
}

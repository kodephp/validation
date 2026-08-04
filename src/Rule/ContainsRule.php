<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 子串包含验证规则，协程安全（无状态）
 *
 * 规则格式：contains:abc,def
 * 字符串必须包含全部给定子串；数组则必须包含全部给定元素。
 */
class ContainsRule implements RuleInterface
{
    /**
     * 执行包含验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 需要包含的子串或元素列表
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === [] || $params === []) {
            return null;
        }

        if (is_array($value)) {
            foreach ($params as $needle) {
                if (!in_array($needle, $value, false)) {
                    return 'contains';
                }
            }
            return null;
        }

        if (!is_scalar($value)) {
            return 'contains';
        }

        $haystack = (string) $value;

        foreach ($params as $needle) {
            if ($needle !== '' && !str_contains($haystack, (string) $needle)) {
                return 'contains';
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
        return 'contains';
    }
}

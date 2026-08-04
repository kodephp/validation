<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * URL 别名（slug）验证规则，协程安全（无状态）
 *
 * 规则格式：slug
 * 仅允许小写字母、数字与连字符，且连字符不能位于首尾或连续出现。
 */
class SlugRule implements RuleInterface
{
    /**
     * 执行 slug 验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'slug';
        }

        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1 ? null : 'slug';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'slug';
    }
}

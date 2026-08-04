<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 后缀验证规则，协程安全（无状态）
 *
 * 验证字符串是否以指定后缀结尾（多字节安全）。
 * 规则格式：ends_with:suffix
 */
class EndsWithRule implements RuleInterface
{
    /**
     * 执行后缀验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为后缀字符串
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $suffix = (string) ($params[0] ?? '');

        if ($suffix === '' || !str_ends_with((string) $value, $suffix)) {
            return 'ends_with';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'ends_with';
    }
}

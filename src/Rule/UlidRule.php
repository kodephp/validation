<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * ULID 验证规则，协程安全（无状态）
 *
 * 规则格式：ulid
 * 26 位 Crockford Base32 字符（排除 I、L、O、U），首字符不超过 7。
 */
class UlidRule implements RuleInterface
{
    /**
     * 执行 ULID 验证
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
            return 'ulid';
        }

        return preg_match('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/i', $value) === 1 ? null : 'ulid';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'ulid';
    }
}

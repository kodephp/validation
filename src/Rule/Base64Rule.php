<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * Base64 字符串验证规则，协程安全（无状态）
 *
 * 规则格式：base64
 * 严格模式解码，要求字符集合法且填充正确。
 */
class Base64Rule implements RuleInterface
{
    /**
     * 执行 Base64 验证
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
            return 'base64';
        }

        if (strlen($value) % 4 !== 0) {
            return 'base64';
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return 'base64';
        }

        return base64_encode($decoded) === $value ? null : 'base64';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'base64';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * IPv4 地址验证规则，协程安全（无状态）
 *
 * 规则格式：ipv4
 */
class Ipv4Rule implements RuleInterface
{
    /**
     * 执行 IPv4 验证
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
            return 'ipv4';
        }

        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false ? null : 'ipv4';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'ipv4';
    }
}

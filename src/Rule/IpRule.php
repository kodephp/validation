<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * IP 地址验证规则，协程安全（无状态）
 *
 * 使用 filter_var(FILTER_VALIDATE_IP) 验证 IPv4/IPv6 地址。
 */
class IpRule implements RuleInterface
{
    /**
     * 执行 IP 地址验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            return 'ip';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'ip';
    }
}

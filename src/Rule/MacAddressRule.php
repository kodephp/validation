<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * MAC 地址验证规则，协程安全（无状态）
 *
 * 验证字符串是否为有效的 MAC 地址格式。
 * 支持格式：AA:BB:CC:DD:EE:FF、AA-BB-CC-DD-EE-FF、AABB.CCDD.EEFF
 */
class MacAddressRule implements RuleInterface
{
    /**
     * 执行 MAC 地址验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'mac_address';
        }

        $patterns = [
            '/^([0-9a-f]{2}[:]){5}([0-9a-f]{2})$/i',
            '/^([0-9a-f]{2}[-]){5}([0-9a-f]{2})$/i',
            '/^([0-9a-f]{4}[.]){2}([0-9a-f]{4})$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return null;
            }
        }

        return 'mac_address';
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'mac_address';
    }
}

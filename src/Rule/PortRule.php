<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 网络端口号验证规则，协程安全（无状态）
 *
 * 规则格式：port
 * 取值范围 1-65535。
 */
class PortRule implements RuleInterface
{
    /**
     * 执行端口号验证
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

        if (!is_scalar($value) || is_bool($value)) {
            return 'port';
        }

        $str = (string) $value;

        if (preg_match('/^\d+$/', $str) !== 1) {
            return 'port';
        }

        $port = (int) $str;

        return $port >= 1 && $port <= 65535 ? null : 'port';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'port';
    }
}

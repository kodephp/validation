<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * UUID 格式验证规则，协程安全（无状态）
 *
 * 验证字符串是否为标准 UUID 格式（含或不含连字符均可）。
 * 支持 UUID v1-v5 格式。
 */
class UuidRule implements RuleInterface
{
    /**
     * 执行 UUID 格式验证
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
            return 'uuid';
        }

        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        if (!preg_match($pattern, $value)) {
            return 'uuid';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'uuid';
    }
}

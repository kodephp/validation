<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 十六进制颜色值验证规则，协程安全（无状态）
 *
 * 规则格式：hex_color
 * 支持 #RGB、#RGBA、#RRGGBB、#RRGGBBAA 四种写法。
 */
class HexColorRule implements RuleInterface
{
    /**
     * 执行颜色值验证
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
            return 'hex_color';
        }

        return preg_match('/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value) === 1
            ? null
            : 'hex_color';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'hex_color';
    }
}

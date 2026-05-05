<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 布尔验证规则，协程安全（无状态）
 *
 * 接受：true, false, 0, 1, "0", "1", "true", "false"
 */
class BooleanRule implements RuleInterface
{
    /**
     * 执行布尔值验证
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

        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];

        if (!in_array($value, $acceptable, true)) {
            return 'boolean';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'boolean';
    }
}

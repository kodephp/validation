<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 日期验证规则，协程安全（无状态）
 *
 * 验证值是否为有效的日期格式。
 * 支持自定义格式参数：date:Y-m-d 或 date:Y-m-d H:i:s
 */
class DateRule implements RuleInterface
{
    /**
     * 执行日期验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为日期格式（默认 Y-m-d）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $format = $params[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, (string) $value);

        if ($date === false || $date->format($format) !== (string) $value) {
            return 'date';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[Override]
    public function getName(): string
    {
        return 'date';
    }
}

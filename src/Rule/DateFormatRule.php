<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 严格日期格式验证规则，协程安全（无状态）
 *
 * 规则格式：date_format:Y-m-d H:i:s
 * 要求值与格式完全一致（不接受 PHP 的宽松解析），参数不按逗号切分。
 */
class DateFormatRule implements RuleInterface
{
    /**
     * 执行日期格式验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为日期格式
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
            return 'date_format';
        }

        $format = $params[0] ?? '';

        if ($format === '') {
            return 'date_format';
        }

        $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);

        if ($date === false) {
            return 'date_format';
        }

        $errors = \DateTimeImmutable::getLastErrors();

        if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return 'date_format';
        }

        return $date->format($format) === $value ? null : 'date_format';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'date_format';
    }
}

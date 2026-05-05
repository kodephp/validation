<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 数字位数区间验证规则，协程安全（无状态）
 *
 * 验证整数值的位数是否在指定区间内。
 * 规则格式：digits_between:6,12
 */
class DigitsBetweenRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $min = (int) ($params[0] ?? 0);
        $max = (int) ($params[1] ?? 0);

        if (!is_numeric($value) || !preg_match('/^\d+$/', (string) $value)) {
            return 'digits_between';
        }

        $length = strlen(ltrim((string) $value, '0'));

        if ($length < $min || $length > $max) {
            return 'digits_between';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'digits_between';
    }
}

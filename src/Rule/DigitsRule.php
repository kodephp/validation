<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 数字位数验证规则，协程安全（无状态）
 *
 * 验证整数值的位数是否精确等于给定值。
 * 规则格式：digits:6
 */
class DigitsRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $count = (int) ($params[0] ?? 0);

        if (!is_numeric($value) || !preg_match('/^\d+$/', (string) $value)) {
            return 'digits';
        }

        if (strlen(ltrim((string) $value, '0')) !== $count) {
            return 'digits';
        }

        return null;
    }

    public function getName(): string
    {
        return 'digits';
    }
}

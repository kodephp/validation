<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 精确大小验证规则，协程安全（无状态）
 *
 * 验证数字或字符串长度是否精确等于给定值。
 * 规则格式：size:10
 */
class SizeRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $size = (float) ($params[0] ?? 0);

        if (is_string($value)) {
            if (mb_strlen($value) !== (int) $size) {
                return 'size';
            }
        } elseif (is_numeric($value)) {
            if ((float) $value !== $size) {
                return 'size';
            }
        } elseif (is_array($value)) {
            if (count($value) !== (int) $size) {
                return 'size';
            }
        }

        return null;
    }

    public function getName(): string
    {
        return 'size';
    }
}

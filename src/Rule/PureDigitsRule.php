<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 纯数字验证规则，协程安全（无状态）
 *
 * 验证字符串是否仅包含数字 0-9。
 */
class PureDigitsRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value) || !preg_match('/^\d+$/', $value)) {
            return 'pure_digits';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'pure_digits';
    }
}

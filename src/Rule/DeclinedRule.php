<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 拒绝验证规则，协程安全（无状态）
 *
 * 验证字段是否为 no, off, 0, false。
 */
class DeclinedRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $declined = ['no', 'off', '0', 0, false];

        if (!in_array($value, $declined, true)) {
            return 'declined';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'declined';
    }
}

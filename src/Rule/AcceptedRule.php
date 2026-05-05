<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 接受验证规则，协程安全（无状态）
 *
 * 验证字段是否为 yes, on, 1, true。通常用于同意条款等场景。
 */
class AcceptedRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $acceptable = ['yes', 'on', '1', 1, true];

        if (!in_array($value, $acceptable, true)) {
            return 'accepted';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'accepted';
    }
}

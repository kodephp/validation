<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 用户名格式验证规则，协程安全（无状态）
 *
 * 验证字符串是否以英文字母开头，后续仅包含字母、数字和下划线。
 * 常用于用户名、标识符等场景。
 * 格式：^[a-zA-Z][a-zA-Z0-9_]*$
 */
class UsernameRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $value)) {
            return 'username';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'username';
    }
}

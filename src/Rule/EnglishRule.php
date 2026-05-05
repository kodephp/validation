<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 纯英文验证规则，协程安全（无状态）
 *
 * 验证字符串是否仅包含英文字母（a-zA-Z）。
 */
class EnglishRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value) || !preg_match('/^[a-zA-Z]+$/', $value)) {
            return 'english';
        }

        return null;
    }

    public function getName(): string
    {
        return 'english';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 英文开头验证规则，协程安全（无状态）
 *
 * 验证字符串是否以英文字母开头。
 * 规则格式：start_with_english 或 start_with_english:min_length
 */
class StartWithEnglishRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value) || !preg_match('/^[a-zA-Z]/', $value)) {
            return 'start_with_english';
        }

        return null;
    }

    public function getName(): string
    {
        return 'start_with_english';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 中英文数字组合验证规则，协程安全（无状态）
 *
 * 验证字符串是否仅包含中文、英文字母和数字。
 * 常用于昵称、姓名等场景。
 */
class ChineseAlphaNumRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value) || !preg_match('/^[\p{Han}a-zA-Z0-9]+$/u', $value)) {
            return 'chinese_alpha_num';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'chinese_alpha_num';
    }
}

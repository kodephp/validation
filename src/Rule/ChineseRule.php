<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 中文字符验证规则，协程安全（无状态）
 *
 * 验证字符串是否包含中文字符（Unicode \p{Han}）。
 * 若要求纯中文，可使用 regex:/^[\p{Han}]+$/u 。
 */
class ChineseRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value) || !preg_match('/\p{Han}/u', $value)) {
            return 'chinese';
        }

        return null;
    }

    public function getName(): string
    {
        return 'chinese';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 特殊字符验证规则，协程安全（无状态）
 *
 * 验证字符串是否包含至少一个指定的特殊字符。
 * 规则格式：special_chars 或 special_chars:!@#$%^&*()_+-=[]{}|;:',.<>?/\`~"
 *
 * 默认检查字符：!@#$%^&*()-_=+[]{}|;:'",.<>?/\`~
 */
class SpecialCharsRule implements RuleInterface
{
    /**
     * 默认特殊字符集
     */
    private const DEFAULT_CHARS = '!@#$%^&*()-_=+[]{}|;:\'",.<>?/\\`~';

    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $charSet = $params[0] ?? self::DEFAULT_CHARS;

        // 检查值中是否包含至少一个指定字符集中的字符
        $pattern = '/[' . preg_quote($charSet, '/') . ']/u';

        if (!is_string($value) || !preg_match($pattern, $value)) {
            return 'special_chars';
        }

        return null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'special_chars';
    }
}

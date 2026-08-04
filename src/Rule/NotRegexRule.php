<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 反向正则验证规则，协程安全（无状态）
 *
 * 规则格式：not_regex:/^admin/i
 * 匹配成功即视为验证失败。参数不会按逗号切分，可安全书写含逗号的量词。
 */
class NotRegexRule implements RuleInterface
{
    /**
     * 执行反向正则验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为正则表达式
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $pattern = $params[0] ?? '';

        if ($pattern === '' || !is_scalar($value)) {
            return null;
        }

        return @preg_match($pattern, (string) $value) === 1 ? 'not_regex' : null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'not_regex';
    }
}

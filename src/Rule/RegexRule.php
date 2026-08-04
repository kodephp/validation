<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 正则验证规则，协程安全（无状态）
 *
 * 使用 preg_match 验证值是否匹配给定的正则表达式。
 * 规则格式：regex:/^[a-z]+$/ 或 regex:/pattern/flags
 */
class RegexRule implements RuleInterface
{
    /**
     * 执行正则验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为正则表达式
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $pattern = (string) ($params[0] ?? '');

        if ($pattern === '' || preg_match($pattern, (string) $value) !== 1) {
            return 'regex';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'regex';
    }
}

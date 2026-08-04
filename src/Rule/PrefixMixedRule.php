<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 前缀英文+后缀混合验证规则，协程安全（无状态）
 *
 * 验证字符串前N位必须是英文字母，后续可以是字母数字的组合。
 * 规则格式：prefix_mixed:2  表示前2位必须英文，后面可混合字母数字
 */
class PrefixMixedRule implements RuleInterface
{
    /**
     * 执行前缀英文+后缀混合验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为前缀英文长度
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'prefix_mixed';
        }

        $prefixLen = (int) ($params[0] ?? 1);

        if ($prefixLen < 1) {
            return 'prefix_mixed';
        }

        if (mb_strlen($value) < $prefixLen) {
            return 'prefix_mixed';
        }

        $prefix = mb_substr($value, 0, $prefixLen);
        $suffix = mb_substr($value, $prefixLen);

        if (!preg_match('/^[a-zA-Z]+$/', $prefix)) {
            return 'prefix_mixed';
        }

        if ($suffix !== '' && !preg_match('/^[a-zA-Z0-9]+$/', $suffix)) {
            return 'prefix_mixed';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'prefix_mixed';
    }
}

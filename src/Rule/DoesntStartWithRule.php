<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 禁止前缀验证规则，协程安全（无状态）
 *
 * 规则格式：doesnt_start_with:admin,root
 * 命中任意一个前缀即失败。
 */
class DoesntStartWithRule implements RuleInterface
{
    /**
     * 执行禁止前缀验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 禁止的前缀列表
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === [] || $params === []) {
            return null;
        }

        if (!is_scalar($value)) {
            return 'doesnt_start_with';
        }

        $subject = (string) $value;

        foreach ($params as $prefix) {
            if ($prefix !== '' && str_starts_with($subject, (string) $prefix)) {
                return 'doesnt_start_with';
            }
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'doesnt_start_with';
    }
}

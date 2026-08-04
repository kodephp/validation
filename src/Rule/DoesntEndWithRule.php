<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 禁止后缀验证规则，协程安全（无状态）
 *
 * 规则格式：doesnt_end_with:.exe,.sh
 * 命中任意一个后缀即失败。
 */
class DoesntEndWithRule implements RuleInterface
{
    /**
     * 执行禁止后缀验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 禁止的后缀列表
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
            return 'doesnt_end_with';
        }

        $subject = (string) $value;

        foreach ($params as $suffix) {
            if ($suffix !== '' && str_ends_with($subject, (string) $suffix)) {
                return 'doesnt_end_with';
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
        return 'doesnt_end_with';
    }
}

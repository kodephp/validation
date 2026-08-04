<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 过去日期验证规则，协程安全（无状态）
 *
 * 验证日期是否为过去的日期。
 */
class PastRule implements RuleInterface
{
    /**
     * 执行过去日期验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        try {
            $timestamp = is_string($value) ? strtotime($value) : $value;

            if ($timestamp === false) {
                return 'past';
            }

            if ($timestamp >= time()) {
                return 'past';
            }
        } catch (\Throwable $e) {
            return 'past';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'past';
    }
}

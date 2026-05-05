<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 未来日期验证规则，协程安全（无状态）
 *
 * 验证日期是否为未来的日期。
 */
class FutureRule implements RuleInterface
{
    /**
     * 执行未来日期验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        try {
            $timestamp = is_string($value) ? strtotime($value) : $value;

            if ($timestamp === false) {
                return 'future';
            }

            if ($timestamp <= time()) {
                return 'future';
            }
        } catch (\Throwable $e) {
            return 'future';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'future';
    }
}

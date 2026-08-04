<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 中国大陆手机号验证规则，协程安全（无状态）
 *
 * 规则格式：mobile
 * 匹配 1 开头、第二位为 3-9 的 11 位号码。
 */
class MobileRule implements RuleInterface
{
    /**
     * 执行手机号验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_scalar($value)) {
            return 'mobile';
        }

        return preg_match('/^1[3-9]\d{9}$/', (string) $value) === 1 ? null : 'mobile';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'mobile';
    }
}

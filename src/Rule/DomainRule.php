<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 域名验证规则，协程安全（无状态）
 *
 * 规则格式：domain
 * 校验主机名格式，要求至少包含一级顶级域，总长度不超过 253。
 */
class DomainRule implements RuleInterface
{
    /**
     * 执行域名验证
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

        if (!is_string($value) || strlen($value) > 253) {
            return 'domain';
        }

        $pattern = '/^(?!-)[a-zA-Z0-9-]{1,63}(?<!-)(?:\.(?!-)[a-zA-Z0-9-]{1,63}(?<!-))*\.[a-zA-Z]{2,63}$/';

        return preg_match($pattern, $value) === 1 ? null : 'domain';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'domain';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * JSON 验证规则，协程安全（无状态）
 *
 * 基于 PHP 8.3 原生 json_validate()，相比 json_decode() 不构建中间数据结构，
 * 大 JSON 串的内存占用与耗时显著更低。
 */
class JsonRule implements RuleInterface
{
    /**
     * 执行 JSON 格式验证
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

        if (!is_string($value)) {
            return 'json';
        }

        return json_validate($value) ? null : 'json';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'json';
    }
}

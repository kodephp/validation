<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 语义化版本号验证规则，协程安全（无状态）
 *
 * 规则格式：semver
 * 遵循 semver.org 2.0.0 官方正则，支持预发布与构建元数据。
 */
class SemverRule implements RuleInterface
{
    /**
     * 语义化版本官方正则
     */
    private const string PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)'
        . '(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?'
        . '(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    /**
     * 执行版本号验证
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
            return 'semver';
        }

        return preg_match(self::PATTERN, $value) === 1 ? null : 'semver';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'semver';
    }
}

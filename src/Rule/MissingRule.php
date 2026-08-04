<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

use Kode\Validation\Rule\Concern\AccessesFieldTrait;

/**
 * 字段禁止出现验证规则，协程安全（无状态）
 *
 * 规则格式：missing
 * 键存在即失败，常用于拦截前端不应提交的敏感字段。
 */
class MissingRule implements RuleInterface
{
    use AccessesFieldTrait;

    /**
     * 执行禁止出现验证
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
        return $this->fieldExists($data, $field) ? 'missing' : null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'missing';
    }
}

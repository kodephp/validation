<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

use Kode\Validation\Rule\Concern\AccessesFieldTrait;

/**
 * 非空占位验证规则，协程安全（无状态）
 *
 * 规则格式：filled
 * 字段不存在时通过；一旦存在则不允许为空。与 required 的区别在于
 * required 要求键必须存在，filled 只约束"存在时不能为空"。
 */
class FilledRule implements RuleInterface
{
    use AccessesFieldTrait;

    /**
     * 执行非空占位验证
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
        if (!$this->fieldExists($data, $field)) {
            return null;
        }

        return $this->isEmptyValue($value) ? 'filled' : null;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'filled';
    }
}

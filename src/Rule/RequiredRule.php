<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 必填验证规则，协程安全（无状态）
 *
 * 判断字段值是否存在且非空。空值定义：
 *  - null
 *  - 空字符串 ''
 *  - 空数组 []
 */
class RequiredRule implements RuleInterface
{
    /**
     * 执行必填验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return 'required';
        }

        return null;
    }

    /**
     * 获取规则名称
     */
    public function getName(): string
    {
        return 'required';
    }
}

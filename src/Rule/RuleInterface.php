<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 验证规则接口
 *
 * 每个规则类代表一条独立的验证逻辑。实现类必须是无状态的，
 * 以便在多个协程中安全共享。
 */
interface RuleInterface
{
    /**
     * 执行验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，如 min:5 中的 ['5']
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回错误消息，通过返回 null
     */
    public function validate(string $field, mixed $value, array $params, array $data): ?string;

    /**
     * 获取规则名称
     *
     * @return string 规则名（英文），如 'required'、'email' 等
     */
    public function getName(): string;
}

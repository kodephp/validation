<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 闭包自定义验证规则，协程安全（无状态）
 *
 * 允许用户传入闭包函数作为自定义验证规则。
 * 闭包签名：function(string $field, mixed $value, array $params, array $data): ?string
 * 返回 null 表示验证通过，返回字符串表示验证失败（返回值为错误消息键名）。
 */
class ClosureRule implements RuleInterface
{
    /**
     * @var \Closure(string, mixed, array, array): ?string
     */
    private readonly \Closure $callback;

    /**
     * @param \Closure(string, mixed, array, array): ?string $callback
     */
    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * 执行闭包验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回原生错误消息，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        $result = ($this->callback)($field, $value, $params, $data);

        if ($result === null || $result === true) {
            return null;
        }

        if (is_string($result)) {
            return $result;
        }

        return 'closure';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'closure';
    }
}

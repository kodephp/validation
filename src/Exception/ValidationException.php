<?php

declare(strict_types=1);

namespace Kode\Validation\Exception;

use Kode\Validation\ValidationResult;
use RuntimeException;

/**
 * 验证异常
 *
 * 当需要在验证失败时抛出异常（而非返回结果对象）的可选方案。
 * 异常携带完整的 ValidationResult，便于上层捕获后获取详细错误信息。
 */
class ValidationException extends RuntimeException
{
    /**
     * @param ValidationResult $result  验证结果
     * @param string|null      $message 异常消息，默认取首条错误
     */
    public function __construct(
        private readonly ValidationResult $result,
        ?string $message = null
    ) {
        parent::__construct($message ?? $result->first() ?? '数据验证失败');
    }

    /**
     * 获取验证结果
     *
     * @return ValidationResult 包含详细错误信息的验证结果对象
     */
    public function getResult(): ValidationResult
    {
        return $this->result;
    }

    /**
     * 获取所有错误消息
     *
     * @return array 格式 ['字段名' => ['规则名' => '错误消息', ...], ...]
     */
    public function errors(): array
    {
        return $this->result->errors();
    }

    /**
     * 获取按字段归并的错误消息
     *
     * @return array<string, list<string>>
     */
    public function messages(): array
    {
        return $this->result->messages();
    }

    /**
     * 获取第一条错误消息
     */
    public function first(?string $field = null): ?string
    {
        return $this->result->first($field);
    }
}

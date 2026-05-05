<?php

declare(strict_types=1);

namespace Kode\Validation;

use Kode\Validation\Contract\ValidationResultInterface;

/**
 * 验证结果，协程安全（只读不可变）
 *
 * 封装一次验证操作的所有结果信息。对象创建后不可修改，
 * 因此可以在多个协程间安全传递。
 */
readonly class ValidationResult implements ValidationResultInterface
{
    /**
     * @param bool  $valid         验证是否通过
     * @param array $errors        错误详情，格式 ['字段名' => ['规则名' => '消息', ...], ...]
     * @param array $validatedData 通过验证的字段数据
     */
    public function __construct(
        private bool $valid,
        private array $errors,
        private array $validatedData
    ) {
    }

    /**
     * 判断验证是否通过
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * 获取所有验证错误
     *
     * @return array 格式 ['字段名' => ['规则名' => '错误消息', ...], ...]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * 获取通过验证的数据
     *
     * 只包含验证通过的字段。若整个验证失败，可通过此方法获取
     * 部分通过验证的字段数据。
     *
     * @return array 键值对，仅包含验证通过的字段
     */
    public function validatedData(): array
    {
        return $this->validatedData;
    }
}

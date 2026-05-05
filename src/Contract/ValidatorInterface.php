<?php

declare(strict_types=1);

namespace Kode\Validation\Contract;

use Kode\Validation\ValidationResult;

/**
 * 验证器接口，协程安全
 *
 * 实现类必须确保并发验证互不干扰，每次调用 validate() 不依赖实例属性存储验证状态。
 */
interface ValidatorInterface
{
    /**
     * 验证数据
     *
     * @param array $data     要验证的数据（键值对）
     * @param array $rules    验证规则，格式 ['字段名' => '规则1|规则2', ...]
     * @param array $messages 自定义错误消息，格式 ['字段名.规则名' => '消息模板']
     * @return ValidationResult 验证结果对象
     */
    public function validate(array $data, array $rules, array $messages = []): ValidationResult;
}

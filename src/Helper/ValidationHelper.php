<?php

declare(strict_types=1);

namespace Kode\Validation\Helper;

use Kode\Validation\Contract\ValidatorInterface;
use Kode\Validation\ValidationResult;
use Kode\Validation\Validator;

/**
 * 验证快捷方式，协程安全
 *
 * 提供静态方法快速验证，适用于无需依赖注入的简单场景。
 * 内部维护一个共享的 Validator 实例，所有方法均为局部变量操作，协程安全。
 *
 * 使用示例：
 * <code>
 * $result = ValidationHelper::check(
 *     ['name' => '张三'],
 *     ['name' => 'required|min:2']
 * );
 * </code>
 */
final class ValidationHelper
{
    /**
     * @var ValidatorInterface|null 共享验证器实例
     */
    private static ?ValidatorInterface $instance = null;

    /**
     * 获取或创建验证器实例
     */
    private static function getInstance(): ValidatorInterface
    {
        return self::$instance ??= Validator::create();
    }

    /**
     * 快速验证并返回结果对象
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     * @return ValidationResult
     */
    public static function check(array $data, array $rules, array $messages = []): ValidationResult
    {
        return self::getInstance()->validate($data, $rules, $messages);
    }

    /**
     * 快速验证并返回通过的数据，不通过则返回 null
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     * @return array|null 通过的数据或 null
     */
    public static function validated(array $data, array $rules, array $messages = []): ?array
    {
        $result = self::getInstance()->validate($data, $rules, $messages);

        return $result->isValid() ? $result->validatedData() : null;
    }

    /**
     * 快速判断数据是否通过验证
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     */
    public static function passes(array $data, array $rules, array $messages = []): bool
    {
        return self::getInstance()->validate($data, $rules, $messages)->isValid();
    }

    /**
     * 快速取得第一条错误消息
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     * @return string|null 全部通过时返回 null
     */
    public static function firstError(array $data, array $rules, array $messages = []): ?string
    {
        return self::getInstance()->validate($data, $rules, $messages)->first();
    }

    /**
     * 自定义共享验证器实例
     *
     * @param ValidatorInterface $validator
     */
    public static function useInstance(ValidatorInterface $validator): void
    {
        self::$instance = $validator;
    }

    /**
     * 重置共享验证器实例（主要用于单元测试）
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}

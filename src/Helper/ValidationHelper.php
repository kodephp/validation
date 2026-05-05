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
        if (self::$instance === null) {
            $messages = [];
            $configPath = dirname(__DIR__, 2) . '/config/validation.php';
            if (file_exists($configPath)) {
                $messages = require $configPath;
            }
            self::$instance = new Validator($messages);
        }

        return self::$instance;
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
     * 自定义共享验证器实例
     *
     * @param ValidatorInterface $validator
     */
    public static function useInstance(ValidatorInterface $validator): void
    {
        self::$instance = $validator;
    }
}

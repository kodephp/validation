<?php

declare(strict_types=1);

namespace Kode\Validation\Trait;

use Kode\Validation\Contract\ValidatorInterface;
use Kode\Validation\Exception\ValidationException;
use Kode\Validation\ValidationResult;
use Kode\Validation\Validator;

/**
 * 请求验证特征，协程安全
 *
 * 用于 Controller、Service、Model 等类中快速集成验证功能。
 *
 * 使用示例：
 * <code>
 * class UserController
 * {
 *     use ValidatesRequests;
 *
 *     public function store(array $request): array
 *     {
 *         $validated = $this->validateThrows($request, [
 *             'name'  => 'required|min:2|max:20',
 *             'email' => 'required|email',
 *         ]);
 *
 *         return User::create($validated);
 *     }
 * }
 * </code>
 */
trait ValidatesRequests
{
    /**
     * @var ValidatorInterface|null 验证器实例
     */
    private ?ValidatorInterface $validationValidator = null;

    /**
     * 获取验证器实例
     *
     * 优先使用已设置的实例，否则创建默认实例。
     * 子类可重写此方法来注入自定义验证器。
     *
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        if ($this->validationValidator === null) {
            $messages = [];
            $configPath = dirname(__DIR__, 2) . '/config/validation.php';
            if (file_exists($configPath)) {
                $messages = require $configPath;
            }
            $this->validationValidator = new Validator($messages);
        }

        return $this->validationValidator;
    }

    /**
     * 设置验证器实例
     *
     * @param ValidatorInterface $validator 自定义验证器
     */
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validationValidator = $validator;
    }

    /**
     * 验证请求数据，返回验证结果
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     * @return ValidationResult
     */
    public function validateRequest(array $data, array $rules, array $messages = []): ValidationResult
    {
        return $this->getValidator()->validate($data, $rules, $messages);
    }

    /**
     * 验证请求数据，失败时抛出异常，成功时返回验证通过的数据
     *
     * 适用于 Controller 中"验证不通过应返回 422"的场景。
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     * @return array 通过验证的数据
     * @throws ValidationException 验证失败时抛出
     */
    public function validateThrows(array $data, array $rules, array $messages = []): array
    {
        $result = $this->getValidator()->validate($data, $rules, $messages);

        if (!$result->isValid()) {
            throw new ValidationException($result);
        }

        return $result->validatedData();
    }

    /**
     * 验证请求数据，失败时返回错误，成功时返回验证通过的数据
     *
     * 适用于 API 响应中直接返回结果的场景。
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     * @return array ['valid' => bool, 'data' => array, 'errors' => array]
     */
    public function validateWithResult(array $data, array $rules, array $messages = []): array
    {
        $result = $this->getValidator()->validate($data, $rules, $messages);

        return [
            'valid'  => $result->isValid(),
            'data'   => $result->isValid() ? $result->validatedData() : [],
            'errors' => $result->errors(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation;

use Kode\Validation\Contract\ValidationResultInterface;

/**
 * 验证结果，协程安全（只读不可变）
 *
 * 封装一次验证操作的所有结果信息。对象创建后不可修改，
 * 因此可以在多个协程间安全传递。
 *
 * 实现 Countable 与 JsonSerializable，可直接 count() 统计出错字段数，
 * 或 json_encode() 输出接口响应结构。
 */
readonly class ValidationResult implements ValidationResultInterface, \Countable, \JsonSerializable
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
    #[\Override]
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * 判断验证是否失败（isValid 的反义，便于可读性更好的早返回写法）
     */
    public function fails(): bool
    {
        return !$this->valid;
    }

    /**
     * 获取所有验证错误
     *
     * @return array 格式 ['字段名' => ['规则名' => '错误消息', ...], ...]
     */
    #[\Override]
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
    #[\Override]
    public function validatedData(): array
    {
        return $this->validatedData;
    }

    /**
     * 获取第一条错误消息
     *
     * @param string|null $field 指定字段，null 表示取全局第一条
     * @return string|null 无错误时返回 null
     */
    public function first(?string $field = null): ?string
    {
        if ($field !== null) {
            $fieldErrors = $this->errors[$field] ?? [];
            return $fieldErrors === [] ? null : (string) reset($fieldErrors);
        }

        foreach ($this->errors as $fieldErrors) {
            if ($fieldErrors !== []) {
                return (string) reset($fieldErrors);
            }
        }

        return null;
    }

    /**
     * 判断指定字段是否存在错误
     */
    public function has(string $field): bool
    {
        return isset($this->errors[$field]) && $this->errors[$field] !== [];
    }

    /**
     * 获取指定字段的全部错误消息
     *
     * @return list<string>
     */
    public function get(string $field): array
    {
        return array_values($this->errors[$field] ?? []);
    }

    /**
     * 获取按字段归并的错误消息（去掉规则名层级）
     *
     * @return array<string, list<string>> 格式 ['字段名' => ['消息1', '消息2'], ...]
     */
    public function messages(): array
    {
        $messages = [];

        foreach ($this->errors as $field => $fieldErrors) {
            $messages[$field] = array_values($fieldErrors);
        }

        return $messages;
    }

    /**
     * 获取扁平化的全部错误消息
     *
     * @return list<string>
     */
    public function flatten(): array
    {
        $flat = [];

        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $message) {
                $flat[] = (string) $message;
            }
        }

        return $flat;
    }

    /**
     * 获取未通过的字段与规则名
     *
     * @return array<string, list<string>> 格式 ['字段名' => ['规则名1', '规则名2'], ...]
     */
    public function failedRules(): array
    {
        $failed = [];

        foreach ($this->errors as $field => $fieldErrors) {
            $failed[$field] = array_keys($fieldErrors);
        }

        return $failed;
    }

    /**
     * 获取出错字段名列表
     *
     * @return list<string>
     */
    public function invalidFields(): array
    {
        return array_keys($this->errors);
    }

    /**
     * 从通过的数据中挑选指定字段
     *
     * @param list<string> $fields 字段名列表
     */
    public function only(array $fields): array
    {
        return array_intersect_key($this->validatedData, array_flip($fields));
    }

    /**
     * 从通过的数据中剔除指定字段
     *
     * @param list<string> $fields 字段名列表
     */
    public function except(array $fields): array
    {
        return array_diff_key($this->validatedData, array_flip($fields));
    }

    /**
     * 转换为数组结构，便于直接作为接口响应体
     *
     * @return array{valid: bool, data: array, errors: array}
     */
    public function toArray(): array
    {
        return [
            'valid'  => $this->valid,
            'data'   => $this->validatedData,
            'errors' => $this->errors,
        ];
    }

    /**
     * 出错字段数量
     */
    #[\Override]
    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * JSON 序列化结构
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

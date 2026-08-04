<?php

declare(strict_types=1);

namespace Kode\Validation\Rule\Concern;

/**
 * 字段存在性判断辅助，协程安全（纯函数，无状态）
 *
 * 供 present / missing / filled 等需要区分"键不存在"与"值为空"的规则复用。
 */
trait AccessesFieldTrait
{
    /**
     * 判断字段键是否存在于数据中（支持点号嵌套路径）
     *
     * @param array  $data  完整的待验证数据
     * @param string $field 字段名，支持 user.profile.name 形式
     */
    protected function fieldExists(array $data, string $field): bool
    {
        if (!str_contains($field, '.')) {
            return array_key_exists($field, $data);
        }

        $cursor = $data;

        foreach (explode('.', $field) as $key) {
            if (!is_array($cursor) || !array_key_exists($key, $cursor)) {
                return false;
            }
            $cursor = $cursor[$key];
        }

        return true;
    }

    /**
     * 判断值是否视为"空"
     *
     * null、空字符串、纯空白字符串与空数组均视为空。
     */
    protected function isEmptyValue(mixed $value): bool
    {
        if ($value === null || $value === []) {
            return true;
        }

        return is_string($value) && trim($value) === '';
    }
}

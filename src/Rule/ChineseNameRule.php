<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 中文姓名验证规则，协程安全（无状态）
 *
 * 规则格式：chinese_name
 * 2-16 个汉字，允许少数民族姓名中的间隔号「·」，但不能位于首尾。
 */
class ChineseNameRule implements RuleInterface
{
    /**
     * 执行中文姓名验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数（此规则无参数）
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (!is_string($value)) {
            return 'chinese_name';
        }

        $pattern = '/^[\x{4e00}-\x{9fa5}]{1,15}(?:[·・][\x{4e00}-\x{9fa5}]{1,15})*$/u';

        if (preg_match($pattern, $value) !== 1) {
            return 'chinese_name';
        }

        $length = mb_strlen(str_replace(['·', '・'], '', $value));

        return $length >= 2 && $length <= 16 ? null : 'chinese_name';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'chinese_name';
    }
}

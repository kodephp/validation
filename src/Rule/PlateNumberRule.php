<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 中国大陆车牌号验证规则，协程安全（无状态）
 *
 * 规则格式：plate_number
 * 同时支持传统燃油车 7 位车牌与新能源 8 位车牌。
 */
class PlateNumberRule implements RuleInterface
{
    /**
     * 省份简称字符集
     */
    private const string PROVINCES = '京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼渝川贵云藏陕甘青宁新使领';

    /**
     * 执行车牌号验证
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
            return 'plate_number';
        }

        $plate = strtoupper(trim($value));
        $provinces = self::PROVINCES;

        // 新能源车牌：省份 + 字母 + 6 位（D/F 开头或结尾）
        $newEnergy = '/^[' . $provinces . '][A-Z](?:[DF][A-HJ-NP-Z0-9][0-9]{4}|[0-9]{5}[DF])$/u';
        // 传统车牌：省份 + 字母 + 5 位字母数字（排除 I、O）
        $ordinary = '/^[' . $provinces . '][A-Z][A-HJ-NP-Z0-9]{4}[A-HJ-NP-Z0-9挂学警港澳]$/u';

        if (preg_match($newEnergy, $plate) === 1 || preg_match($ordinary, $plate) === 1) {
            return null;
        }

        return 'plate_number';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'plate_number';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 中国大陆居民身份证验证规则，协程安全（无状态）
 *
 * 规则格式：id_card
 * 支持 18 位（带 ISO 7064:1983 MOD 11-2 校验位）与 15 位旧版号码。
 */
class IdCardRule implements RuleInterface
{
    /**
     * 加权因子
     */
    private const array WEIGHTS = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

    /**
     * 校验码字符表
     */
    private const array CHECK_CODES = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

    /**
     * 执行身份证号验证
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

        if (!is_scalar($value)) {
            return 'id_card';
        }

        $id = strtoupper(trim((string) $value));

        // 15 位旧版身份证：全数字且出生日期合法
        if (preg_match('/^\d{15}$/', $id) === 1) {
            return $this->isValidBirthday('19' . substr($id, 6, 6)) ? null : 'id_card';
        }

        if (preg_match('/^\d{17}[\dX]$/', $id) !== 1) {
            return 'id_card';
        }

        if (!$this->isValidBirthday(substr($id, 6, 8))) {
            return 'id_card';
        }

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += (int) $id[$i] * self::WEIGHTS[$i];
        }

        return self::CHECK_CODES[$sum % 11] === $id[17] ? null : 'id_card';
    }

    /**
     * 校验 Ymd 格式出生日期是否真实存在
     */
    private function isValidBirthday(string $ymd): bool
    {
        if (strlen($ymd) !== 8) {
            return false;
        }

        $year  = (int) substr($ymd, 0, 4);
        $month = (int) substr($ymd, 4, 2);
        $day   = (int) substr($ymd, 6, 2);

        return checkdate($month, $day, $year) && $year >= 1900;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'id_card';
    }
}

<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * 银行卡号验证规则，协程安全（无状态）
 *
 * 规则格式：bank_card
 * 校验 12-19 位纯数字并通过 Luhn 算法。
 */
class BankCardRule implements RuleInterface
{
    /**
     * 执行银行卡号验证
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
            return 'bank_card';
        }

        $number = str_replace([' ', '-'], '', (string) $value);

        if (preg_match('/^\d{12,19}$/', $number) !== 1) {
            return 'bank_card';
        }

        return $this->passesLuhn($number) ? null : 'bank_card';
    }

    /**
     * Luhn 校验算法
     */
    private function passesLuhn(string $number): bool
    {
        $sum = 0;
        $double = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];

            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double;
        }

        return $sum % 10 === 0;
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'bank_card';
    }
}

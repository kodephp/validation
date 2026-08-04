<?php

declare(strict_types=1);

namespace Kode\Validation\Rule;

/**
 * PHP 枚举验证规则，协程安全（无状态）
 *
 * 规则格式：enum:App\Enum\OrderStatus
 * 校验值能否转换为指定的 Backed Enum 成员；也接受枚举实例本身。
 */
class EnumRule implements RuleInterface
{
    /**
     * 执行枚举验证
     *
     * @param string $field  字段名
     * @param mixed  $value  字段值
     * @param array  $params 规则参数，第一个元素为枚举类名
     * @param array  $data   完整的待验证数据
     * @return string|null 验证失败返回规则名，通过返回 null
     */
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $enumClass = ltrim((string) ($params[0] ?? ''), '\\');

        if ($enumClass === '' || !enum_exists($enumClass)) {
            return 'enum';
        }

        if ($value instanceof \UnitEnum) {
            return $value instanceof $enumClass ? null : 'enum';
        }

        if (!is_subclass_of($enumClass, \BackedEnum::class)) {
            return 'enum';
        }

        /** @var class-string<\BackedEnum> $enumClass */
        $reflection = new \ReflectionEnum($enumClass);
        $backingType = (string) $reflection->getBackingType();

        if ($backingType === 'int') {
            if (!is_int($value) && !(is_string($value) && preg_match('/^-?\d+$/', $value) === 1)) {
                return 'enum';
            }
            return $enumClass::tryFrom((int) $value) !== null ? null : 'enum';
        }

        if (!is_scalar($value)) {
            return 'enum';
        }

        return $enumClass::tryFrom((string) $value) !== null ? null : 'enum';
    }

    /**
     * 获取规则名称
     */
    #[\Override]
    public function getName(): string
    {
        return 'enum';
    }
}

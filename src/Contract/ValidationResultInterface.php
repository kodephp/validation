<?php

declare(strict_types=1);

namespace Kode\Validation\Contract;

/**
 * 验证结果接口，协程安全
 *
 * 表示一次验证操作的最终结果。实现类不可变，一旦创建不允许修改数据。
 */
interface ValidationResultInterface
{
    /**
     * 判断验证是否通过
     *
     * @return bool true 表示所有规则都通过，false 表示存在错误
     */
    public function isValid(): bool;

    /**
     * 获取所有验证错误
     *
     * @return array 格式 ['字段名' => ['规则名' => '错误消息', ...], ...]
     */
    public function errors(): array;

    /**
     * 获取通过验证的数据
     *
     * 只包含验证通过的字段，存在错误的字段不会出现在此数组中。
     *
     * @return array 键值对，仅包含验证通过的字段
     */
    public function validatedData(): array;
}

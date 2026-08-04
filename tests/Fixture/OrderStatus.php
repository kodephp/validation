<?php

declare(strict_types=1);

namespace Kode\Validation\Tests\Fixture;

/**
 * 字符串枚举测试夹具
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}

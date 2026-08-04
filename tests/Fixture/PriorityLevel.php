<?php

declare(strict_types=1);

namespace Kode\Validation\Tests\Fixture;

/**
 * 整型枚举测试夹具
 */
enum PriorityLevel: int
{
    case Low = 1;
    case Normal = 5;
    case High = 9;
}

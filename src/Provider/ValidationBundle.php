<?php

declare(strict_types=1);

namespace Kode\Validation\Provider;

use Kode\Validation\Contract\ValidatorInterface;
use Kode\Validation\Validator;

/**
 * 服务提供者，协程安全
 *
 * 用于与 kode/di 容器集成，注册验证器绑定。
 * 如果项目未使用 kode/di，可忽略此类，直接 new Validator() 即可。
 */
class ValidationBundle
{
    /**
     * 注册服务绑定
     *
     * @param object $app 容器实例（kode/di Container）
     */
    public function register(object $app): void
    {
        if (method_exists($app, 'bind')) {
            $app->bind(ValidatorInterface::class, static fn (): Validator => Validator::create());
        }
    }

    /**
     * 启动服务（可在此发布配置文件）
     */
    public function boot(): void
    {
        // 预留：配置文件发布逻辑
    }
}

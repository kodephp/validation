<?php

declare(strict_types=1);

namespace Kode\Validation\Tests\Feature;

use Kode\Validation\Exception\ValidationException;
use Kode\Validation\Helper\ValidationHelper;
use Kode\Validation\Trait\ValidatesRequests;
use Kode\Validation\ValidationResult;
use Kode\Validation\Validator;
use PHPUnit\Framework\TestCase;
use Fiber;

/**
 * 框架集成测试
 *
 * 验证包在 Controller、Service、Model 等场景下的可用性，
 * 以及多进程、多线程、协程并发安全性。
 */
class FrameworkIntegrationTest extends TestCase
{
    // ==================== ValidatesRequests Trait ====================

    public function testTrait验证请求成功(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $result = $controller->validateRequest(
            ['name' => '张三', 'email' => 'test@example.com'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
    }

    public function testTrait验证请求失败(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $result = $controller->validateRequest(
            ['name' => '', 'email' => 'invalid'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('name', $result->errors());
        $this->assertArrayHasKey('email', $result->errors());
    }

    public function testTrait验证抛出异常(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $this->expectException(ValidationException::class);

        $controller->validateThrows(
            ['name' => ''],
            ['name' => 'required|min:2']
        );
    }

    public function testTrait验证抛出成功返回数据(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $data = $controller->validateThrows(
            ['name' => '张三', 'email' => 'test@example.com'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        $this->assertSame('张三', $data['name']);
        $this->assertSame('test@example.com', $data['email']);
    }

    public function testTrait验证返回结构化结果(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $result = $controller->validateWithResult(
            ['name' => '张三'],
            ['name' => 'required|min:2']
        );

        $this->assertTrue($result['valid']);
        $this->assertArrayHasKey('name', $result['data']);
        $this->assertEmpty($result['errors']);
    }

    public function testTrait验证返回结构化结果失败(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $result = $controller->validateWithResult(
            ['name' => ''],
            ['name' => 'required|min:2']
        );

        $this->assertFalse($result['valid']);
        $this->assertEmpty($result['data']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testTrait自定义验证器(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $customValidator = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');
        $customValidator->stopOnFirstFailure();

        $controller->setValidator($customValidator);

        $result = $controller->validateRequest(
            ['name' => '', 'email' => 'bad'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        // 首错即停，只有 name 的错误
        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->errors());
    }

    // ==================== ValidationHelper ====================

    public function test静态助手快速验证(): void
    {
        $result = ValidationHelper::check(
            ['name' => '张三'],
            ['name' => 'required|min:2']
        );

        $this->assertTrue($result->isValid());
    }

    public function test静态助手返回验证通过数据(): void
    {
        $data = ValidationHelper::validated(
            ['name' => '张三', 'age' => 25],
            ['name' => 'required|min:2', 'age' => 'required|numeric']
        );

        $this->assertIsArray($data);
        $this->assertSame('张三', $data['name']);
        $this->assertSame(25, $data['age']);
    }

    public function test静态助手返回null验证失败(): void
    {
        $data = ValidationHelper::validated(
            ['name' => ''],
            ['name' => 'required|min:2']
        );

        $this->assertNull($data);
    }

    // ==================== Validator::validateThrows ====================

    public function testValidator直接抛出异常方法(): void
    {
        $validator = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');

        $data = $validator->validateThrows(
            ['name' => '张三', 'email' => 'test@example.com'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        $this->assertSame('张三', $data['name']);
    }

    public function testValidator直接抛出异常方法失败(): void
    {
        $validator = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');

        $this->expectException(ValidationException::class);

        $validator->validateThrows(
            [],
            ['name' => 'required']
        );
    }

    // ==================== 控制器场景模拟 ====================

    public function test控制器注册场景(): void
    {
        $controller = new class {
            use ValidatesRequests;

            public function register(array $request): array
            {
                try {
                    $validated = $this->validateThrows($request, [
                        'username' => 'required|min:3|max:20|alpha_num',
                        'email'    => 'required|email',
                        'password' => 'required|min:6|confirmed',
                    ]);

                    return ['success' => true, 'user' => $validated];
                } catch (ValidationException $e) {
                    return ['success' => false, 'errors' => $e->errors()];
                }
            }
        };

        // 成功场景
        $response = $controller->register([
            'username'              => 'zhangsan',
            'email'                 => 'zhangsan@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $this->assertTrue($response['success']);

        // 失败场景
        $response = $controller->register([
            'username' => 'ab',
            'email'    => 'invalid',
        ]);

        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('username', $response['errors']);
    }

    // ==================== Service 层场景模拟 ====================

    public function testService层验证场景(): void
    {
        $service = new class {
            use ValidatesRequests;

            public function createUser(array $data): array
            {
                $result = $this->validateWithResult($data, [
                    'name'  => 'required|min:2|max:50',
                    'email' => 'required|email|max:100',
                    'role'  => 'required|in:user,admin,moderator',
                ]);

                if (!$result['valid']) {
                    return $result;
                }

                // 模拟数据库操作
                $result['data']['id'] = 1;
                $result['data']['created_at'] = date('Y-m-d H:i:s');

                return $result;
            }
        };

        $result = $service->createUser([
            'name'  => '张三',
            'email' => 'test@example.com',
            'role'  => 'user',
        ]);

        $this->assertTrue($result['valid']);
        $this->assertSame(1, $result['data']['id']);
    }

    // ==================== 多协程并发测试 ====================

    public function test多协程并发trait验证互不干扰(): void
    {
        $concurrency = 20;
        $fibers = [];

        for ($i = 1; $i <= $concurrency; $i++) {
            $id = $i;
            $fibers[$id] = new Fiber(function () use ($id) {
                $service = new class {
                    use ValidatesRequests;
                };

                $result = $service->validateRequest(
                    ['user_id' => $id, 'name' => "User{$id}", 'email' => "user{$id}@example.com"],
                    ['user_id' => 'required|min:1', 'name' => 'required|alpha_num|min:2', 'email' => 'required|email']
                );

                return [
                    'id'       => $id,
                    'is_valid' => $result->isValid(),
                    'data'     => $result->validatedData(),
                ];
            });

            $fibers[$id]->start();
        }

        $results = [];
        foreach ($fibers as $id => $fiber) {
            while ($fiber->isSuspended()) {
                $fiber->resume();
            }
            $results[$id] = $fiber->getReturn();
        }

        // 断言所有协程结果独立且正确
        foreach ($results as $id => $result) {
            $this->assertTrue($result['is_valid'], "协程 {$id} 验证应通过");
            $this->assertSame($id, $result['data']['user_id'], "协程 {$id} user_id 应与输入一致");
            $this->assertSame("User{$id}", $result['data']['name'], "协程 {$id} name 应与输入一致");
        }

        $this->assertCount($concurrency, $results);
    }

    public function test多协程并发helper验证互不干扰(): void
    {
        $concurrency = 15;
        $fibers = [];

        for ($i = 1; $i <= $concurrency; $i++) {
            $fibers[$i] = new Fiber(function () use ($i) {
                $data = ValidationHelper::validated(
                    ['num' => $i * 2],
                    ['num' => 'required|numeric|min:2']
                );

                return [
                    'id'   => $i,
                    'data' => $data,
                ];
            });

            $fibers[$i]->start();
        }

        foreach ($fibers as $i => $fiber) {
            while ($fiber->isSuspended()) {
                $fiber->resume();
            }
            $result = $fiber->getReturn();
            $this->assertIsArray($result['data'], "协程 {$i} 应返回数据");
            $this->assertSame($i * 2, $result['data']['num'], "协程 {$i} num 应为 " . ($i * 2));
        }
    }

    // ==================== 多实例并发安全 ====================

    public function test多实例并发验证隔离(): void
    {
        $v1 = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');
        $v2 = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');
        $v3 = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');

        $f1 = new Fiber(function () use ($v1) {
            return $v1->validate(['a' => ''], ['a' => 'required'])->errors();
        });
        $f2 = new Fiber(function () use ($v2) {
            return $v2->validate(['b' => 'hello'], ['b' => 'required|email'])->errors();
        });
        $f3 = new Fiber(function () use ($v3) {
            return $v3->validate(['c' => 'test@example.com'], ['c' => 'required|email'])->isValid();
        });

        $f1->start();
        $f2->start();
        $f3->start();

        while ($f1->isSuspended()) $f1->resume();
        while ($f2->isSuspended()) $f2->resume();
        while ($f3->isSuspended()) $f3->resume();

        $errors1 = $f1->getReturn();
        $errors2 = $f2->getReturn();
        $isValid3 = $f3->getReturn();

        $this->assertArrayHasKey('required', $errors1['a']);
        $this->assertArrayHasKey('email', $errors2['b']);
        $this->assertTrue($isValid3);
    }

    // ==================== Controller trait 协程并发 ====================

    public function test同一trait实例多次验证互不干扰(): void
    {
        $controller = new class {
            use ValidatesRequests;
        };

        $r1 = $controller->validateRequest(['x' => ''], ['x' => 'required']);
        $r2 = $controller->validateRequest(['x' => 'ok'], ['x' => 'required']);
        $r3 = $controller->validateRequest(['x' => 'ok', 'y' => 'bad'], ['y' => 'email']);

        $this->assertFalse($r1->isValid());
        $this->assertTrue($r2->isValid());
        $this->assertFalse($r3->isValid());
        $this->assertArrayHasKey('y', $r3->errors());
    }
}

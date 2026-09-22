<?php

declare(strict_types=1);

namespace Kode\Validation\Tests\Feature;

use Kode\Validation\Helper\ValidationHelper;
use Kode\Validation\Validator;
use PHPUnit\Framework\TestCase;
use Fiber;

/**
 * 协程安全测试
 *
 * 通过 Fiber 并发执行多个验证操作，验证结果互不干扰，无数据交叉污染。
 */
class ContextSafetyTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');
    }

    /**
     * 测试多协程并发验证数据隔离
     *
     * 创建 10 个 Fiber 同时验证不同的数据，断言每个协程的结果完全独立。
     */
    public function test多协程并发验证数据不交叉污染(): void
    {
        $concurrency = 10;
        $fibers = [];
        $expectedResults = [];

        // 准备 10 组不同的验证数据
        for ($i = 1; $i <= $concurrency; $i++) {
            $data = [
                'user_id' => $i,
                'name'    => "用户{$i}",
                'email'   => "user{$i}@example.com",
            ];

            $expectedResults[$i] = [
                'data'   => $data,
                'should_pass' => true,
            ];
        }

        // 创建 10 个 Fiber 并发验证
        foreach ($expectedResults as $id => $info) {
            $fibers[$id] = new Fiber(function () use ($id, $info, $expectedResults) {
                $result = $this->validator->validate($info['data'], [
                    'user_id' => 'required|min:1',
                    'name'    => 'required|min:2|max:10',
                    'email'   => 'required|email',
                ]);

                // 在 Fiber 内部暂存结果，通过 Fiber 的返回值传出
                return [
                    'id'         => $id,
                    'is_valid'   => $result->isValid(),
                    'errors'     => $result->errors(),
                    'data'       => $result->validatedData(),
                    'expected'   => $info,
                ];
            });

            $fibers[$id]->start();
        }

        // 等待所有 Fiber 完成并收集结果
        $results = [];
        foreach ($fibers as $id => $fiber) {
            while ($fiber->isSuspended()) {
                $fiber->resume();
            }
            $results[$id] = $fiber->getReturn();
        }

        // 断言每个协程的结果正确且隔离
        foreach ($results as $id => $result) {
            $this->assertTrue(
                $result['is_valid'],
                "协程 {$id} 验证应通过"
            );
            $this->assertSame(
                [],
                $result['errors'],
                "协程 {$id} 不应有错误"
            );
            $this->assertSame(
                $result['expected']['data'],
                $result['data'],
                "协程 {$id} 的数据应与输入一致"
            );
        }

        // 确保结果数量正确
        $this->assertCount($concurrency, $results);
    }

    /**
     * 测试多协程验证失败场景的隔离
     *
     * 部分协程验证通过、部分失败，确保失败的消息不会交叉。
     */
    public function test多协程混合验证结果隔离(): void
    {
        $testCases = [
            1 => [
                'data'        => ['name' => '张三', 'email' => 'zhangsan@example.com'],
                'should_pass' => true,
            ],
            2 => [
                'data'        => ['name' => '', 'email' => 'lisi@example.com'],
                'should_pass' => false,
                'error_field' => 'name',
                'error_rule'  => 'required',
            ],
            3 => [
                'data'        => ['name' => '王五', 'email' => 'invalid'],
                'should_pass' => false,
                'error_field' => 'email',
                'error_rule'  => 'email',
            ],
            4 => [
                'data'        => ['name' => '', 'email' => 'invalid'],
                'should_pass' => false,
                'error_field' => 'name',
            ],
            5 => [
                'data'        => ['name' => '赵六', 'email' => 'zhaoliu@example.com'],
                'should_pass' => true,
            ],
        ];

        $fibers = [];

        foreach ($testCases as $id => $info) {
            $fibers[$id] = new Fiber(function () use ($info) {
                $result = $this->validator->validate($info['data'], [
                    'name'  => 'required|min:2|max:20',
                    'email' => 'required|email',
                ]);

                return [
                    'is_valid' => $result->isValid(),
                    'errors'   => $result->errors(),
                    'data'     => $result->validatedData(),
                    'expected' => $info,
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

        foreach ($results as $id => $result) {
            $expected = $testCases[$id];

            if ($expected['should_pass']) {
                $this->assertTrue(
                    $result['is_valid'],
                    "协程 {$id} 验证应通过"
                );
                $this->assertSame([], $result['errors'], "协程 {$id} 不应有错误");
            } else {
                $this->assertFalse(
                    $result['is_valid'],
                    "协程 {$id} 验证应失败"
                );
                $this->assertArrayHasKey(
                    $expected['error_field'],
                    $result['errors'],
                    "协程 {$id} 应在字段 {$expected['error_field']} 有错误"
                );
            }
        }
    }

    /**
     * 测试同一验证器实例多次调用不相互影响
     */
    public function test同一实例多次验证互不干扰(): void
    {
        $result1 = $this->validator->validate(
            ['name' => '张三', 'email' => 'zhangsan@example.com'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        $result2 = $this->validator->validate(
            ['name' => '', 'email' => 'bad'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        $result3 = $this->validator->validate(
            ['name' => '李四', 'email' => 'lisi@example.com'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        // 第一次验证：应通过
        $this->assertTrue($result1->isValid());
        $this->assertSame([], $result1->errors());

        // 第二次验证：应失败
        $this->assertFalse($result2->isValid());
        $this->assertArrayHasKey('name', $result2->errors());
        $this->assertArrayHasKey('email', $result2->errors());

        // 第三次验证：应通过（证明第二次的失败未影响实例状态）
        $this->assertTrue($result3->isValid());
        $this->assertSame([], $result3->errors());
    }

    /**
     * 测试自定义规则在并发中不冲突
     */
    public function test自定义规则并发不冲突(): void
    {
        // 添加一个使用局部计数器模拟的自定义规则
        $this->validator->addRule('check_id', function (string $field, mixed $value, array $params, array $data): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            // 使用局部变量，不依赖外部状态
            $minId = (int) ($params[0] ?? 1);
            if ((int) $value < $minId) {
                return 'check_id';
            }
            return null;
        });

        $fibers = [];
        for ($i = 1; $i <= 5; $i++) {
            $userId = $i;
            $fibers[$i] = new Fiber(function () use ($userId) {
                $result = $this->validator->validate(
                    ['user_id' => $userId],
                    ['user_id' => 'required|check_id:1']
                );

                return [
                    'id'       => $userId,
                    'is_valid' => $result->isValid(),
                ];
            });

            $fibers[$i]->start();
        }

        foreach ($fibers as $i => $fiber) {
            while ($fiber->isSuspended()) {
                $fiber->resume();
            }
            $result = $fiber->getReturn();
            $this->assertTrue(
                $result['is_valid'],
                "协程 {$i} 自定义规则验证应通过"
            );
        }
    }

    /**
     * 共享验证器实例在「真正交错」的协程下结果隔离。
     *
     * 上面的用例里 Fiber 从不让出，等价于顺序执行，证不了并发隔离；这里让自定义规则在
     * 验证进行到一半时 suspend，两条验证确实同时在飞，才能检验实例上有没有请求级状态。
     */
    public function test共享实例在真实交错的协程下结果隔离(): void
    {
        $entered = [];
        $validator = Validator::create();
        $validator->addRule('yield', static function (string $field, mixed $value, array $params, array $data) use (&$entered): ?string {
            $entered[] = $params[0] ?? '?';
            // 把执行权交给另一条协程：此刻本次验证尚未结束，中间状态若挂在实例上就会串味
            Fiber::suspend();

            return null;
        });

        ValidationHelper::useInstance($validator);

        try {
            $passing = new Fiber(static fn () => ValidationHelper::check(
                ['name' => '张三'],
                ['name' => 'yield:a|required|min:2']
            ));
            $failing = new Fiber(static fn () => ValidationHelper::check(
                ['name' => ''],
                ['name' => 'yield:b|required']
            ));

            $passing->start();
            $failing->start();

            self::assertTrue($passing->isSuspended(), '前置条件：两条验证应同时在飞');
            self::assertTrue($failing->isSuspended(), '前置条件：两条验证应同时在飞');
            self::assertSame(['a', 'b'], $entered, '规则应交错入场，而非各自跑完');

            // 故意先跑完「会失败」的那条：中间状态若跨调用共享，它的错误就会漏进后完成的验证里
            $failing->resume();
            $failResult = $failing->getReturn();

            $passing->resume();
            $passResult = $passing->getReturn();
        } finally {
            ValidationHelper::reset();
        }

        self::assertTrue($passResult->isValid(), '另一条协程的失败不应污染本次验证结果');
        self::assertSame([], $passResult->errors());
        self::assertFalse($failResult->isValid());
        self::assertSame(['name'], array_keys($failResult->errors()));
        self::assertCount(1, $failResult->errors()['name'], '只应收到自己那条 required 错误');
    }

    /**
     * useInstance 换的是进程级静态实例，reset 负责复原。
     *
     * 常驻多进程 worker 里，一次 useInstance 会作用到之后该进程内的所有请求与协程，
     * 因此它只适合启动期装配或单元测试；业务代码要按请求定制请用 Validator::create()。
     */
    public function testUseInstance为进程级全局且Reset复原默认实例(): void
    {
        $data = ['a' => '', 'b' => ''];
        $rules = ['a' => 'required', 'b' => 'required'];

        self::assertCount(2, ValidationHelper::check($data, $rules)->errors(), '默认实例应收集全部字段错误');

        ValidationHelper::useInstance(Validator::create()->stopOnFirstFailure());

        try {
            self::assertCount(1, ValidationHelper::check($data, $rules)->errors(), '共享实例已被整体替换');
        } finally {
            ValidationHelper::reset();
        }

        self::assertCount(2, ValidationHelper::check($data, $rules)->errors(), 'reset 后应回到默认共享实例');
    }
}

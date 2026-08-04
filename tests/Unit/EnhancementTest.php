<?php

declare(strict_types=1);

namespace Kode\Validation\Tests\Unit;

use Kode\Validation\Exception\ValidationException;
use Kode\Validation\Helper\ValidationHelper;
use Kode\Validation\ValidationResult;
use Kode\Validation\Validator;
use PHPUnit\Framework\TestCase;

/**
 * v1.9.0 增强特性单元测试
 *
 * 覆盖 bail 短路、后置回调、字段显示名、多级通配符、正则参数修复、
 * 结果对象扩展 API、规则惰性加载与解析缓存。
 */
class EnhancementTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = Validator::create();
    }

    // ==================== 版本与基线 ====================

    public function test版本号为1_9_0(): void
    {
        $this->assertSame('1.9.0', Validator::VERSION);
    }

    public function testPHP版本基线为8_3(): void
    {
        $this->assertGreaterThanOrEqual(80300, PHP_VERSION_ID);

        $composer = json_decode(
            (string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame('>=8.3', $composer['require']['php']);
        $this->assertSame('1.9.0', $composer['version']);
    }

    // ==================== bail 短路 ====================

    public function testBail规则在首个错误后停止(): void
    {
        $withoutBail = $this->validator->validate(
            ['email' => 'x'],
            ['email' => 'min:5|email']
        );
        $this->assertCount(1, $withoutBail);
        $this->assertSame(['min', 'email'], $withoutBail->failedRules()['email']);

        $withBail = $this->validator->validate(
            ['email' => 'x'],
            ['email' => 'bail|min:5|email']
        );
        $this->assertSame(['min'], $withBail->failedRules()['email']);
    }

    public function testBail不影响其他字段(): void
    {
        $result = $this->validator->validate(
            ['a' => 'x', 'b' => 'y'],
            ['a' => 'bail|min:5|email', 'b' => 'min:5']
        );

        $this->assertCount(2, $result);
    }

    // ==================== 后置回调 ====================

    public function test后置回调可改写结果(): void
    {
        $validator = Validator::create()->afterValidation(
            static function (ValidationResult $result, array $data): ?ValidationResult {
                if (($data['name'] ?? '') === '违禁词') {
                    return new ValidationResult(
                        false,
                        array_merge($result->errors(), ['name' => ['blocked' => '名称包含违禁词']]),
                        []
                    );
                }
                return null;
            }
        );

        $ok = $validator->validate(['name' => '张三'], ['name' => 'required']);
        $this->assertTrue($ok->isValid());

        $blocked = $validator->validate(['name' => '违禁词'], ['name' => 'required']);
        $this->assertFalse($blocked->isValid());
        $this->assertSame('名称包含违禁词', $blocked->first('name'));
    }

    public function test前置与后置回调可同时生效(): void
    {
        $validator = Validator::create()
            ->beforeValidation(static function (array $data, array $rules): array {
                $data['name'] = trim((string) ($data['name'] ?? ''));
                return [$data, $rules];
            })
            ->afterValidation(static fn (ValidationResult $r): ?ValidationResult => null);

        $result = $validator->validate(['name' => '  张三  '], ['name' => 'required|length:2']);

        $this->assertTrue($result->isValid());
        $this->assertSame('张三', $result->validatedData()['name']);
    }

    // ==================== 字段显示名 ====================

    public function test全局字段显示名映射(): void
    {
        $validator = Validator::create()->setAttributeNames([
            'email'        => '邮箱地址',
            'users.*.name' => '用户姓名',
        ]);

        $result = $validator->validate(['email' => 'bad'], ['email' => 'email']);
        $this->assertSame('邮箱地址 不是有效的邮箱地址', $result->first('email'));

        $nested = $validator->validate(
            ['users' => [['name' => '']]],
            ['users.*.name' => 'required']
        );
        $this->assertSame('用户姓名 不能为空', $nested->first('users.0.name'));
    }

    public function test通配符自定义消息(): void
    {
        $result = $this->validator->validate(
            ['users' => [['age' => 'abc']]],
            ['users.*.age' => 'numeric'],
            ['users.*.age.numeric' => '年龄必须为数字']
        );

        $this->assertSame('年龄必须为数字', $result->first('users.0.age'));
    }

    public function test新增占位符(): void
    {
        $validator = Validator::create()->addMessages([
            'between' => ':field 取值 :value 不在 :params 范围内',
        ]);

        $result = $validator->validate(['score' => 120], ['score' => 'between:0,100']);

        $this->assertSame('score 取值 120 不在 0, 100 范围内', $result->first('score'));
    }

    // ==================== 多级通配符 ====================

    public function test多级通配符展开(): void
    {
        $data = [
            'orders' => [
                ['items' => [['sku' => 'A1'], ['sku' => '']]],
                ['items' => [['sku' => 'B1']]],
            ],
        ];

        $result = $this->validator->validate($data, ['orders.*.items.*.sku' => 'required']);

        $this->assertFalse($result->isValid());
        $this->assertSame(['orders.0.items.1.sku'], $result->invalidFields());
    }

    public function test通配符字段也遵守首错即停(): void
    {
        $validator = Validator::create()->stopOnFirstFailure();

        $result = $validator->validate(
            ['users' => [['name' => ''], ['name' => '']]],
            ['users.*.name' => 'required']
        );

        $this->assertCount(1, $result);
    }

    // ==================== 参数解析修复 ====================

    public function test正则参数不再被逗号截断(): void
    {
        $pass = $this->validator->validate(['code' => '1234'], ['code' => 'regex:/^\d{3,5}$/']);
        $this->assertTrue($pass->isValid());

        $fail = $this->validator->validate(['code' => '12'], ['code' => 'regex:/^\d{3,5}$/']);
        $this->assertFalse($fail->isValid());
    }

    public function test日期格式参数保留空格与冒号(): void
    {
        $result = $this->validator->validate(
            ['at' => '2026-08-04 23:15:08'],
            ['at' => 'date_format:Y-m-d H:i:s']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 结果对象扩展 ====================

    public function test结果对象扩展方法(): void
    {
        $result = $this->validator->validate(
            ['name' => '', 'email' => 'bad', 'age' => 20],
            ['name' => 'required', 'email' => 'email', 'age' => 'numeric']
        );

        $this->assertTrue($result->fails());
        $this->assertTrue($result->has('name'));
        $this->assertFalse($result->has('age'));
        $this->assertCount(2, $result);
        $this->assertSame(['name', 'email'], $result->invalidFields());
        $this->assertSame(['required'], $result->failedRules()['name']);
        $this->assertCount(2, $result->flatten());
        $this->assertSame($result->first(), $result->first('name'));
        $this->assertSame([], $result->get('age'));
        $this->assertSame(['name' => ['name 不能为空']], array_slice($result->messages(), 0, 1));

        $this->assertSame(['age' => 20], $result->only(['age']));
        $this->assertSame([], $result->except(['age']));

        $array = $result->toArray();
        $this->assertFalse($array['valid']);
        $this->assertArrayHasKey('errors', $array);
        $this->assertSame($array, $result->jsonSerialize());
        $this->assertJson(json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test未提交字段不会混入通过数据(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三'],
            ['name' => 'required', 'nickname' => 'string']
        );

        $this->assertTrue($result->isValid());
        $this->assertSame(['name' => '张三'], $result->validatedData());
    }

    public function test异常携带首条错误消息(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('name 不能为空');

        try {
            $this->validator->validateThrows(['name' => ''], ['name' => 'required']);
        } catch (ValidationException $e) {
            $this->assertSame('name 不能为空', $e->first());
            $this->assertSame(['name' => ['name 不能为空']], $e->messages());
            throw $e;
        }
    }

    // ==================== 规则注册与缓存 ====================

    public function test规则注册查询接口(): void
    {
        $this->assertTrue($this->validator->hasRule('required'));
        $this->assertTrue($this->validator->hasRule('bail'));
        $this->assertTrue($this->validator->hasRule('closure'));
        $this->assertFalse($this->validator->hasRule('不存在的规则'));

        $names = $this->validator->ruleNames();
        $this->assertGreaterThanOrEqual(90, count($names));
        $this->assertSame($names, array_unique($names));
    }

    public function test批量注册自定义规则(): void
    {
        $validator = Validator::create()->addRules([
            'even' => static fn (string $f, mixed $v): ?string => is_numeric($v) && (int) $v % 2 === 0 ? null : 'even',
            'odd'  => static fn (string $f, mixed $v): ?string => is_numeric($v) && (int) $v % 2 === 1 ? null : 'odd',
        ]);

        $this->assertTrue($validator->hasRule('even'));
        $this->assertTrue($validator->validate(['n' => 4], ['n' => 'even'])->isValid());
        $this->assertFalse($validator->validate(['n' => 4], ['n' => 'odd'])->isValid());
    }

    public function test自定义规则可覆盖内置规则(): void
    {
        $validator = Validator::create();
        $validator->addRule('email', static fn (): ?string => null);

        $this->assertTrue($validator->validate(['e' => '不是邮箱'], ['e' => 'email'])->isValid());

        // 覆盖仅作用于当前实例，不污染共享池
        $this->assertFalse(Validator::create()->validate(['e' => '不是邮箱'], ['e' => 'email'])->isValid());
    }

    public function test闭包规则复用不重复注册(): void
    {
        $closure = static fn (string $f, mixed $v): ?string => $v === 'ok' ? null : '值必须为 ok';

        $validator = Validator::create();

        $this->assertTrue($validator->validate(['a' => 'ok'], ['a' => [$closure]])->isValid());

        $failed = $validator->validate(['a' => 'no'], ['a' => [$closure]]);
        $this->assertFalse($failed->isValid());
        $this->assertSame('值必须为 ok', $failed->first('a'));
    }

    public function test规则解析缓存不影响结果(): void
    {
        Validator::flushCaches();

        $first = $this->validator->validate(['n' => 'abc'], ['n' => 'required|min:5']);
        $second = $this->validator->validate(['n' => 'abcdef'], ['n' => 'required|min:5']);

        $this->assertFalse($first->isValid());
        $this->assertTrue($second->isValid());
    }

    public function test共享规则池跨实例复用(): void
    {
        Validator::flushCaches();

        $a = Validator::create();
        $b = Validator::create();

        $this->assertTrue($a->validate(['e' => 'a@b.com'], ['e' => 'email'])->isValid());
        $this->assertTrue($b->validate(['e' => 'a@b.com'], ['e' => 'email'])->isValid());
    }

    // ==================== 快捷方式 ====================

    public function test快捷方法(): void
    {
        ValidationHelper::reset();

        $this->assertTrue(ValidationHelper::passes(['n' => 'abc'], ['n' => 'required']));
        $this->assertFalse(ValidationHelper::passes(['n' => ''], ['n' => 'required']));
        $this->assertNull(ValidationHelper::firstError(['n' => 'abc'], ['n' => 'required']));
        $this->assertSame('n 不能为空', ValidationHelper::firstError(['n' => ''], ['n' => 'required']));

        ValidationHelper::reset();
    }

    public function test工厂方法加载内置中文消息(): void
    {
        $result = Validator::create()->validate(['email' => 'bad'], ['email' => 'email']);

        $this->assertSame('email 不是有效的邮箱地址', $result->first());

        $bare = new Validator();
        $this->assertSame('email', $bare->validate(['email' => 'bad'], ['email' => 'email'])->first());
    }
}

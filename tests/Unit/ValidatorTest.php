<?php

declare(strict_types=1);

namespace Kode\Validation\Tests\Unit;

use Kode\Validation\Validator;
use PHPUnit\Framework\TestCase;

/**
 * 验证器单元测试
 *
 * 覆盖所有内置规则、自定义消息、字段别名、组合验证等场景。
 */
class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');
    }

    // ==================== 必填规则 ====================

    public function test必填规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三'],
            ['name' => 'required']
        );

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->errors());
        $this->assertSame(['name' => '张三'], $result->validatedData());
    }

    public function test必填规则验证失败null值(): void
    {
        $result = $this->validator->validate(
            ['name' => null],
            ['name' => 'required']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('name', $result->errors());
        $this->assertArrayHasKey('required', $result->errors()['name']);
    }

    public function test必填规则验证失败空字符串(): void
    {
        $result = $this->validator->validate(
            ['name' => ''],
            ['name' => 'required']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('required', $result->errors()['name']);
    }

    public function test必填规则验证失败空数组(): void
    {
        $result = $this->validator->validate(
            ['tags' => []],
            ['tags' => 'required']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('required', $result->errors()['tags']);
    }

    public function test必填规则验证通过数字零(): void
    {
        $result = $this->validator->validate(
            ['age' => 0],
            ['age' => 'required']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 邮箱规则 ====================

    public function test邮箱规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['email' => 'user@example.com'],
            ['email' => 'email']
        );

        $this->assertTrue($result->isValid());
    }

    public function test邮箱规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['email' => 'not-an-email'],
            ['email' => 'email']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('email', $result->errors()['email']);
    }

    public function test邮箱规则跳过空值(): void
    {
        $result = $this->validator->validate(
            ['email' => ''],
            ['email' => 'email']
        );

        $this->assertTrue($result->isValid());
    }

    public function test邮箱规则跳过null值(): void
    {
        $result = $this->validator->validate(
            ['email' => null],
            ['email' => 'email']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 最小值规则 ====================

    public function test最小值规则数字通过(): void
    {
        $result = $this->validator->validate(
            ['age' => 18],
            ['age' => 'min:18']
        );

        $this->assertTrue($result->isValid());
    }

    public function test最小值规则数字失败(): void
    {
        $result = $this->validator->validate(
            ['age' => 15],
            ['age' => 'min:18']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('min', $result->errors()['age']);
    }

    public function test最小值规则字符串通过(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三'],
            ['name' => 'min:2']
        );

        $this->assertTrue($result->isValid());
    }

    public function test最小值规则字符串失败(): void
    {
        $result = $this->validator->validate(
            ['name' => '张'],
            ['name' => 'min:2']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('min', $result->errors()['name']);
    }

    public function test最小值规则跳过空值(): void
    {
        $result = $this->validator->validate(
            ['age' => null],
            ['age' => 'min:18']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 最大值规则 ====================

    public function test最大值规则数字通过(): void
    {
        $result = $this->validator->validate(
            ['score' => 100],
            ['score' => 'max:100']
        );

        $this->assertTrue($result->isValid());
    }

    public function test最大值规则数字失败(): void
    {
        $result = $this->validator->validate(
            ['score' => 150],
            ['score' => 'max:100']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('max', $result->errors()['score']);
    }

    public function test最大值规则字符串通过(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三'],
            ['name' => 'max:3']
        );

        $this->assertTrue($result->isValid());
    }

    public function test最大值规则字符串失败(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三李四'],
            ['name' => 'max:3']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('max', $result->errors()['name']);
    }

    public function test最大值规则跳过空值(): void
    {
        $result = $this->validator->validate(
            ['score' => null],
            ['score' => 'max:100']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 区间规则 ====================

    public function test区间规则数字通过(): void
    {
        $result = $this->validator->validate(
            ['age' => 30],
            ['age' => 'between:1,100']
        );

        $this->assertTrue($result->isValid());
    }

    public function test区间规则数字失败太小(): void
    {
        $result = $this->validator->validate(
            ['age' => 0],
            ['age' => 'between:1,100']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('between', $result->errors()['age']);
    }

    public function test区间规则数字失败太大(): void
    {
        $result = $this->validator->validate(
            ['age' => 200],
            ['age' => 'between:1,100']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('between', $result->errors()['age']);
    }

    public function test区间规则字符串通过(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三丰'],
            ['name' => 'between:2,5']
        );

        $this->assertTrue($result->isValid());
    }

    public function test区间规则字符串失败太短(): void
    {
        $result = $this->validator->validate(
            ['name' => '张'],
            ['name' => 'between:2,5']
        );

        $this->assertFalse($result->isValid());
    }

    public function test区间规则跳过空值(): void
    {
        $result = $this->validator->validate(
            ['age' => null],
            ['age' => 'between:1,100']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 枚举规则 ====================

    public function test枚举规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['status' => 'active'],
            ['status' => 'in:active,inactive,pending']
        );

        $this->assertTrue($result->isValid());
    }

    public function test枚举规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['status' => 'deleted'],
            ['status' => 'in:active,inactive,pending']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('in', $result->errors()['status']);
    }

    public function test枚举规则跳过空值(): void
    {
        $result = $this->validator->validate(
            ['status' => ''],
            ['status' => 'in:active,inactive']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 正则规则 ====================

    public function test正则规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['code' => 'ABC123'],
            ['code' => 'regex:/^[A-Z0-9]+$/']
        );

        $this->assertTrue($result->isValid());
    }

    public function test正则规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['code' => 'abc-123'],
            ['code' => 'regex:/^[A-Z0-9]+$/']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('regex', $result->errors()['code']);
    }

    public function test正则规则手机号验证(): void
    {
        $result = $this->validator->validate(
            ['phone' => '13800138000'],
            ['phone' => 'regex:/^1[3-9]\d{9}$/']
        );

        $this->assertTrue($result->isValid());
    }

    public function test正则规则跳过空值(): void
    {
        $result = $this->validator->validate(
            ['code' => null],
            ['code' => 'regex:/^[A-Z0-9]+$/']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== 确认规则 ====================

    public function test确认规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['password' => 'secret', 'password_confirmation' => 'secret'],
            ['password' => 'confirmed']
        );

        $this->assertTrue($result->isValid());
    }

    public function test确认规则验证失败不一致(): void
    {
        $result = $this->validator->validate(
            ['password' => 'secret', 'password_confirmation' => 'different'],
            ['password' => 'confirmed']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('confirmed', $result->errors()['password']);
    }

    public function test确认规则验证失败缺少确认字段(): void
    {
        $result = $this->validator->validate(
            ['password' => 'secret'],
            ['password' => 'confirmed']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('confirmed', $result->errors()['password']);
    }

    // ==================== 自定义错误消息 ====================

    public function test自定义错误消息(): void
    {
        $result = $this->validator->validate(
            ['name' => ''],
            ['name' => 'required'],
            ['name.required' => '姓名必须要填写哦']
        );

        $this->assertFalse($result->isValid());
        $this->assertSame('姓名必须要填写哦', $result->errors()['name']['required']);
    }

    public function test规则级别自定义消息(): void
    {
        $result = $this->validator->validate(
            ['email' => 'bad-email'],
            ['email' => 'email'],
            ['email.email' => '请输入正确的邮箱地址']
        );

        $this->assertFalse($result->isValid());
        $this->assertSame('请输入正确的邮箱地址', $result->errors()['email']['email']);
    }

    // ==================== 字段别名 ====================

    public function test字段别名(): void
    {
        $result = $this->validator->validate(
            ['user_email' => ''],
            ['user_email' => 'required'],
            ['user_email.attribute' => '用户邮箱']
        );

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('用户邮箱', $result->errors()['user_email']['required']);
    }

    public function test字段别名含占位模板(): void
    {
        $result = $this->validator->validate(
            ['age' => 15],
            ['age' => 'min:18'],
            ['age.attribute' => '年龄']
        );

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('年龄', $result->errors()['age']['min']);
        $this->assertStringContainsString('18', $result->errors()['age']['min']);
    }

    // ==================== 多规则组合验证 ====================

    public function test多规则组合验证全部通过(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三', 'email' => 'zhangsan@example.com', 'age' => 25],
            [
                'name'  => 'required|min:2|max:20',
                'email' => 'required|email',
                'age'   => 'required|between:18,60',
            ]
        );

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->errors());
    }

    public function test多规则组合验证部分失败(): void
    {
        $result = $this->validator->validate(
            ['name' => '', 'email' => 'bad-email', 'age' => 15],
            [
                'name'  => 'required|min:2|max:20',
                'email' => 'required|email',
                'age'   => 'required|between:18,60',
            ]
        );

        $this->assertFalse($result->isValid());

        $errors = $result->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('age', $errors);

        // 确认 validatedData 不包含有错误的字段
        $validatedData = $result->validatedData();
        $this->assertArrayNotHasKey('name', $validatedData);
        $this->assertArrayNotHasKey('email', $validatedData);
        $this->assertArrayNotHasKey('age', $validatedData);
    }

    public function test部分字段验证通过(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三', 'email' => 'bad-email'],
            [
                'name'  => 'required|min:2',
                'email' => 'required|email',
            ]
        );

        $this->assertFalse($result->isValid());

        // name 字段验证通过，应出现在 validatedData 中
        $this->assertArrayHasKey('name', $result->validatedData());
        $this->assertSame('张三', $result->validatedData()['name']);

        // email 字段验证失败，不应出现在 validatedData 中
        $this->assertArrayNotHasKey('email', $result->validatedData());
    }

    // ==================== 数组格式规则 ====================

    public function test数组格式规则(): void
    {
        $result = $this->validator->validate(
            ['name' => '', 'email' => 'test@example.com'],
            [
                'name'  => ['required', 'min:2'],
                'email' => ['required', 'email'],
            ]
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('name', $result->errors());
        $this->assertArrayNotHasKey('email', $result->errors());
    }

    // ==================== 自定义规则（闭包） ====================

    public function test自定义闭包规则(): void
    {
        $this->validator->addRule('is_even', function (string $field, mixed $value, array $params, array $data): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            if ((int) $value % 2 !== 0) {
                return "{$field} 必须是偶数";
            }
            return null;
        });

        $result = $this->validator->validate(
            ['num' => 4],
            ['num' => 'required|is_even']
        );

        $this->assertTrue($result->isValid());
    }

    public function test自定义闭包规则失败(): void
    {
        $this->validator->addRule('is_even', function (string $field, mixed $value, array $params, array $data): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            if ((int) $value % 2 !== 0) {
                return 'is_even';
            }
            return null;
        });

        $result = $this->validator->validate(
            ['num' => 3],
            ['num' => 'is_even']
        );

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('is_even', $result->errors()['num']);
    }

    // ==================== 边界值测试 ====================

    public function test字段不存在默认为null(): void
    {
        $result = $this->validator->validate(
            [],
            ['name' => 'required']
        );

        $this->assertFalse($result->isValid());
    }

    public function test区间规则边界值(): void
    {
        // 等于最小值
        $result = $this->validator->validate(
            ['age' => 1],
            ['age' => 'between:1,100']
        );
        $this->assertTrue($result->isValid());

        // 等于最大值
        $result = $this->validator->validate(
            ['age' => 100],
            ['age' => 'between:1,100']
        );
        $this->assertTrue($result->isValid());
    }

    // ==================== 不存在规则的处理 ====================

    public function test不存在的规则被忽略(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三'],
            ['name' => 'required|nonexistent|min:2']
        );

        $this->assertTrue($result->isValid());
    }

    // ==================== ValidationResult 完整性 ====================

    public function test验证通过结果完整性(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三', 'email' => 'test@example.com'],
            ['name' => 'required|min:2', 'email' => 'required|email']
        );

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->errors());
        $this->assertSame(
            ['name' => '张三', 'email' => 'test@example.com'],
            $result->validatedData()
        );
    }

    public function test验证失败结果完整性(): void
    {
        $result = $this->validator->validate(
            ['name' => '', 'email' => 'bad'],
            ['name' => 'required', 'email' => 'required|email']
        );

        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->errors());
        $this->assertSame([], $result->validatedData());
    }

    // ==================== 综合场景测试 ====================

    public function test注册表单验证场景(): void
    {
        $data = [
            'username'              => 'zhangsan',
            'email'                 => 'zhangsan@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'age'                   => 25,
        ];

        $rules = [
            'username' => 'required|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
            'email'    => 'required|email',
            'password' => 'required|min:6|max:100|confirmed',
            'age'      => 'required|between:18,100',
        ];

        $messages = [
            'username.attribute' => '用户名',
            'email.attribute'    => '邮箱',
            'password.attribute' => '密码',
            'age.attribute'      => '年龄',
        ];

        $result = $this->validator->validate($data, $rules, $messages);

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->errors());
        // validatedData 只返回定义了规则的字段，password_confirmation 仅用于 confirmed 比较
        $this->assertSame(
            ['username' => 'zhangsan', 'email' => 'zhangsan@example.com', 'password' => 'secret123', 'age' => 25],
            $result->validatedData()
        );
    }

    public function test注册表单验证失败场景(): void
    {
        $data = [
            'username'              => 'ab',
            'email'                 => 'bad-email',
            'password'              => '12345',
            'password_confirmation' => 'different',
            'age'                   => 12,
        ];

        $rules = [
            'username' => 'required|min:3|max:20',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
            'age'      => 'required|between:18,100',
        ];

        $messages = [
            'username.attribute' => '用户名',
            'email.attribute'    => '邮箱',
            'password.attribute' => '密码',
            'age.attribute'      => '年龄',
        ];

        $result = $this->validator->validate($data, $rules, $messages);

        $this->assertFalse($result->isValid());

        $errors = $result->errors();
        $this->assertArrayHasKey('username', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertArrayHasKey('age', $errors);

        // 确认每个错误消息都包含了对应别名
        $this->assertStringContainsString('用户名', $errors['username']['min']);
        $this->assertStringContainsString('邮箱', $errors['email']['email']);
        $this->assertStringContainsString('密码', $errors['password']['min']);
        $this->assertStringContainsString('年龄', $errors['age']['between']);
    }

    // ==================== URL 规则 ====================

    public function testURL规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['website' => 'https://example.com'],
            ['website' => 'url']
        );
        $this->assertTrue($result->isValid());
    }

    public function testURL规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['website' => 'not-a-url'],
            ['website' => 'url']
        );
        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('url', $result->errors()['website']);
    }

    public function testURL规则跳过空值(): void
    {
        $result = $this->validator->validate(
            ['website' => ''],
            ['website' => 'url']
        );
        $this->assertTrue($result->isValid());
    }

    // ==================== IP 规则 ====================

    public function testIP规则验证通过v4(): void
    {
        $result = $this->validator->validate(
            ['ip' => '192.168.1.1'],
            ['ip' => 'ip']
        );
        $this->assertTrue($result->isValid());
    }

    public function testIP规则验证通过v6(): void
    {
        $result = $this->validator->validate(
            ['ip' => '::1'],
            ['ip' => 'ip']
        );
        $this->assertTrue($result->isValid());
    }

    public function testIP规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['ip' => '999.999.999.999'],
            ['ip' => 'ip']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 数字规则 ====================

    public function test数字规则验证通过整数(): void
    {
        $result = $this->validator->validate(
            ['count' => 42],
            ['count' => 'numeric']
        );
        $this->assertTrue($result->isValid());
    }

    public function test数字规则验证通过字符串数字(): void
    {
        $result = $this->validator->validate(
            ['price' => '19.99'],
            ['price' => 'numeric']
        );
        $this->assertTrue($result->isValid());
    }

    public function test数字规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['count' => 'abc'],
            ['count' => 'numeric']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 字母规则 ====================

    public function test字母规则验证通过英文(): void
    {
        $result = $this->validator->validate(
            ['name' => 'HelloWorld'],
            ['name' => 'alpha']
        );
        $this->assertTrue($result->isValid());
    }

    public function test字母规则验证通过中文(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三'],
            ['name' => 'alpha']
        );
        $this->assertTrue($result->isValid());
    }

    public function test字母规则验证失败含数字(): void
    {
        $result = $this->validator->validate(
            ['name' => 'Hello123'],
            ['name' => 'alpha']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 字母数字规则 ====================

    public function test字母数字规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['code' => 'ABC123'],
            ['code' => 'alpha_num']
        );
        $this->assertTrue($result->isValid());
    }

    public function test字母数字规则验证通过中文数字(): void
    {
        $result = $this->validator->validate(
            ['code' => '用户001'],
            ['code' => 'alpha_num']
        );
        $this->assertTrue($result->isValid());
    }

    public function test字母数字规则验证失败含符号(): void
    {
        $result = $this->validator->validate(
            ['code' => 'ABC-123'],
            ['code' => 'alpha_num']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 日期规则 ====================

    public function test日期规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['birthday' => '2024-01-15'],
            ['birthday' => 'date']
        );
        $this->assertTrue($result->isValid());
    }

    public function test日期规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['birthday' => '2024-13-01'],
            ['birthday' => 'date']
        );
        $this->assertFalse($result->isValid());
    }

    public function test日期规则自定义格式(): void
    {
        $result = $this->validator->validate(
            ['datetime' => '2024-01-15 14:30:00'],
            ['datetime' => 'date:Y-m-d H:i:s']
        );
        $this->assertTrue($result->isValid());
    }

    public function test日期规则自定义格式失败(): void
    {
        $result = $this->validator->validate(
            ['datetime' => '15/01/2024'],
            ['datetime' => 'date:Y-m-d']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 相同规则 ====================

    public function test相同规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['password' => 'secret', 'confirm' => 'secret'],
            ['password' => 'same:confirm']
        );
        $this->assertTrue($result->isValid());
    }

    public function test相同规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['password' => 'secret', 'confirm' => 'different'],
            ['password' => 'same:confirm']
        );
        $this->assertFalse($result->isValid());
    }

    public function test相同规则目标字段不存在(): void
    {
        $result = $this->validator->validate(
            ['password' => 'secret'],
            ['password' => 'same:missing_field']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 不同规则 ====================

    public function test不同规则验证通过(): void
    {
        $result = $this->validator->validate(
            ['new_password' => 'new', 'old_password' => 'old'],
            ['new_password' => 'different:old_password']
        );
        $this->assertTrue($result->isValid());
    }

    public function test不同规则验证失败(): void
    {
        $result = $this->validator->validate(
            ['new_password' => 'same', 'old_password' => 'same'],
            ['new_password' => 'different:old_password']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 首错即停 ====================

    public function test首错即停模式(): void
    {
        $validator = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');
        $validator->stopOnFirstFailure();

        $result = $validator->validate(
            ['name' => '', 'email' => 'bad', 'age' => 0],
            [
                'name'  => 'required|min:2',
                'email' => 'required|email',
                'age'   => 'required|between:18,60',
            ]
        );

        $this->assertFalse($result->isValid());
        // 应该只有第一个字段的错误
        $this->assertCount(1, $result->errors());
        $this->assertArrayHasKey('name', $result->errors());
    }

    public function test首错即停不影响后续验证(): void
    {
        $validator = new Validator(require dirname(__DIR__, 2) . '/config/validation.php');
        $validator->stopOnFirstFailure();

        // 第一次验证——应该在 name 停止
        $validator->validate(
            ['name' => '', 'email' => 'bad'],
            ['name' => 'required', 'email' => 'email']
        );

        // 第二次验证——正常全量验证（name 通过，应继续）
        $result2 = $validator->validate(
            ['name' => '张三', 'email' => 'bad'],
            ['name' => 'required', 'email' => 'email']
        );

        $this->assertFalse($result2->isValid());
        $this->assertArrayHasKey('email', $result2->errors());
    }

    // ==================== Sometimes 规则 ====================

    public function testSometimes规则字段不存在时跳过(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三'],
            ['email' => 'sometimes|email']
        );
        $this->assertTrue($result->isValid());
    }

    public function testSometimes规则字段存在时正常验证(): void
    {
        $result = $this->validator->validate(
            ['name' => '张三', 'email' => 'bad-email'],
            ['email' => 'sometimes|email']
        );
        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('email', $result->errors());
    }

    // ==================== Nullable 规则 ====================

    public function testNullable规则null时跳过(): void
    {
        $result = $this->validator->validate(
            ['email' => null],
            ['email' => 'nullable|email']
        );
        $this->assertTrue($result->isValid());
    }

    public function testNullable规则有值正常验证(): void
    {
        $result = $this->validator->validate(
            ['email' => 'bad-email'],
            ['email' => 'nullable|email']
        );
        $this->assertFalse($result->isValid());
    }

    // ==================== 通配符数组验证 ====================

    public function test通配符数组验证全部通过(): void
    {
        $data = [
            'items' => [
                ['name' => '商品A', 'price' => 100],
                ['name' => '商品B', 'price' => 200],
            ],
        ];

        $rules = [
            'items.*.name'  => 'required|min:2',
            'items.*.price' => 'required|numeric|min:1',
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result->isValid());
    }

    public function test通配符数组验证部分失败(): void
    {
        $data = [
            'items' => [
                ['name' => 'A', 'price' => 'bad'],
                ['name' => '商品B', 'price' => 200],
            ],
        ];

        $rules = [
            'items.*.name'  => 'required|min:2',
            'items.*.price' => 'required|numeric',
        ];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
        $errors = $result->errors();
        $this->assertArrayHasKey('items.0.name', $errors);
        $this->assertArrayHasKey('items.0.price', $errors);
    }

    // ==================== 综合性增强测试 ====================

    public function test更新用户资料场景sometimes(): void
    {
        // 模拟 PATCH 请求——只提交部分字段
        $data = ['email' => 'newemail@example.com'];

        $rules = [
            'name'     => 'sometimes|min:2|max:20',
            'email'    => 'sometimes|email',
            'bio'      => 'sometimes|max:500',
        ];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
        $this->assertSame(['email' => 'newemail@example.com'], $result->validatedData());
    }

    public function test可选字段nullable组合(): void
    {
        $data = ['website' => null];

        $result = $this->validator->validate($data, [
            'website' => 'nullable|url',
        ]);

        $this->assertTrue($result->isValid());
    }

    public function test综合新规则验证场景(): void
    {
        $data = [
            'website'       => 'https://example.com',
            'server_ip'     => '10.0.0.1',
            'score'         => '95.5',
            'first_name'    => '张三',
            'username'      => 'user001',
            'birthday'      => '2000-01-01',
            'password'      => 'secret',
            'password_check' => 'secret',
            'new_email'     => 'new@example.com',
            'old_email'     => 'old@example.com',
        ];

        $rules = [
            'website'        => 'required|url',
            'server_ip'      => 'required|ip',
            'score'          => 'required|numeric',
            'first_name'     => 'required|alpha',
            'username'       => 'required|alpha_num',
            'birthday'       => 'required|date',
            'password'       => 'required|same:password_check',
            'new_email'      => 'required|email|different:old_email',
        ];

        $messages = [
            'website.attribute'        => '网站',
            'server_ip.attribute'      => '服务器IP',
            'score.attribute'          => '分数',
            'first_name.attribute'     => '名字',
            'username.attribute'       => '用户名',
            'birthday.attribute'       => '生日',
            'password.attribute'       => '密码',
            'password_check.attribute' => '确认密码',
            'new_email.attribute'      => '新邮箱',
            'old_email.attribute'      => '旧邮箱',
        ];

        $result = $this->validator->validate($data, $rules, $messages);

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->errors());
    }
}

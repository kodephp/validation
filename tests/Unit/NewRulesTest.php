<?php

declare(strict_types=1);

namespace Kode\Validation\Tests\Unit;

use Kode\Validation\Tests\Fixture\OrderStatus;
use Kode\Validation\Tests\Fixture\PriorityLevel;
use Kode\Validation\Validator;
use PHPUnit\Framework\TestCase;

/**
 * v1.9.0 新增规则单元测试
 *
 * 覆盖中国本地化、通用格式与逻辑条件三类共 31 条新规则。
 */
class NewRulesTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = Validator::create();
    }

    /**
     * 断言单字段规则的通过与失败情况
     */
    private function assertRule(string $rule, array $valid, array $invalid): void
    {
        foreach ($valid as $value) {
            $result = $this->validator->validate(['f' => $value], ['f' => $rule]);
            $this->assertTrue(
                $result->isValid(),
                sprintf('规则 %s 应通过：%s', $rule, var_export($value, true))
            );
        }

        foreach ($invalid as $value) {
            $result = $this->validator->validate(['f' => $value], ['f' => $rule]);
            $this->assertFalse(
                $result->isValid(),
                sprintf('规则 %s 应失败：%s', $rule, var_export($value, true))
            );
        }
    }

    // ==================== 中国本地化 ====================

    public function test手机号规则(): void
    {
        $this->assertRule(
            'mobile',
            ['13800138000', '19912345678'],
            ['12800138000', '1380013800', '138001380001', 'abcdefghijk']
        );
    }

    public function test身份证规则(): void
    {
        $this->assertRule(
            'id_card',
            ['11010519491231002X', '11010519491231002x'],
            ['110105194912310021', '11010519491331002X', '1101051949123100', 'abc']
        );
    }

    public function test银行卡规则(): void
    {
        $this->assertRule(
            'bank_card',
            ['6222021001122337', '6222 0210 0112 2337'],
            ['6222021001122338', '123', '62220210011223a7']
        );
    }

    public function test邮政编码规则(): void
    {
        $this->assertRule('postal_code', ['518000', '100000'], ['018000', '51800', '5180000']);
    }

    public function test中文姓名规则(): void
    {
        $this->assertRule(
            'chinese_name',
            ['张三', '欧阳明日', '买买提·吐尔逊'],
            ['张', 'ZhangSan', '张三3', '·张三']
        );
    }

    public function test车牌号规则(): void
    {
        $this->assertRule(
            'plate_number',
            ['京A12345', '沪A1234挂', '粤BD12345', '京AD12345'],
            ['ABC1234', '京A1234I', '京A1234']
        );
    }

    // ==================== 通用格式 ====================

    public function testUlid规则(): void
    {
        $this->assertRule(
            'ulid',
            ['01ARZ3NDEKTSV4RRFFQ69G5FAV'],
            ['01ARZ3NDEKTSV4RRFFQ69G5FA', '91ARZ3NDEKTSV4RRFFQ69G5FAV', '01ARZ3NDEKTSV4RRFFQ69G5FAU']
        );
    }

    public function test语义化版本规则(): void
    {
        $this->assertRule(
            'semver',
            ['1.9.0', '2.0.0-beta.1', '1.0.0+build.5'],
            ['1.9', 'v1.9.0', '01.9.0']
        );
    }

    public function testBase64规则(): void
    {
        $this->assertRule('base64', [base64_encode('kode/validation'), 'aGVsbG8='], ['aGVsbG8', '###']);
    }

    public function test十六进制颜色规则(): void
    {
        $this->assertRule('hex_color', ['#fff', '#FFFFFF', '#12345678'], ['fff', '#12345', '#ggg']);
    }

    public function testSlug规则(): void
    {
        $this->assertRule('slug', ['hello-world', 'php83'], ['Hello-World', '-hello', 'hello--world']);
    }

    public function testIpv4与Ipv6规则(): void
    {
        $this->assertRule('ipv4', ['192.168.1.1', '8.8.8.8'], ['256.1.1.1', '::1']);
        $this->assertRule('ipv6', ['::1', '2001:db8::ff00:42:8329'], ['192.168.1.1', 'xyz']);
    }

    public function test端口规则(): void
    {
        $this->assertRule('port', [80, '8080', 65535], [0, 65536, '-1', 'abc']);
    }

    public function test域名规则(): void
    {
        $this->assertRule(
            'domain',
            ['example.com', 'api.kode.dev'],
            ['exa mple.com', '-example.com', 'example']
        );
    }

    public function test经纬度规则(): void
    {
        $this->assertRule('latitude', [0, 22.5431, -90, 90], [90.1, -91, 'abc']);
        $this->assertRule('longitude', [114.0579, -180, 180], [180.1, -181, 'abc']);
    }

    public function testAscii规则(): void
    {
        $this->assertRule('ascii', ['Hello World!', 'abc123'], ['中文', "line\nbreak"]);
    }

    public function test大小写规则(): void
    {
        $this->assertRule('lowercase', ['hello', 'php8.3'], ['Hello', 'HELLO']);
        $this->assertRule('uppercase', ['HELLO', 'PHP8.3'], ['Hello', 'hello']);
    }

    // ==================== 逻辑与条件 ====================

    public function test排除枚举规则(): void
    {
        $this->assertRule('not_in:admin,root', ['user', 'guest'], ['admin', 'root']);
    }

    public function test反向正则规则(): void
    {
        $this->assertRule('not_regex:/^admin/i', ['user_1'], ['admin_1', 'ADMIN_1']);
    }

    public function test非空占位规则(): void
    {
        $this->assertTrue($this->validator->validate([], ['nickname' => 'filled'])->isValid());
        $this->assertTrue($this->validator->validate(['nickname' => 'a'], ['nickname' => 'filled'])->isValid());
        $this->assertFalse($this->validator->validate(['nickname' => ''], ['nickname' => 'filled'])->isValid());
        $this->assertFalse($this->validator->validate(['nickname' => '  '], ['nickname' => 'filled'])->isValid());
    }

    public function test字段存在与禁止规则(): void
    {
        $this->assertTrue($this->validator->validate(['tag' => null], ['tag' => 'present'])->isValid());
        $this->assertFalse($this->validator->validate([], ['tag' => 'present'])->isValid());

        $this->assertTrue($this->validator->validate([], ['role' => 'missing'])->isValid());
        $this->assertFalse($this->validator->validate(['role' => 'admin'], ['role' => 'missing'])->isValid());
    }

    public function test倍数规则(): void
    {
        $this->assertRule('multiple_of:5', [10, 0, '25'], [7, 2.5]);
        $this->assertRule('multiple_of:0.1', [0.3, 1.2], [0.35]);
    }

    public function test严格日期格式规则(): void
    {
        $this->assertRule('date_format:Y-m-d', ['2026-08-04'], ['2026-8-4', '2026/08/04', '20260804']);
        $this->assertRule('date_format:Y-m-d H:i:s', ['2026-08-04 23:15:08'], ['2026-08-04']);
    }

    public function test枚举规则(): void
    {
        $rule = 'enum:' . OrderStatus::class;
        $this->assertTrue($this->validator->validate(['s' => 'paid'], ['s' => $rule])->isValid());
        $this->assertTrue($this->validator->validate(['s' => OrderStatus::Paid], ['s' => $rule])->isValid());
        $this->assertFalse($this->validator->validate(['s' => 'unknown'], ['s' => $rule])->isValid());

        $intRule = 'enum:' . PriorityLevel::class;
        $this->assertTrue($this->validator->validate(['p' => 5], ['p' => $intRule])->isValid());
        $this->assertTrue($this->validator->validate(['p' => '9'], ['p' => $intRule])->isValid());
        $this->assertFalse($this->validator->validate(['p' => 3], ['p' => $intRule])->isValid());
    }

    public function test包含规则(): void
    {
        $this->assertRule('contains:hello,world', ['hello world'], ['hello there']);

        $result = $this->validator->validate(
            ['tags' => ['php', 'kode']],
            ['tags' => 'contains:php']
        );
        $this->assertTrue($result->isValid());
    }

    public function test禁止前后缀规则(): void
    {
        $this->assertRule('doesnt_start_with:admin,root', ['user_1'], ['admin_1', 'root_1']);
        $this->assertRule('doesnt_end_with:.exe,.sh', ['report.pdf'], ['virus.exe', 'run.sh']);
    }

    // ==================== 空值放行一致性 ====================

    public function test新规则统一放行空值(): void
    {
        $rules = [
            'mobile', 'id_card', 'bank_card', 'postal_code', 'chinese_name', 'plate_number',
            'ulid', 'semver', 'base64', 'hex_color', 'slug', 'ipv4', 'ipv6', 'port',
            'domain', 'latitude', 'longitude', 'ascii', 'lowercase', 'uppercase',
            'not_in:a,b', 'not_regex:/^x/', 'multiple_of:5', 'date_format:Y-m-d',
            'enum:' . OrderStatus::class, 'contains:x', 'doesnt_start_with:x', 'doesnt_end_with:x',
        ];

        foreach ($rules as $rule) {
            $result = $this->validator->validate(['f' => ''], ['f' => $rule]);
            $this->assertTrue($result->isValid(), "规则 {$rule} 应放行空字符串");
        }
    }

    public function test新规则均已注册(): void
    {
        $names = [
            'mobile', 'id_card', 'bank_card', 'postal_code', 'chinese_name', 'plate_number',
            'ulid', 'semver', 'base64', 'hex_color', 'slug', 'ipv4', 'ipv6', 'port', 'domain',
            'latitude', 'longitude', 'ascii', 'lowercase', 'uppercase', 'not_in', 'not_regex',
            'filled', 'present', 'missing', 'multiple_of', 'date_format', 'enum', 'contains',
            'doesnt_start_with', 'doesnt_end_with',
        ];

        $this->assertCount(31, $names);

        foreach ($names as $name) {
            $this->assertTrue($this->validator->hasRule($name), "规则 {$name} 未注册");
        }
    }

    public function test新规则均有默认中文消息(): void
    {
        $messages = require dirname(__DIR__, 2) . '/config/validation.php';

        foreach (['mobile', 'id_card', 'bank_card', 'enum', 'multiple_of', 'doesnt_end_with'] as $name) {
            $this->assertArrayHasKey($name, $messages);
        }

        $result = $this->validator->validate(['phone' => '123'], ['phone' => 'mobile']);
        $this->assertSame('phone 不是有效的手机号码', $result->first());
    }
}

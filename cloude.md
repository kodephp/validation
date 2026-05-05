# cloude.md — kode/validation IDE 助手规范

## 项目概述

`kode/validation` 是 PHP 8.2+ 的协程安全验证库，包名 `kode/validation`，版本 `1.1.0`。

- GitHub: https://github.com/kodephp/validation
- 许可证: MIT
- 测试: PHPUnit 10.x

## 核心设计原则

1. **协程安全第一**：Validator 不持有请求级可变状态，所有中间变量均为局部变量
2. **规则无状态**：每个 Rule 类可跨协程共享，`validate()` 纯函数化
3. **结果不可变**：`ValidationResult` 为 `readonly class`，构造后不可修改
4. **中文优先**：注释、消息模板、文档全部中文
5. **非短路验证**：默认收集所有错误，`stopOnFirstFailure()` 可切换

## PHP 版本特性使用指南

### PHP 8.2（基准版本）
- `readonly class` → [ValidationResult](file:///src/ValidationResult.php)
- 独立类型：`null`, `true`, `false`
- DNF 类型：`(RuleInterface&JsonSerializable)|null`

### PHP 8.3 新特性（推荐应用）
```php
// #[\Override] 标注重写方法
#[\Override]
public function validate(array $data, array $rules, array $messages = []): ValidationResult

// json_validate() → JSON 规则
if (!json_validate($value)) { return 'json'; }

// 类型化类常量
final class Validator {
    public const string VERSION = '1.1.0';
}
```

### PHP 8.4 新特性（可应用）
```php
// 属性钩子（Property Hooks）
readonly class ValidationResult {
    public bool $isValid {
        get => $this->isValid;
    }
}

// 非对称可见性
class Validator {
    public private(set) array $rules = [];
}

// #[\Deprecated] 属性
#[\Deprecated(message: "使用 stopOnFirstFailure() 替代", since: "1.2.0")]
public function setStopOnFirstFailure(bool $stop): void {}
```

### PHP 8.5 新特性（规划中，使用 feature detection）
```php
// 属性钩子增强
// 模式匹配（Pattern Matching）

// 安全使用方式——运行时检测：
if (PHP_VERSION_ID >= 80500) {
    // 使用 PHP 8.5 特性
}
```

## 添加新规则的标准流程

### 1. 创建规则类 `src/Rule/XxxRule.php`
```php
<?php
declare(strict_types=1);
namespace Kode\Validation\Rule;

/**
 * XXX验证规则，协程安全（无状态）
 */
class XxxRule implements RuleInterface
{
    #[\Override]
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        // 空值跳过（正则/邮箱等非必填规则的标准模式）
        if ($value === null || $value === '' || $value === []) {
            return null;
        }
        // 验证逻辑...
        return null; // 或返回规则名称字符串
    }

    #[\Override]
    public function getName(): string
    {
        return 'xxx';
    }
}
```

### 2. 注册到 `Validator.php`
```php
use Kode\Validation\Rule\XxxRule;
// 在 registerBuiltinRules() 的 $builtinRules 数组中添加
new XxxRule(),
```

### 3. 添加消息模板到 `config/validation.php`
```php
'xxx' => ':attribute 自定义错误消息',
```

### 4. 添加测试到 `tests/Unit/ValidatorTest.php`
```php
// ==================== XXX 规则 ====================
public function testXXX规则验证通过(): void { ... }
public function testXXX规则验证失败(): void { ... }
public function testXXX规则跳过空值(): void { ... }
```

## 现有规则速查（16 条）

| 规则名 | 类 | 参数 |
|--------|-----|------|
| required | RequiredRule | - |
| email | EmailRule | - |
| min | MinRule | value |
| max | MaxRule | value |
| between | BetweenRule | min,max |
| in | InRule | val1,val2,... |
| regex | RegexRule | pattern |
| confirmed | ConfirmedRule | - |
| url | UrlRule | - |
| ip | IpRule | - |
| numeric | NumericRule | - |
| alpha | AlphaRule | - |
| alpha_num | AlnumRule | - |
| date | DateRule | format |
| same | SameRule | field |
| different | DifferentRule | field |
| sometimes | (内置) | - |
| nullable | (内置) | - |

## 测试规范

- 测试类继承 `PHPUnit\Framework\TestCase`
- 方法命名：`test中文描述()`
- 每个规则至少 2 个用例（通过 + 失败）
- 空值跳过测试（email/url 等非必填规则）
- 边界值测试（min/max/between）
- 协程安全测试：使用 `Fiber` 并发验证隔离

## 运行测试
```bash
composer install
vendor/bin/phpunit tests/ --no-configuration --bootstrap vendor/autoload.php
```

## 版本策略

```
主版本.次版本.修订版本
1    .  2   .  0
│      │      └── 修订版本：Bug 修复
│      └── 次版本：新规则/新特性（向后兼容）
└── 主版本：不兼容的 API 变更
```

## 类关系图

```
ValidatorInterface ──▶ Validator ──▶ ValidationResult
                           │              │
                           │ uses         │ implements
                           ▼              ▼
                      RuleInterface  ValidationResultInterface
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
         RequiredRule  EmailRule    MinRule  ...
```

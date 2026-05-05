# kode/validation

协程安全的 PHP 数据验证库，支持 PHP 8.2+。

## 特性

- **协程安全**：所有验证过程使用局部变量，多 Fiber 并发互不干扰
- **管道规则**：`required|email|min:5|max:100` 直观的规则表达式
- **50+ 内置规则**：覆盖中英文、特殊字符、用户名、类型、格式、范围、比较等全场景
- **闭包自定义规则**：支持内联闭包和 `addRule()` 动态注册
- **中文错误消息**：默认中文模板，完整的字段别名和占位符支持
- **只读结果对象**：不可变设计，安全传递
- **多框架集成**：Trait / Helper / Validator 三种方式适配任何框架

## 环境要求

- PHP >= 8.2

## 安装

```bash
composer require kode/validation
```

## 快速开始

```php
<?php

declare(strict_types=1);

use Kode\Validation\Validator;

$validator = new Validator(require 'vendor/kode/validation/config/validation.php');

$data = [
    'name'  => '张三',
    'email' => 'zhangsan@example.com',
    'age'   => 25,
];

$rules = [
    'name'  => 'required|min:2|max:20',
    'email' => 'required|email',
    'age'   => 'required|between:18,60',
];

$result = $validator->validate($data, $rules);

if ($result->isValid()) {
    $safeData = $result->validatedData();
} else {
    print_r($result->errors());
}
```

## 内置规则速查（50 条）

### 基础验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `required` | `required` | 必填（null/''/[] 不通过） |
| `sometimes` | `sometimes` | 字段存在时才验证 |
| `nullable` | `nullable` | 为 null 时跳过 |

### 类型验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `string` | `string` | 字符串类型 |
| `integer` | `integer` | 整数类型 |
| `float` | `float` | 浮点类型 |
| `numeric` | `numeric` | 数字（整型/浮点/数字串） |
| `boolean` | `boolean` | 布尔类型 |
| `array` | `array` | 数组类型 |
| `json` | `json` | JSON 格式 |

### 范围与枚举
| 规则 | 格式 | 说明 |
|------|------|------|
| `min` | `min:值` | 最小值，字符串按 `mb_strlen`（1中文=1长度） |
| `max` | `max:值` | 最大值 |
| `between` | `between:最小值,最大值` | 区间范围（含边界） |
| `size` | `size:值` | 精确等于 |
| `in` | `in:值1,值2,值3` | 枚举值（严格类型比较） |

### 格式验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `email` | `email` | 邮箱格式 |
| `url` | `url` | URL 地址 |
| `ip` | `ip` | IPv4/IPv6 |
| `regex` | `regex:/正则/` | 正则表达式 |
| `date` | `date` 或 `date:Y-m-d` | 日期格式 |
| `digits` | `digits:位数` | 精确数字位数 |
| `digits_between` | `digits_between:最小,最大` | 数字位数区间 |

### 内容验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `alpha` | `alpha` | 纯字母 |
| `alpha_num` | `alpha_num` | 字母+数字 |
| `distinct` | `distinct` | 数组值不重复 |
| `starts_with` | `starts_with:前缀` | 以某值开头 |
| `ends_with` | `ends_with:后缀` | 以某值结尾 |

### 中英文与字符验证
| 规则 | 格式 | 说明 | 示例 |
|------|------|------|------|
| `chinese` | `chinese` | 包含中文字符 | `张三` ✓ |
| `english` | `english` | 纯英文字母 | `Hello` ✓ |
| `pure_digits` | `pure_digits` | 纯数字 | `12345` ✓ |
| `special_chars` | `special_chars` 或 `special_chars:字符集` | 包含特殊字符 | `abc@123` ✓ |
| `start_with_english` | `start_with_english` | 英文字母开头 | `user001` ✓ |
| `chinese_alpha_num` | `chinese_alpha_num` | 中文+英文+数字 | `张三Hello123` ✓ |
| `username` | `username` | 用户名格式：英文开头+字母数字下划线 | `admin_01` ✓ |
| `prefix_mixed` | `prefix_mixed:前缀长度` | 前N位英文+后缀字母数字混合 | `AB123` (prefix_mixed:2) ✓ |

### 关系与比较
| 规则 | 格式 | 说明 |
|------|------|------|
| `confirmed` | `confirmed` | 确认字段（需 `_confirmation` 字段） |
| `same` | `same:字段名` | 与某字段相同 |
| `different` | `different:字段名` | 与某字段不同 |
| `gt` | `gt:字段名` | 大于某字段 |
| `gte` | `gte:字段名` | 大于等于某字段 |
| `lt` | `lt:字段名` | 小于某字段 |
| `lte` | `lte:字段名` | 小于等于某字段 |
| `after` | `after:日期/字段` | 晚于某日期 |
| `before` | `before:日期/字段` | 早于某日期 |

### 接受/禁止
| 规则 | 格式 | 说明 |
|------|------|------|
| `accepted` | `accepted` | 必须接受（yes/on/1/true） |
| `declined` | `declined` | 必须拒绝（no/off/0/false） |
| `prohibited` | `prohibited` | 禁止存在 |
| `prohibited_if` | `prohibited_if:字段,值` | 条件下禁止 |

### 条件必填与排除
| 规则 | 格式 | 说明 |
|------|------|------|
| `required_if` | `required_if:字段,值` | 某字段为某值时必填 |
| `required_unless` | `required_unless:字段,值` | 某字段不为某值时必填 |
| `required_with` | `required_with:字段` | 某字段存在时必填 |
| `exclude_if` | `exclude_if:字段,值` | 条件下排除字段 |
| `exclude_unless` | `exclude_unless:字段,值` | 条件不满足时排除字段 |

## 规则详解

### min / max — 中文长度正确计算

`min` 和 `max` 对字符串使用 `mb_strlen()` 计算长度，**1 个中文字符 = 1 个长度**：

```php
['name' => 'required|chinese|min:2']
// '张三'  → mb_strlen = 2 → 通过
// '张'    → mb_strlen = 1 → 失败

['name' => 'required|max:3']
// '张三李' → mb_strlen = 3 → 通过
// '张三李王' → mb_strlen = 4 → 失败
```

### prefix_mixed — 前缀英文+后缀混合

```php
['code' => 'prefix_mixed:2']
// 'AB123'    → 前2位英文+后缀数字 → 通过
// 'ABCxyz'   → 前2位英文+后缀字母 → 通过
// '1AB123'   → 前2位不是英文 → 失败
// 'AB123@'   → 后缀含特殊字符 → 失败
```

### special_chars — 特殊字符验证

```php
['password' => 'special_chars']
// 默认检查: !@#$%^&*()-_=+[]{}|;:'",.<>?/\`~

['code' => 'special_chars:#']
// 只检查是否包含 #

['code' => 'special_chars:@#-']
// 检查是否包含 @、#、- 之一
```

### username — 用户名格式

```php
['username' => 'username']
// 'admin_01'  → 英文开头+字母数字下划线 → 通过
// '001user'   → 数字开头 → 失败
// '_user'     → 下划线开头 → 失败
// '张三user'  → 中文开头 → 失败
```

## 自定义规则

### 方式一：内联闭包（数组规则中直接传入）

```php
$rules = [
    'email' => [
        'required',
        function (string $field, mixed $value, array $params, array $data): ?string {
            return str_ends_with($value, '@example.com')
                ? null
                : '必须是 @example.com 邮箱';
        },
    ],
];

$result = $validator->validate($data, $rules);
```

### 方式二：addRule() 动态注册

```php
$validator->addRule('is_even', function (string $field, mixed $value, array $params, array $data): ?string {
    if ($value === null || $value === '') {
        return null; // 跳过空值
    }
    return ((int) $value % 2 === 0) ? null : 'must_be_even';
});

$result = $validator->validate(['num' => 4], ['num' => 'required|is_even']);
```

### 方式三：自定义规则类

```php
use Kode\Validation\Rule\RuleInterface;

class CustomRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        // 自定义逻辑
        return null; // 通过，或返回错误消息字符串
    }

    public function getName(): string
    {
        return 'custom';
    }
}

$validator->addRule('custom', new CustomRule());
```

## 自定义错误消息

### 规则级别

```php
$messages = [
    'name.required' => '姓名必须要填写',
    'email.email'   => '邮箱格式不正确',
];

$result = $validator->validate($data, $rules, $messages);
```

### 字段别名

```php
$messages = [
    'user_email.attribute' => '用户邮箱',
    'user_email.required'  => ':attribute 不能为空',  // 用户邮箱 不能为空
];
```

### 占位符

| 占位符 | 替换为 |
|--------|--------|
| `:attribute` | 字段别名或原名 |
| `:value` | 字段当前值 |
| `:param_0` | 规则第 1 个参数 |
| `:param_1` | 规则第 2 个参数 |

## 规则定义格式

### 字符串管道

```php
$rules = [
    'name'  => 'required|min:2|max:20',
    'email' => 'required|email',
];
```

### 数组格式（支持混用闭包和规则实例）

```php
use Kode\Validation\Rule\RequiredRule;
use Kode\Validation\Rule\EmailRule;

$rules = [
    'name' => ['required', 'min:2', 'max:20'],
    'email' => [
        'required',
        new EmailRule(),
        function (string $field, mixed $value, array $params, array $data): ?string {
            return str_ends_with($value, '@example.com') ? null : 'domain_not_allowed';
        },
    ],
];
```

## 验证结果

`ValidationResult` 是只读对象：

```php
$result = $validator->validate($data, $rules);

$result->isValid();        // bool: 全部通过为 true
$result->errors();         // array: ['字段' => ['规则' => '消息']]
$result->validatedData();  // array: 仅通过验证的字段
```

### 部分验证结果

```php
$data = ['name' => '张三', 'email' => 'invalid'];
$rules = ['name' => 'required|min:2', 'email' => 'required|email'];

$result = $validator->validate($data, $rules);

$result->isValid();  // false
$result->errors();   // ['email' => ['email' => 'email 不是有效的邮箱地址']]
$result->validatedData();  // ['name' => '张三']
```

## 实际应用

### 用户注册

```php
$data = [
    'username'              => 'zhangsan',
    'email'                 => 'zhangsan@example.com',
    'password'              => 'Secret123!',
    'password_confirmation' => 'Secret123!',
    'age'                   => 25,
];

$rules = [
    'username' => 'required|min:3|max:20|username',
    'email'    => 'required|email',
    'password' => 'required|min:6|confirmed',
    'age'      => 'required|between:18,100',
];

$messages = [
    'username.attribute' => '用户名',
    'email.attribute'    => '邮箱',
    'password.attribute' => '密码',
];

$result = $validator->validate($data, $rules, $messages);
```

### 复杂表单验证

```php
$data = [
    'name'   => '张三',
    'age'    => 25,
    'score'  => 95.5,
    'tags'   => ['red', 'blue', 'green'],
    'code'   => 123456,
    'agree'  => true,
];

$rules = [
    'name'  => 'required|string|chinese|min:2',
    'age'   => 'required|integer|between:1,120',
    'score' => 'required|float|between:0,100',
    'tags'  => 'required|array|distinct|size:3',
    'code'  => 'required|digits:6',
    'agree' => 'required|accepted',
];

$result = $validator->validate($data, $rules);
```

### API 请求验证

```php
function handleCreateUser(array $request): array
{
    $validator = new Validator(require 'config/validation.php');

    $result = $validator->validate($request, [
        'name'  => 'required|min:2|max:50',
        'email' => 'required|email',
        'role'  => 'required|in:user,admin,moderator',
    ]);

    if (!$result->isValid()) {
        return ['code' => 422, 'errors' => $result->errors()];
    }

    $user = createUser($result->validatedData());
    return ['code' => 201, 'data' => $user];
}
```

## 协程安全

`Validator` 不持有单次验证的中间状态，所有临时数据均为局部变量。即使多个 Fiber 同时调用 `validate()`，也不会出现数据交叉污染。

```php
for ($i = 0; $i < 10; $i++) {
    $fiber = new Fiber(function () use ($validator, $i) {
        return $validator->validate(
            ['id' => $i],
            ['id' => 'required|integer']
        )->isValid();
    });
    $fiber->start();
}
```

## 框架集成

### 方式一：ValidatesRequests Trait

```php
use Kode\Validation\Trait\ValidatesRequests;

class UserController
{
    use ValidatesRequests;

    public function store(array $request): array
    {
        $validated = $this->validateThrows($request, [
            'name'  => 'required|min:2|max:20',
            'email' => 'required|email',
        ]);
        return User::create($validated);
    }

    public function update(array $request): array
    {
        $result = $this->validateWithResult($request, [
            'name'  => 'sometimes|min:2|max:20',
            'email' => 'sometimes|email',
        ]);
        if (!$result['valid']) {
            return ['code' => 422, 'errors' => $result['errors']];
        }
        return ['code' => 200, 'data' => updateUser($result['data'])];
    }
}
```

| 方法 | 返回 | 说明 |
|------|------|------|
| `validateRequest()` | `ValidationResult` | 返回结果对象 |
| `validateThrows()` | `array` | 失败抛异常，成功返回数据 |
| `validateWithResult()` | `array` | 返回 `['valid','data','errors']` |
| `setValidator()` | `void` | 注入自定义验证器 |

### 方式二：ValidationHelper 静态方法

```php
use Kode\Validation\Helper\ValidationHelper;

$result = ValidationHelper::check($data, $rules);
$data = ValidationHelper::validated($data, $rules);
```

### 方式三：Validator 直接使用

```php
$validator = new Validator(require 'config/validation.php');
$data = $validator->stopOnFirstFailure()->validateThrows($data, $rules);
```

### 方式四：DI 容器

```php
$app->bind(ValidatorInterface::class, Validator::class);
```

## 多进程 / 多线程 / 协程安全

| 场景 | 安全性 | 说明 |
|------|--------|------|
| 协程（Fiber） | ✅ 安全 | 无实例可变状态，全部局部变量 |
| 多线程（parallel） | ✅ 安全 | Rule 类无状态，可跨线程共享 |
| 多进程（pcntl_fork） | ✅ 安全 | 独立内存空间，天然隔离 |
| Swoole/Swow 协程 | ✅ 安全 | 不依赖全局/静态可变状态 |
| Trait 复用 | ✅ 安全 | 每次调用独立结果对象 |

## 自定义默认消息

```bash
cp vendor/kode/validation/config/validation.php config/validation.php
```

```php
$validator = new Validator(require 'config/validation.php');
```

## 类图

```
┌─────────────────────┐     ┌──────────────────────────┐
│  ValidatorInterface │     │ValidationResultInterface │
│  + validate()       │     │  + isValid()             │
│  + validateThrows() │     │  + errors()              │
└──────────┬──────────┘     │  + validatedData()       │
           │                └─────────────┬────────────┘
           ▼                              │
┌─────────────────────┐                  ▼
│     Validator       │     ┌──────────────────────────┐
│  VERSION = '1.6.0'  │────▶│   ValidationResult       │
│  - 50条内置规则     │     │  (readonly)               │
│  + validate()        │     │  - valid: bool           │
│  + validateThrows()  │     │  - errors: array         │
│  + stopOnFirstFail() │     │  - validatedData: array  │
│  + beforeValidation()│     └──────────────────────────┘
│  + addRule()         │
└──────────┬──────────┘     ┌──────────────────────────┐
           │                │  Trait\ValidatesRequests │
           ▼                │  + validateRequest()     │
┌─────────────────────┐     │  + validateThrows()      │
│   RuleInterface     │     │  + validateWithResult()  │
│  + validate()       │     └──────────────────────────┘
│  + getName()        │
└──────────┬──────────┘     ┌──────────────────────────┐
           │                │  Helper\ValidationHelper │
   50 个规则实现类...       │  + check() (static)      │
                            │  + validated() (static)  │
                            └──────────────────────────┘
```

## 许可证

MIT License. 详见 [LICENSE](LICENSE) 文件。

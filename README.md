# kode/validation

协程安全的 PHP 数据验证库，专为 kode framework 设计，支持 PHP 8.2+。

## 特性

- **协程安全**：所有验证过程使用局部变量，多 Fiber 并发互不干扰
- **管道规则**：`required|email|min:5|max:100` 直观的规则表达式
- **8+ 内置规则**：覆盖必填、类型、格式、范围、比较等常见场景
- **自定义规则**：闭包或类均可注册，灵活扩展
- **中文错误消息**：默认中文模板，完整的字段别名和占位符支持
- **只读结果对象**：不可变设计，安全传递
- **10 级数据流管道**：面向 HLS FPGA 综合的流水线架构

## 环境要求

- PHP >= 8.2
- [kode/context](https://github.com/kodephp/context) ^1.0（协程上下文）

## 安装

```bash
composer require kode/validation
```

## 快速开始

```php
<?php

declare(strict_types=1);

use Kode\Validation\Validator;

// 使用默认中文消息模板
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
    // 使用 $safeData 进行后续操作
} else {
    $errors = $result->errors();
    print_r($errors);
    // 输出：['name' => ['required' => 'name 不能为空'], ...]
}
```

## 内置规则

| 规则 | 格式 | 说明 |
|------|------|------|
| `required` | `required` | 字段必填，null/空字符串/空数组不通过 |
| `email` | `email` | 邮箱格式（基于 `filter_var`） |
| `min` | `min:值` | 最小值，数字直接比较，字符串按 `mb_strlen` |
| `max` | `max:值` | 最大值，同上 |
| `between` | `between:最小值,最大值` | 区间范围，含边界 |
| `in` | `in:值1,值2,值3` | 枚举值，严格类型比较 |
| `regex` | `regex:/正则/` | 正则表达式匹配 |
| `confirmed` | `confirmed` | 确认字段，需同名 `_confirmation` 后缀字段 |

### 规则详解

#### required - 必填验证

```php
['name' => 'required']

// 以下视为空：
null    → 不通过
''      → 不通过
[]      → 不通过
0       → 通过（数字零不是空）
'0'     → 通过
false   → 通过
```

#### email - 邮箱验证

```php
['email' => 'email']

// 空值跳过，如需必填请组合 required：
['email' => 'required|email']
```

#### min / max - 最小最大值

```php
['age' => 'min:18']           // 数字 ≥ 18
['name' => 'min:2']           // 字符串长度 ≥ 2（mb_strlen）
['score' => 'max:100']        // 数字 ≤ 100
['title' => 'max:50']         // 字符串长度 ≤ 50
```

#### between - 区间范围

```php
['age' => 'between:18,60']    // 18 ≤ age ≤ 60
['name' => 'between:2,20']    // 2 ≤ 字符长度 ≤ 20
```

#### in - 枚举值

```php
['status' => 'in:active,inactive,pending']
// 严格类型比较（===），'1' ≠ 1
```

#### regex - 正则匹配

```php
['phone' => 'regex:/^1[3-9]\d{9}$/']
['code' => 'regex:/^[A-Z]{2}\d{4}$/']
```

#### confirmed - 确认字段

```php
// 数据中需存在同名 _confirmation 字段
$data = ['password' => 'secret', 'password_confirmation' => 'secret'];
$rules = ['password' => 'confirmed'];
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

$rules = ['user_email' => 'required|email'];
```

### 占位符

| 占位符 | 替换为 |
|--------|--------|
| `:attribute` | 字段别名或字段原名 |
| `:value` | 字段当前值 |
| `:param_0` | 规则第 1 个参数 |
| `:param_1` | 规则第 2 个参数 |
| `:param_N` | 规则第 N+1 个参数 |

## 自定义规则

### 闭包方式

```php
$validator->addRule('is_even', function (string $field, mixed $value, array $params, array $data): ?string {
    if ($value === null || $value === '') {
        return null; // 跳过空值
    }
    if ((int) $value % 2 !== 0) {
        return 'is_even'; // 返回规则名作为错误 key
    }
    return null; // 通过
});

$result = $validator->validate(['num' => 4], ['num' => 'is_even']);
```

### 类方式

```php
use Kode\Validation\Rule\RuleInterface;

class CustomRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $params, array $data): ?string
    {
        // 自定义逻辑
        return null; // 或返回错误消息
    }

    public function getName(): string
    {
        return 'custom';
    }
}

$validator->addRule('custom', new CustomRule());
```

## 数组格式规则

除管道字符串外，也支持数组格式：

```php
$rules = [
    'name' => ['required', 'min:2', 'max:20'],
    'email' => ['required', 'email'],
];

// 甚至混用规则类实例
use Kode\Validation\Rule\RequiredRule;
use Kode\Validation\Rule\EmailRule;

$rules = [
    'email' => [new RequiredRule(), new EmailRule()],
];
```

## 验证结果

`ValidationResult` 是只读对象，提供三个方法：

```php
$result = $validator->validate($data, $rules);

$result->isValid();        // bool: 全部通过为 true
$result->errors();         // array: ['字段' => ['规则' => '消息']]
$result->validatedData();  // array: 仅通过验证的字段数据
```

### 部分验证结果

当多字段验证时，部分字段通过、部分失败：

```php
$data = ['name' => '张三', 'email' => 'invalid'];
$rules = ['name' => 'required|min:2', 'email' => 'required|email'];

$result = $validator->validate($data, $rules);

$result->isValid();  // false
$result->errors();   // ['email' => ['email' => 'email 不是有效的邮箱地址']]
$result->validatedData();  // ['name' => '张三']  ← 仅包含通过验证的字段
```

## 实际应用示例

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
    'username.regex'     => '用户名只能包含字母、数字和下划线',
];

$result = $validator->validate($data, $rules, $messages);

if (!$result->isValid()) {
    foreach ($result->errors() as $field => $fieldErrors) {
        foreach ($fieldErrors as $rule => $message) {
            echo "{$field}: {$message}\n";
        }
    }
}
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

## 协程安全性

`Validator` 不持有单次验证的中间状态，所有临时数据均为局部变量。即使多个 Fiber 同时调用 `validate()`，也不会出现数据交叉污染。

```php
for ($i = 0; $i < 10; $i++) {
    $fiber = new Fiber(function () use ($i) {
        $result = $validator->validate(
            ['id' => $i],
            ['id' => 'required']
        );
        return $result->isValid();
    });
    $fiber->start();
    // ... 并发执行
}
```

## 框架集成

如果使用 `kode/di` 容器，可通过 Bundle 自动注册：

```php
// Bundle 会自动加载（通过 composer.json extra.kode.bundle 配置）
// 或手动注册：
$app->bind(ValidatorInterface::class, Validator::class);
```

不使用容器时直接实例化即可：

```php
$validator = new Validator(require 'config/validation.php');
```

## 自定义默认消息

复制配置文件到你的项目：

```bash
cp vendor/kode/validation/config/validation.php config/validation.php
```

修改后传入构造函数：

```php
$validator = new Validator(require 'config/validation.php');
```

## 框架集成

支持在 Controller、Model、Service、View 等框架各层中直接使用，无需额外封装。

### 方式一：ValidatesRequests Trait

将 Trait 引入任何类，即可获得验证能力：

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
            'role'  => 'required|in:user,admin',
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
| `validateRequest()` | `ValidationResult` | 返回标准结果对象 |
| `validateThrows()` | `array` | 失败抛异常，成功返回数据 |
| `validateWithResult()` | `array` | 返回 `['valid','data','errors']` |
| `setValidator()` | `void` | 注入自定义验证器 |

### 方式二：ValidationHelper 静态方法

```php
use Kode\Validation\Helper\ValidationHelper;

$result = ValidationHelper::check($data, $rules);
$data = ValidationHelper::validated($data, $rules); // 失败返回 null
```

### 方式三：Validator 直接使用

```php
$validator = new Validator(require 'config/validation.php');
$data = $validator->stopOnFirstFailure()->validateThrows($data, $rules);
```

## 多进程 / 多线程 / 协程安全

| 场景 | 安全性 | 说明 |
|------|--------|------|
| 协程（Fiber） | ✅ 安全 | 无实例可变状态，全部局部变量 |
| 多线程（parallel） | ✅ 安全 | Rule 类无状态，可跨线程共享 |
| 多进程（pcntl_fork） | ✅ 安全 | 独立内存空间，天然隔离 |
| Swoole/Swow 协程 | ✅ 安全 | 不依赖全局/静态可变状态 |
| Trait 复用 | ✅ 安全 | 每次调用独立结果对象 |

```php
for ($i = 1; $i <= 100; $i++) {
    $fiber = new Fiber(function () use ($validator, $i) {
        return $validator->validate(
            ['id' => $i], ['id' => 'required|integer']
        )->isValid();
    });
    $fiber->start();
}
// 100 个协程并发，结果互不干扰
```

## 完整规则（43 条）

| 规则 | 类型 | 说明 |
|------|------|------|
| `required` | 基础 | 必填（null/''/[] 不通过） |
| `sometimes` | 控制 | 字段存在时才验证 |
| `nullable` | 控制 | 字段为 null 时跳过 |
| `confirmed` | 关系 | 需 `_confirmation` 字段匹配 |
| `accepted` / `declined` | 基础 | 接受/拒绝 |
| `prohibited` / `prohibited_if` | 禁止 | 字段禁止存在 |
| `string` / `integer` / `float` / `numeric` / `boolean` / `array` | 类型 | 类型验证 |
| `min` / `max` / `between` / `size` / `in` | 范围 | 范围/枚举 |
| `email` / `url` / `ip` / `json` / `regex` | 格式 | 格式验证 |
| `date` / `after` / `before` | 日期 | 日期与比较 |
| `alpha` / `alpha_num` / `distinct` | 内容 | 内容验证 |
| `starts_with` / `ends_with` | 内容 | 前后缀 |
| `digits` / `digits_between` | 格式 | 数字位数 |
| `gt` / `gte` / `lt` / `lte` | 比较 | 字段间比较 |
| `same` / `different` | 关系 | 字段异同 |
| `required_if` / `required_unless` / `required_with` | 条件 | 条件必填 |
| `exclude_if` / `exclude_unless` | 条件 | 条件排除 |

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
│  VERSION = '1.4.0'  │────▶│   ValidationResult       │
│  - 43条内置规则     │     │  (readonly)               │
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
   43 个规则实现类...       │  + check() (static)      │
                            │  + validated() (static)  │
                            └──────────────────────────┘
```

## 版本历史

| 版本 | 更新内容 |
|------|----------|
| v1.0.0 | 8 条基础规则 + 协程安全架构 |
| v1.1.0 | +8 条规则 + sometimes/nullable + 通配符 |
| v1.2.0 | +9 条规则 + stopOnFirstFailure |
| v1.3.0 | +17 条规则 + beforeValidation + 条件排除 |
| v1.3.1 | 移除 PHP 8.3 专属语法，兼容 8.2+ |
| v1.4.0 | Trait + Helper + 框架集成 + 多进程/协程 |
```

## 许可证

MIT License. 详见 [LICENSE](LICENSE) 文件。

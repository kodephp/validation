# kode/validation

协程安全的 PHP 数据验证库，支持 PHP 8.3+（类型化类常量、`#[\Override]`、`json_validate()`）。

## 特性

- **协程安全**：所有验证过程使用局部变量，多 Fiber 并发互不干扰
- **管道规则**：`required|email|min:5|max:100` 直观的规则表达式
- **88 内置规则**：覆盖中英文、特殊字符、用户名、类型、格式、范围、比较、中国本地化（手机号 / 身份证 / 银行卡 / 车牌 / 邮编 / 中文名）等全场景；其中 **31 条为 v1.9.0 新增**
- **闭包自定义规则**：支持内联闭包和 `addRule()` 动态注册
- **中文错误消息**：默认中文模板，完整的字段别名和占位符支持
- **只读结果对象**：不可变设计，安全传递，并实现 `Countable` / `JsonSerializable`
- **性能优化**：内置规则进程级共享实例池、字符串规则串解析缓存（带上限防膨胀）、闭包规则 `WeakMap` 缓存
- **多框架集成**：Trait / Helper / Validator 三种方式适配任何框架

## 环境要求

- PHP >= 8.3

## 安装

```bash
composer require kode/validation
```

## 快速开始

```php
<?php

declare(strict_types=1);

use Kode\Validation\Validator;

// Validator::create() 自动加载内置中文消息模板（config/validation.php）
$validator = Validator::create();

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

## 内置规则速查（88 条）

> 下表为历史规则；**v1.9.0 新增的 31 条规则**请见 [新增规则（v1.9.0）](#新增规则v190共-31-条) 一节。

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
| `json` | `json` | JSON 格式（使用 `json_validate()`） |

### 范围与枚举
| 规则 | 格式 | 说明 |
|------|------|------|
| `min` | `min:值` | 最小值，字符串按 `mb_strlen`（1中文=1长度） |
| `max` | `max:值` | 最大值 |
| `between` | `between:最小值,最大值` | 区间范围（含边界） |
| `size` | `size:值` | 精确等于 |
| `in` | `in:值1,值2,值3` | 枚举值（严格类型比较） |
| `length` | `length:长度` | 精确字符长度（mb_strlen 计算） |

### 格式验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `email` | `email` | 邮箱格式 |
| `url` | `url` | URL 地址 |
| `ip` | `ip` | IPv4/IPv6 |
| `regex` | `regex:/正则/` | 正则表达式（参数整体保留，不被逗号截断） |
| `date` | `date` 或 `date:Y-m-d` | 日期格式 |
| `digits` | `digits:位数` | 精确数字位数 |
| `digits_between` | `digits_between:最小,最大` | 数字位数区间 |
| `uuid` | `uuid` | UUID 格式（v1-v5） |
| `mac_address` | `mac_address` | MAC 地址（三种格式） |
| `timezone` | `timezone` | 有效时区标识符 |
| `future` | `future` | 未来日期 |
| `past` | `past` | 过去日期 |

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

## 新增规则（v1.9.0，共 31 条）

### 中国本地化验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `mobile` | `mobile` | 中国大陆手机号（1[3-9] 开头，11 位） |
| `id_card` | `id_card` | 身份证号（18 位，校验位 + 出生日期合法性，基于 `checkdate`） |
| `bank_card` | `bank_card` | 银行卡号（Luhn 校验） |
| `postal_code` | `postal_code` | 中国邮政编码（6 位数字） |
| `chinese_name` | `chinese_name` | 中文姓名（2-8 位汉字，支持间隔号 `·`） |
| `plate_number` | `plate_number` | 车牌号（传统 + 新能源，普通/教练/挂/警/使馆等） |

```php
['phone' => 'required|mobile']
// '13812345678'  → 通过
// '12345678901'  → 失败

['id' => 'required|id_card']
// '11010519491231002X' → 通过（含校验位）
// '440524188001010014' → 失败（校验位不合法）

['plate' => 'plate_number']
// '京A12345' / '粤BD12345'（新能源） / '沪A1234挂' → 通过
// 'ABC1234' / '京A1234I' → 失败
```

### 通用格式验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `ascii` | `ascii` | 仅 ASCII 可打印字符 |
| `base64` | `base64` | 合法 Base64 编码 |
| `hex_color` | `hex_color` | 十六进制颜色（`#RGB` / `#RRGGBB`） |
| `slug` | `slug` | URL slug（小写字母、数字、连字符） |
| `ulid` | `ulid` | ULID 标识符（26 位 Crockford Base32） |
| `semver` | `semver` | 语义化版本号（SemVer 2.0.0） |
| `domain` | `domain` | 域名（含多级子域与 IDN 兼容） |
| `ipv4` | `ipv4` | 仅 IPv4 地址 |
| `ipv6` | `ipv6` | 仅 IPv6 地址 |
| `port` | `port` | 端口号（0-65535，整数） |
| `latitude` | `latitude` | 纬度（-90 ~ 90） |
| `longitude` | `longitude` | 经度（-180 ~ 180） |
| `lowercase` | `lowercase` | 全小写 |
| `uppercase` | `uppercase` | 全大写 |
| `date_format` | `date_format:Y-m-d` | 按指定格式校验日期（参数整体保留） |
| `not_regex` | `not_regex:/正则/` | 不匹配指定正则（参数整体保留） |
| `enum` | `enum:ClassName` | 枚举成员（支持 Backed Enum 的 `tryFrom`） |

```php
['color' => 'hex_color']
// '#fff' / '#1a2b3c' → 通过
// 'red' / '#12345' → 失败

['ver' => 'semver']
// '1.9.0' / '2.0.0-rc.1' → 通过

['status' => 'enum:App\Enums\OrderStatus']
// 传入 OrderStatus::Paid 的 value → 通过
// 不在枚举成员中 → 失败
```

### 逻辑与存在性验证
| 规则 | 格式 | 说明 |
|------|------|------|
| `not_in` | `not_in:值1,值2` | 不在枚举值中（严格类型比较） |
| `multiple_of` | `multiple_of:3` | 为某数的整数倍 |
| `contains` | `contains:子串` | 字符串包含指定子串 |
| `doesnt_start_with` | `doesnt_start_with:前缀` | 不以某值开头 |
| `doesnt_end_with` | `doesnt_end_with:后缀` | 不以某值结尾 |
| `filled` | `filled` | 字段存在且非空（非 null/''/[]） |
| `present` | `present` | 字段必须存在（值可为空） |
| `missing` | `missing` | 字段必须不存在 |

```php
['tags' => 'contains:news']
// 'news-hot' → 通过； 'old' → 失败

['note' => 'filled']
// 'hello' → 通过； '' / 未提交 → 失败

['token' => 'missing']
// 未提供 token → 通过； 提供 → 失败
```

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
// 默认检查: !@#$%^&*()-_=+[]{}|;:'",.<>?/`~

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
| `:field` | 当前字段原始键名（如 `users.0.name`，通配符场景尤其有用） |
| `:params` | 规则全部参数拼接（逗号分隔） |

> 通配符字段（如 `users.*.name`）的错误消息支持用 `users.*.name.required` 形式集中配置，`*`
> 会自动匹配任意下标。

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

`ValidationResult` 是只读对象，并实现 `Countable`（可用 `count()`）与 `JsonSerializable`（可 `json_encode`）。

```php
$result = $validator->validate($data, $rules);

$result->isValid();        // bool: 全部通过为 true
$result->fails();          // bool: 与 isValid() 相反
$result->errors();         // array: ['字段' => ['规则' => '消息']]
$result->validatedData();  // array: 仅通过验证的字段（未提交字段不会混入）
$result->first();          // ?string: 第一条错误消息（可传字段名取该字段首错）
$result->first('email');   // ?string: email 字段的首条错误
$result->has('email');     // bool: 是否存在该字段错误
$result->get('email');     // array: 该字段全部错误
$result->messages();       // array: 扁平化消息列表 ['字段.规则' => 消息]
$result->flatten();        // array: 纯消息字符串列表
$result->failedRules();    // array: ['字段' => ['规则', ...]]
$result->invalidFields();  // array: 失败字段名列表
$result->only(['a','b']);  // array: 仅保留指定字段的验证数据
$result->except(['c']);    // array: 排除指定字段的验证数据
$result->toArray();        // array: 结构化结果 ['valid','errors','validatedData']
count($result);            // int: 错误总数
json_encode($result);      // JSON: 序列化后的结果
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

## 高级用法（v1.9.0）

### 工厂方法 Validator::create()

自动加载内置中文消息模板，无需手动 `require` 配置文件：

```php
$validator = Validator::create();                       // 内置模板
$validator = Validator::create($myMessages);            // 自定义模板
```

### 前置 / 后置回调

```php
$validator
    ->beforeValidation(function (array $data, array $rules): array {
        // 验证前统一 trim / 规整数据
        return [$data, $rules];
    })
    ->afterValidation(function (\Kode\Validation\ValidationResult $result, array $data): ?\Kode\Validation\ValidationResult {
        // 校验通过后追加业务级校验
        return null; // 返回 null 保留原结果；返回新结果则覆盖
    });
```

### 字段显示名与动态消息 / 规则

```php
$validator
    ->setAttributeNames(['user_email' => '用户邮箱', 'users.*.name' => '成员姓名'])
    ->addMessages(['mobile.mobile' => ':attribute 格式不正确'])
    ->addRules(['is_even' => fn ($f, $v, $p, $d) => ((int)$v % 2 === 0 ? null : '需为偶数')])
    ->hasRule('mobile')   // true
    ->ruleNames();        // 全部已注册规则名数组
```

### 流程控制指令

`sometimes` / `nullable` / `bail` 为纯流程控制，不产生错误：
- `bail`：遇到该字段首个错误即停止后续规则（首次错误即返回的语义同样适用于 `stopOnFirstFailure()`）。
- `stopOnFirstFailure()`：全局级短路，首个字段的首个错误出现即返回（现已在通配符分支内同样生效，修复旧版 bug）。

### 静态缓存清理

内置规则实例池 / 解析缓存 / 闭包缓存在常驻进程（Swoole/Swow）中共享。
如需在测试或热重载时重置：

```php
Validator::flushCaches();
```

### 异常用法

`ValidationException` 构造时默认取首条错误作为消息，并提供 `messages()` / `first()`：

```php
try {
    $data = $validator->validateThrows($data, $rules);
} catch (\Kode\Validation\Exception\ValidationException $e) {
    echo $e->first();        // 首条错误
    print_r($e->messages()); // 全部消息
}
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
    'mobile'                => '13812345678',
];

$rules = [
    'username' => 'required|min:3|max:20|username',
    'email'    => 'required|email',
    'mobile'   => 'required|mobile',
    'password' => 'required|min:6|confirmed',
    'age'      => 'required|between:18,100',
];

$messages = [
    'username.attribute' => '用户名',
    'email.attribute'    => '邮箱',
    'mobile.attribute'   => '手机号',
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
    $validator = Validator::create();

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
$data   = ValidationHelper::validated($data, $rules);
// v1.9.0 新增：
$ok     = ValidationHelper::passes($data, $rules);   // bool
$msg    = ValidationHelper::firstError($data, $rules); // ?string 首错
ValidationHelper::reset();                            // 重置内部共享实例
```

### 方式三：Validator 直接使用

```php
$validator = Validator::create();
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

## 性能优化说明（v1.9.0）

- **共享规则实例池**：内置 88 条规则无状态，进程级惰性实例化并缓存，重复验证几乎零构造开销（基准：2 万次构造从 ~116ms 降至 ~2ms）。
- **字符串规则串解析缓存**：`'a|b:c'` 形式的规则串解析结果按原文缓存，上限 `PARSE_CACHE_LIMIT = 1024`，超量整体重置以防长驻进程内存膨胀。
- **闭包规则 `WeakMap` 缓存**：闭包规则以 `\WeakMap` 缓存，闭包被 GC 后自动释放，避免内存持续增长。

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
│  VERSION = '1.9.0'  │────▶│   ValidationResult       │
│  - 88条内置规则     │     │  (readonly)               │
│  + validate()        │     │  - valid: bool           │
│  + validateThrows()  │     │  - errors: array         │
│  + stopOnFirstFail() │     │  - validatedData: array  │
│  + create()          │     │  + fails()/first()/...   │
│  + beforeValidation()│     └──────────────────────────┘
│  + afterValidation() │
│  + setAttributeNames()│    ┌──────────────────────────┐
│  + addMessages()      │    │  Trait\ValidatesRequests │
│  + addRules()         │    │  + validateRequest()     │
│  + hasRule()/ruleNames()│   │  + validateThrows()      │
│  + flushCaches()      │    │  + validateWithResult()  │
└──────────┬──────────┘     └──────────────────────────┘
           │
┌─────────────────────┐     ┌──────────────────────────┐
│   RuleInterface     │     │  Helper\ValidationHelper │
│  + validate()       │     │  + check() (static)      │
│  + getName()        │     │  + validated() (static)  │
└──────────┬──────────┘     │  + passes()/firstError() │
   88 个规则实现类...        │  + reset() (static)      │
                            └──────────────────────────┘
```

## 许可证

MIT License. 详见 [LICENSE](LICENSE) 文件。

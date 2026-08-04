<?php

declare(strict_types=1);

namespace Kode\Validation;

use Kode\Validation\Contract\ValidatorInterface;
use Kode\Validation\Rule\ClosureRule;
use Kode\Validation\Rule\RuleInterface;

/**
 * 核心验证器，协程安全
 *
 * 所有验证过程的中间状态均使用局部变量，不存储在实例属性中，
 * 确保多协程并发验证互不干扰。
 *
 * 性能设计：
 *   1. 内置规则按需惰性实例化，并放入进程级共享池（规则无状态，可跨协程共享）；
 *   2. 字符串规则串解析结果带上限缓存，重复请求零解析开销。
 *
 * 使用示例：
 * <code>
 * $validator = Validator::create();
 * $result = $validator->validate(
 *     ['name' => '张三', 'email' => 'zhangsan@example.com'],
 *     ['name' => 'required|min:2|max:20', 'email' => 'required|email']
 * );
 * if ($result->isValid()) {
 *     $data = $result->validatedData();
 * }
 * </code>
 */
class Validator implements ValidatorInterface
{
    /**
     * 当前验证库版本号（语义化版本）
     */
    public const string VERSION = '1.9.0';

    /**
     * 内置规则映射：规则名 => 规则类名（惰性实例化）
     *
     * @var array<string, class-string<RuleInterface>>
     */
    private const array BUILTIN_RULES = [
        'accepted'           => Rule\AcceptedRule::class,
        'after'              => Rule\AfterRule::class,
        'alpha'              => Rule\AlphaRule::class,
        'alpha_num'          => Rule\AlnumRule::class,
        'array'              => Rule\ArrayRule::class,
        'ascii'              => Rule\AsciiRule::class,
        'bank_card'          => Rule\BankCardRule::class,
        'base64'             => Rule\Base64Rule::class,
        'before'             => Rule\BeforeRule::class,
        'between'            => Rule\BetweenRule::class,
        'boolean'            => Rule\BooleanRule::class,
        'chinese'            => Rule\ChineseRule::class,
        'chinese_alpha_num'  => Rule\ChineseAlphaNumRule::class,
        'chinese_name'       => Rule\ChineseNameRule::class,
        'confirmed'          => Rule\ConfirmedRule::class,
        'contains'           => Rule\ContainsRule::class,
        'date'               => Rule\DateRule::class,
        'date_format'        => Rule\DateFormatRule::class,
        'declined'           => Rule\DeclinedRule::class,
        'different'          => Rule\DifferentRule::class,
        'digits'             => Rule\DigitsRule::class,
        'digits_between'     => Rule\DigitsBetweenRule::class,
        'distinct'           => Rule\DistinctRule::class,
        'doesnt_end_with'    => Rule\DoesntEndWithRule::class,
        'doesnt_start_with'  => Rule\DoesntStartWithRule::class,
        'domain'             => Rule\DomainRule::class,
        'email'              => Rule\EmailRule::class,
        'ends_with'          => Rule\EndsWithRule::class,
        'english'            => Rule\EnglishRule::class,
        'enum'               => Rule\EnumRule::class,
        'exclude_if'         => Rule\ExcludeIfRule::class,
        'exclude_unless'     => Rule\ExcludeUnlessRule::class,
        'filled'             => Rule\FilledRule::class,
        'float'              => Rule\FloatRule::class,
        'future'             => Rule\FutureRule::class,
        'gt'                 => Rule\GtRule::class,
        'gte'                => Rule\GteRule::class,
        'hex_color'          => Rule\HexColorRule::class,
        'id_card'            => Rule\IdCardRule::class,
        'in'                 => Rule\InRule::class,
        'integer'            => Rule\IntegerRule::class,
        'ip'                 => Rule\IpRule::class,
        'ipv4'               => Rule\Ipv4Rule::class,
        'ipv6'               => Rule\Ipv6Rule::class,
        'json'               => Rule\JsonRule::class,
        'latitude'           => Rule\LatitudeRule::class,
        'length'             => Rule\LengthRule::class,
        'longitude'          => Rule\LongitudeRule::class,
        'lowercase'          => Rule\LowercaseRule::class,
        'lt'                 => Rule\LtRule::class,
        'lte'                => Rule\LteRule::class,
        'mac_address'        => Rule\MacAddressRule::class,
        'max'                => Rule\MaxRule::class,
        'min'                => Rule\MinRule::class,
        'missing'            => Rule\MissingRule::class,
        'mobile'             => Rule\MobileRule::class,
        'multiple_of'        => Rule\MultipleOfRule::class,
        'not_in'             => Rule\NotInRule::class,
        'not_regex'          => Rule\NotRegexRule::class,
        'numeric'            => Rule\NumericRule::class,
        'past'               => Rule\PastRule::class,
        'plate_number'       => Rule\PlateNumberRule::class,
        'port'               => Rule\PortRule::class,
        'postal_code'        => Rule\PostalCodeRule::class,
        'prefix_mixed'       => Rule\PrefixMixedRule::class,
        'present'            => Rule\PresentRule::class,
        'prohibited'         => Rule\ProhibitedRule::class,
        'prohibited_if'      => Rule\ProhibitedIfRule::class,
        'pure_digits'        => Rule\PureDigitsRule::class,
        'regex'              => Rule\RegexRule::class,
        'required'           => Rule\RequiredRule::class,
        'required_if'        => Rule\RequiredIfRule::class,
        'required_unless'    => Rule\RequiredUnlessRule::class,
        'required_with'      => Rule\RequiredWithRule::class,
        'same'               => Rule\SameRule::class,
        'semver'             => Rule\SemverRule::class,
        'size'               => Rule\SizeRule::class,
        'slug'               => Rule\SlugRule::class,
        'special_chars'      => Rule\SpecialCharsRule::class,
        'start_with_english' => Rule\StartWithEnglishRule::class,
        'starts_with'        => Rule\StartsWithRule::class,
        'string'             => Rule\StringRule::class,
        'timezone'           => Rule\TimezoneRule::class,
        'ulid'               => Rule\UlidRule::class,
        'uppercase'          => Rule\UppercaseRule::class,
        'url'                => Rule\UrlRule::class,
        'username'           => Rule\UsernameRule::class,
        'uuid'               => Rule\UuidRule::class,
    ];

    /**
     * 控制指令：不产生错误，只影响验证流程
     */
    private const array CONTROL_RULES = ['sometimes', 'nullable', 'bail'];

    /**
     * 参数整体保留、不按逗号切分的规则（避免正则/日期格式被截断）
     */
    private const array RAW_PARAM_RULES = ['regex', 'not_regex', 'date_format'];

    /**
     * 规则串解析缓存条目上限，超过则整体重置，避免长驻进程内存膨胀
     */
    private const int PARSE_CACHE_LIMIT = 1024;

    /**
     * @var array<string, RuleInterface> 进程级共享的内置规则实例池（规则无状态，跨协程共享安全）
     */
    private static array $sharedRules = [];

    /**
     * @var array<string, list<array{0: string, 1: array}>> 字符串规则串解析缓存
     */
    private static array $parseCache = [];

    /**
     * @var \WeakMap<\Closure, array{0: string, 1: ClosureRule}>|null 闭包规则缓存，闭包回收后自动释放
     */
    private static ?\WeakMap $closureRules = null;

    /**
     * @var array<string, RuleInterface> 实例级规则（自定义规则或覆盖内置规则）
     */
    private array $rules = [];

    /**
     * @var array 默认消息模板，规则名 => 消息模板
     */
    private array $defaultMessages;

    /**
     * @var array<string, string> 字段显示名映射，字段名 => 中文名
     */
    private array $attributeNames = [];

    /**
     * @var bool 是否在首个错误时停止验证
     */
    private bool $stopOnFirstFailure = false;

    /**
     * @var callable|null 验证前置回调，签名为 function(array $data, array $rules): array
     */
    private $beforeValidationCallback = null;

    /**
     * @var callable|null 验证后置回调，签名为 function(ValidationResult $result, array $data): ?ValidationResult
     */
    private $afterValidationCallback = null;

    /**
     * 构造函数
     *
     * @param array $defaultMessages 默认消息模板，通常从 config/validation.php 加载
     */
    public function __construct(array $defaultMessages = [])
    {
        $this->defaultMessages = $defaultMessages;
    }

    /**
     * 创建验证器实例
     *
     * 未显式传入消息模板时自动加载内置中文消息（config/validation.php）。
     *
     * @param array|null $messages 消息模板，null 表示使用内置模板
     */
    public static function create(?array $messages = null): self
    {
        if ($messages === null) {
            $configPath = dirname(__DIR__) . '/config/validation.php';
            $messages = file_exists($configPath) ? (array) require $configPath : [];
        }

        return new self($messages);
    }

    /**
     * 验证数据，协程安全
     *
     * 所有验证中间状态均使用局部变量，每次调用相互独立，
     * 多协程并发执行不会导致数据交叉污染。
     *
     * @param array $data     要验证的数据（键值对）
     * @param array $rules    验证规则，支持字符串 '规则1|规则2' 或数组 ['规则1', '规则2']
     * @param array $messages 自定义错误消息，格式 ['字段名.规则名' => '消息模板']
     * @return ValidationResult 验证结果对象
     */
    #[\Override]
    public function validate(array $data, array $rules, array $messages = []): ValidationResult
    {
        // 执行前置回调
        if ($this->beforeValidationCallback !== null) {
            [$data, $rules] = ($this->beforeValidationCallback)($data, $rules);
        }

        // 使用局部变量存储中间状态，不污染实例属性
        $allErrors = [];
        $validatedData = [];
        $excludedFields = [];

        foreach ($rules as $field => $ruleSet) {
            $targets = str_contains($field, '*')
                ? $this->expandWildcardField($field, $data)
                : [$field];

            foreach ($targets as $target) {
                $this->validateSingleField(
                    $target, $ruleSet, $data, $messages,
                    $allErrors, $validatedData, $excludedFields
                );

                // 首个失败即停止
                if ($this->stopOnFirstFailure && $allErrors !== []) {
                    return $this->finalize(new ValidationResult(false, $allErrors, $validatedData), $data);
                }
            }
        }

        // 移除被排除的字段
        foreach ($excludedFields as $excludedField) {
            unset($validatedData[$excludedField]);
        }

        return $this->finalize(
            new ValidationResult($allErrors === [], $allErrors, $validatedData),
            $data
        );
    }

    /**
     * 验证数据，失败时抛出异常，成功时返回通过的数据
     *
     * 适用于 Controller / Service 中"不通过则终止"的场景。
     *
     * @param array $data     待验证数据
     * @param array $rules    验证规则
     * @param array $messages 自定义错误消息
     * @return array 通过验证的数据
     * @throws Exception\ValidationException 验证失败时抛出
     */
    public function validateThrows(array $data, array $rules, array $messages = []): array
    {
        $result = $this->validate($data, $rules, $messages);

        if (!$result->isValid()) {
            throw new Exception\ValidationException($result);
        }

        return $result->validatedData();
    }

    /**
     * 设置是否在首个验证错误时停止
     *
     * @param bool $stop true 表示遇到第一个错误就立即返回
     * @return $this
     */
    public function stopOnFirstFailure(bool $stop = true): self
    {
        $this->stopOnFirstFailure = $stop;
        return $this;
    }

    /**
     * 注册验证前置回调
     *
     * 在验证开始前调用，可修改数据和规则。
     * 回调签名：function(array $data, array $rules): array 返回 [$data, $rules]
     *
     * @param callable $callback 前置回调
     * @return $this
     */
    public function beforeValidation(callable $callback): self
    {
        $this->beforeValidationCallback = $callback;
        return $this;
    }

    /**
     * 注册验证后置回调
     *
     * 在结果生成后调用，可用于追加业务级错误或改写结果。
     * 回调签名：function(ValidationResult $result, array $data): ?ValidationResult
     * 返回 null 表示保留原结果。
     *
     * @param callable $callback 后置回调
     * @return $this
     */
    public function afterValidation(callable $callback): self
    {
        $this->afterValidationCallback = $callback;
        return $this;
    }

    /**
     * 设置字段显示名映射
     *
     * 用于把 :attribute 占位符替换成业务可读的中文名，
     * 支持通配符键，如 ['users.*.name' => '用户姓名']。
     *
     * @param array<string, string> $names 字段名 => 显示名
     * @return $this
     */
    public function setAttributeNames(array $names): self
    {
        $this->attributeNames = $names;
        return $this;
    }

    /**
     * 合并默认错误消息模板
     *
     * @param array $messages 规则名 => 消息模板
     * @return $this
     */
    public function addMessages(array $messages): self
    {
        $this->defaultMessages = array_merge($this->defaultMessages, $messages);
        return $this;
    }

    /**
     * 动态添加自定义规则
     *
     * @param string                 $name 规则名称
     * @param RuleInterface|callable $rule 规则实例或闭包
     */
    public function addRule(string $name, RuleInterface|callable $rule): void
    {
        if ($rule instanceof RuleInterface) {
            $this->rules[$name] = $rule;
            return;
        }

        $this->rules[$name] = new class ($name, $rule) implements RuleInterface {
            public function __construct(
                private string $name,
                private $callback
            ) {
            }

            #[\Override]
            public function validate(string $field, mixed $value, array $params, array $data): ?string
            {
                return ($this->callback)($field, $value, $params, $data);
            }

            #[\Override]
            public function getName(): string
            {
                return $this->name;
            }
        };
    }

    /**
     * 批量添加自定义规则
     *
     * @param array<string, RuleInterface|callable> $rules 规则名 => 规则实例或闭包
     * @return $this
     */
    public function addRules(array $rules): self
    {
        foreach ($rules as $name => $rule) {
            $this->addRule((string) $name, $rule);
        }

        return $this;
    }

    /**
     * 判断规则是否可用（内置规则或已注册的自定义规则）
     */
    public function hasRule(string $name): bool
    {
        return isset($this->rules[$name])
            || isset(self::BUILTIN_RULES[$name])
            || $name === 'closure'
            || in_array($name, self::CONTROL_RULES, true);
    }

    /**
     * 获取全部可用规则名（已排序）
     *
     * @return list<string>
     */
    public function ruleNames(): array
    {
        $names = array_unique(array_merge(
            array_keys(self::BUILTIN_RULES),
            array_keys($this->rules),
            ['closure'],
            self::CONTROL_RULES
        ));

        sort($names);

        return array_values($names);
    }

    /**
     * 清空进程级共享规则池与解析缓存
     *
     * 常规业务无需调用，仅用于单元测试或热重载场景。
     */
    public static function flushCaches(): void
    {
        self::$sharedRules = [];
        self::$parseCache = [];
        self::$closureRules = null;
    }

    /**
     * 执行后置回调并返回最终结果
     */
    private function finalize(ValidationResult $result, array $data): ValidationResult
    {
        if ($this->afterValidationCallback === null) {
            return $result;
        }

        $replaced = ($this->afterValidationCallback)($result, $data);

        return $replaced instanceof ValidationResult ? $replaced : $result;
    }

    /**
     * 解析规则实例
     *
     * 查找顺序：实例级自定义规则 -> 进程级共享池 -> 内置规则映射（惰性实例化）
     */
    private function resolveRule(string $name): ?RuleInterface
    {
        if (isset($this->rules[$name])) {
            return $this->rules[$name];
        }

        if (isset(self::$sharedRules[$name])) {
            return self::$sharedRules[$name];
        }

        if ($name === 'closure') {
            return self::$sharedRules[$name] = new ClosureRule(static fn (): ?string => null);
        }

        $class = self::BUILTIN_RULES[$name] ?? null;

        if ($class === null) {
            return null;
        }

        return self::$sharedRules[$name] = new $class();
    }

    /**
     * 验证单个字段
     *
     * @param string       $field          字段名
     * @param array|string $ruleSet        规则定义
     * @param array        $data           完整数据
     * @param array        $messages       自定义消息
     * @param array        $allErrors      错误收集（引用传递）
     * @param array        $validatedData  通过数据收集（引用传递）
     * @param array        $excludedFields 排除字段收集（引用传递）
     */
    private function validateSingleField(
        string $field,
        array|string $ruleSet,
        array $data,
        array $messages,
        array &$allErrors,
        array &$validatedData,
        array &$excludedFields
    ): void {
        $parsedRules = $this->parseRules($ruleSet);

        $isNested = str_contains($field, '.');

        // 支持点号分隔的嵌套字段
        $value = $isNested
            ? $this->getNestedValue($data, $field)
            : ($data[$field] ?? null);

        $flags = $this->collectControlFlags($parsedRules);

        // 检查 sometimes：字段不存在则整体跳过
        if ($flags['sometimes']) {
            $exists = $isNested
                ? $this->hasNestedKey($data, $field)
                : array_key_exists($field, $data);

            if (!$exists) {
                return;
            }
        }

        // 检查 nullable：值为 null 时跳过后续规则
        if ($value === null && $flags['nullable']) {
            return;
        }

        $fieldErrors = [];
        $shouldExclude = false;

        foreach ($parsedRules as [$ruleName, $params]) {
            // 跳过控制类指令
            if (in_array($ruleName, self::CONTROL_RULES, true)) {
                continue;
            }

            $rule = $this->resolveRule($ruleName);

            if ($rule === null) {
                continue;
            }

            // 检查排除规则
            if ($ruleName === 'exclude_if' || $ruleName === 'exclude_unless') {
                if ($rule->validate($field, $value, $params, $data) !== null) {
                    $shouldExclude = true;
                }
                continue;
            }

            $errorKey = $rule->validate($field, $value, $params, $data);

            if ($errorKey !== null) {
                $fieldErrors[$ruleName] = $this->formatMessage(
                    $field, $ruleName, $value, $params, $messages, $errorKey
                );

                // bail：该字段遇到首个错误即停止后续规则
                if ($flags['bail']) {
                    break;
                }
            }
        }

        if ($shouldExclude) {
            $excludedFields[] = $field;
            return;
        }

        if ($fieldErrors !== []) {
            $allErrors[$field] = $fieldErrors;
            return;
        }

        // 只收集真实提交过的字段，未提交的字段不会以 null 混入通过数据
        $submitted = $isNested
            ? $this->hasNestedKey($data, $field)
            : array_key_exists($field, $data);

        if ($submitted) {
            $validatedData[$field] = $value;
        }
    }

    /**
     * 收集控制指令标记
     *
     * @param list<array{0: string, 1: array}> $parsedRules
     * @return array{sometimes: bool, nullable: bool, bail: bool}
     */
    private function collectControlFlags(array $parsedRules): array
    {
        $flags = ['sometimes' => false, 'nullable' => false, 'bail' => false];

        foreach ($parsedRules as [$ruleName]) {
            if (isset($flags[$ruleName])) {
                $flags[$ruleName] = true;
            }
        }

        return $flags;
    }

    /**
     * 展开通配符字段名，支持多级通配符（如 orders.*.items.*.sku）
     *
     * @return list<string>
     */
    private function expandWildcardField(string $field, array $data): array
    {
        $parts = explode('.', $field);
        $starIndex = array_search('*', $parts, true);

        if ($starIndex === false) {
            return [$field];
        }

        $prefix = implode('.', array_slice($parts, 0, $starIndex));
        $suffix = implode('.', array_slice($parts, $starIndex + 1));

        $baseData = $prefix === '' ? $data : $this->getNestedValue($data, $prefix);

        if (!is_array($baseData)) {
            return [];
        }

        $expanded = [];

        foreach (array_keys($baseData) as $index) {
            $current = $prefix === '' ? (string) $index : "{$prefix}.{$index}";

            if ($suffix !== '') {
                $current .= ".{$suffix}";
            }

            // 递归展开剩余通配符
            foreach ($this->expandWildcardField($current, $data) as $item) {
                $expanded[] = $item;
            }
        }

        return $expanded;
    }

    /**
     * 获取嵌套数据值
     */
    private function getNestedValue(array $data, string $path): mixed
    {
        $value = $data;

        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * 检查嵌套路径是否存在于数据中
     */
    private function hasNestedKey(array $data, string $path): bool
    {
        $value = $data;

        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return false;
            }
            $value = $value[$key];
        }

        return true;
    }

    /**
     * 解析规则定义
     *
     * @param array|string $ruleSet 规则定义，支持：
     *   - 字符串格式：'required|email|min:2'
     *   - 数组格式：['required', 'email', new SomeRule(), fn(...) => 'error']
     * @return list<array{0: string, 1: array}>
     */
    private function parseRules(array|string $ruleSet): array
    {
        // 纯字符串规则串可安全缓存（解析结果为不可变标量数组）
        if (is_string($ruleSet)) {
            if (isset(self::$parseCache[$ruleSet])) {
                return self::$parseCache[$ruleSet];
            }

            $parsed = $this->parseRuleStrings(explode('|', $ruleSet));

            if (count(self::$parseCache) >= self::PARSE_CACHE_LIMIT) {
                self::$parseCache = [];
            }

            return self::$parseCache[$ruleSet] = $parsed;
        }

        $ruleStrings = [];

        foreach ($ruleSet as $item) {
            if ($item instanceof RuleInterface) {
                $name = $item->getName();
                $this->rules[$name] ??= $item;
                $ruleStrings[] = $name;
                continue;
            }

            if ($item instanceof \Closure) {
                $ruleStrings[] = $this->registerClosureRule($item);
                continue;
            }

            $ruleStrings[] = (string) $item;
        }

        return $this->parseRuleStrings($ruleStrings);
    }

    /**
     * 注册闭包规则并返回其内部规则名
     *
     * 使用 WeakMap 缓存，闭包被回收后条目自动释放，避免长驻进程内存增长。
     */
    private function registerClosureRule(\Closure $closure): string
    {
        self::$closureRules ??= new \WeakMap();

        if (isset(self::$closureRules[$closure])) {
            [$name, $rule] = self::$closureRules[$closure];
        } else {
            $name = 'closure_' . spl_object_id($closure);
            $rule = new ClosureRule($closure);
            self::$closureRules[$closure] = [$name, $rule];
        }

        $this->rules[$name] = $rule;

        return $name;
    }

    /**
     * 把规则串数组解析为 [规则名, 参数] 结构
     *
     * @param list<string> $ruleStrings
     * @return list<array{0: string, 1: array}>
     */
    private function parseRuleStrings(array $ruleStrings): array
    {
        $parsed = [];

        foreach ($ruleStrings as $ruleStr) {
            $ruleStr = trim($ruleStr);

            if ($ruleStr === '') {
                continue;
            }

            $colonPos = strpos($ruleStr, ':');

            if ($colonPos === false) {
                $parsed[] = [$ruleStr, []];
                continue;
            }

            $ruleName = substr($ruleStr, 0, $colonPos);
            $paramStr = substr($ruleStr, $colonPos + 1);

            $parsed[] = [$ruleName, $this->parseParams($ruleName, $paramStr)];
        }

        return $parsed;
    }

    /**
     * 解析规则参数
     *
     * regex / not_regex / date_format 的参数整体保留，
     * 否则形如 regex:/^\d{3,5}$/ 的表达式会被逗号切断。
     */
    private function parseParams(string $ruleName, string $paramStr): array
    {
        if (in_array($ruleName, self::RAW_PARAM_RULES, true)) {
            return [$paramStr];
        }

        return array_map(trim(...), explode(',', $paramStr));
    }

    /**
     * 格式化错误消息
     */
    private function formatMessage(
        string $field,
        string $ruleName,
        mixed $value,
        array $params,
        array $messages,
        string $errorKey
    ): string {
        $wildcardKey = $this->toWildcardKey($field);

        $template = $messages["{$field}.{$ruleName}"]
            ?? ($wildcardKey !== null ? ($messages["{$wildcardKey}.{$ruleName}"] ?? null) : null)
            ?? $this->defaultMessages[$ruleName]
            ?? $errorKey;

        $replacements = [
            ':attribute' => $this->resolveAttribute($field, $wildcardKey, $messages),
            ':field'     => $field,
            ':value'     => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
            ':params'    => implode(', ', array_map(strval(...), $params)),
        ];

        foreach ($params as $index => $param) {
            $replacements[":param_{$index}"] = (string) $param;
        }

        return strtr($template, $replacements);
    }

    /**
     * 解析字段显示名
     *
     * 优先级：本次调用的 messages -> 全局 attributeNames -> 通配符匹配 -> 原字段名
     */
    private function resolveAttribute(string $field, ?string $wildcardKey, array $messages): string
    {
        return $messages["{$field}.attribute"]
            ?? $this->attributeNames[$field]
            ?? ($wildcardKey !== null
                ? ($messages["{$wildcardKey}.attribute"] ?? $this->attributeNames[$wildcardKey] ?? $field)
                : $field);
    }

    /**
     * 把含数字下标的嵌套字段名转成通配符形式，如 users.0.name => users.*.name
     */
    private function toWildcardKey(string $field): ?string
    {
        if (!str_contains($field, '.')) {
            return null;
        }

        $wildcard = preg_replace('/(?<=^|\.)\d+(?=\.|$)/', '*', $field);

        return $wildcard === $field ? null : $wildcard;
    }
}

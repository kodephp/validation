<?php

declare(strict_types=1);

namespace Kode\Validation;

use Kode\Validation\Contract\ValidatorInterface;
use Kode\Validation\Rule\AfterRule;
use Kode\Validation\Rule\AlnumRule;
use Kode\Validation\Rule\AlphaRule;
use Kode\Validation\Rule\ArrayRule;
use Kode\Validation\Rule\BeforeRule;
use Kode\Validation\Rule\BetweenRule;
use Kode\Validation\Rule\BooleanRule;
use Kode\Validation\Rule\ConfirmedRule;
use Kode\Validation\Rule\DateRule;
use Kode\Validation\Rule\DifferentRule;
use Kode\Validation\Rule\EmailRule;
use Kode\Validation\Rule\EndsWithRule;
use Kode\Validation\Rule\InRule;
use Kode\Validation\Rule\IpRule;
use Kode\Validation\Rule\JsonRule;
use Kode\Validation\Rule\MaxRule;
use Kode\Validation\Rule\MinRule;
use Kode\Validation\Rule\NumericRule;
use Kode\Validation\Rule\ProhibitedIfRule;
use Kode\Validation\Rule\ProhibitedRule;
use Kode\Validation\Rule\RegexRule;
use Kode\Validation\Rule\RequiredRule;
use Kode\Validation\Rule\RuleInterface;
use Kode\Validation\Rule\SameRule;
use Kode\Validation\Rule\StartsWithRule;
use Kode\Validation\Rule\UrlRule;

/**
 * 核心验证器，协程安全
 *
 * 所有验证过程的中间状态均使用局部变量，不存储在实例属性中，
 * 确保多协程并发验证互不干扰。
 *
 * 使用示例：
 * <code>
 * $validator = new Validator(require 'config/validation.php');
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
     * @var array<string, RuleInterface> 已注册的规则映射，规则名 => 规则实例
     */
    private array $rules = [];

    /**
     * @var array 默认消息模板，规则名 => 消息模板
     */
    private array $defaultMessages;

    /**
     * @var bool 是否在首个错误时停止验证
     */
    private bool $stopOnFirstFailure = false;

    /**
     * 构造函数
     *
     * @param array $defaultMessages 默认消息模板，通常从 config/validation.php 加载
     */
    public function __construct(array $defaultMessages = [])
    {
        $this->defaultMessages = $defaultMessages;
        $this->registerBuiltinRules();
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
    public function validate(array $data, array $rules, array $messages = []): ValidationResult
    {
        // 使用局部变量存储中间状态，不污染实例属性
        $allErrors = [];
        $validatedData = [];

        foreach ($rules as $field => $ruleSet) {
            // 处理嵌套数组字段：items.*.name → 展开为 items.0.name, items.1.name, ...
            if (str_contains($field, '*')) {
                $expanded = $this->expandWildcardField($field, $data);
                foreach ($expanded as $expandedField) {
                    $this->validateSingleField(
                        $expandedField, $ruleSet, $data, $messages,
                        $allErrors, $validatedData
                    );
                    if ($this->stopOnFirstFailure && $allErrors !== []) {
                        return new ValidationResult(false, $allErrors, $validatedData);
                    }
                }
                continue;
            }

            $this->validateSingleField($field, $ruleSet, $data, $messages, $allErrors, $validatedData);

            // 首个失败即停止
            if ($this->stopOnFirstFailure && $allErrors !== []) {
                return new ValidationResult(false, $allErrors, $validatedData);
            }
        }

        return new ValidationResult($allErrors === [], $allErrors, $validatedData);
    }

    /**
     * 设置是否在首个验证错误时停止
     *
     * @param bool $stop true 表示遇到第一个错误就立即返回
     */
    public function stopOnFirstFailure(bool $stop = true): self
    {
        $this->stopOnFirstFailure = $stop;
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
        if (is_callable($rule)) {
            $this->rules[$name] = new class($name, $rule) implements RuleInterface {
                public function __construct(
                    private string $name,
                    private $callback
                ) {
                }

                public function validate(string $field, mixed $value, array $params, array $data): ?string
                {
                    return ($this->callback)($field, $value, $params, $data);
                }

                public function getName(): string
                {
                    return $this->name;
                }
            };
        } else {
            $this->rules[$name] = $rule;
        }
    }

    /**
     * 验证单个字段
     *
     * @param string       $field         字段名
     * @param array|string $ruleSet       规则定义
     * @param array        $data          完整数据
     * @param array        $messages      自定义消息
     * @param array        $allErrors     错误收集（引用传递）
     * @param array        $validatedData 通过数据收集（引用传递）
     */
    private function validateSingleField(
        string $field,
        array|string $ruleSet,
        array $data,
        array $messages,
        array &$allErrors,
        array &$validatedData
    ): void {
        $parsedRules = $this->parseRules($ruleSet);

        // 支持点号分隔的嵌套字段：items.0.name → $data['items'][0]['name']
        $value = str_contains($field, '.')
            ? $this->getNestedValue($data, $field)
            : ($data[$field] ?? null);

        // 检查 sometimes：字段不存在且规则中含 sometimes 则跳过
        if (!array_key_exists($field, $data) && !str_contains($field, '.')) {
            foreach ($parsedRules as [$ruleName]) {
                if ($ruleName === 'sometimes') {
                    return;
                }
            }
        }

        // 对于点号嵌套字段的 sometimes 检查
        if (str_contains($field, '.')) {
            $nestedExists = $this->getNestedValue($data, $field) !== null
                || $this->hasNestedKey($data, $field);
            if (!$nestedExists) {
                foreach ($parsedRules as [$ruleName]) {
                    if ($ruleName === 'sometimes') {
                        return;
                    }
                }
            }
        }

        // 检查 nullable：字段值为 null 且规则中含 nullable 则跳过
        if ($value === null) {
            foreach ($parsedRules as [$ruleName]) {
                if ($ruleName === 'nullable') {
                    return;
                }
            }
        }

        $fieldErrors = [];

        foreach ($parsedRules as [$ruleName, $params]) {
            // 跳过控制类规则
            if ($ruleName === 'sometimes' || $ruleName === 'nullable') {
                continue;
            }

            $rule = $this->rules[$ruleName] ?? null;
            if ($rule === null) {
                continue;
            }

            $errorKey = $rule->validate($field, $value, $params, $data);

            if ($errorKey !== null) {
                $fieldErrors[$ruleName] = $this->formatMessage(
                    $field, $ruleName, $value, $params, $messages, $errorKey
                );
            }
        }

        if ($fieldErrors !== []) {
            $allErrors[$field] = $fieldErrors;
        } else {
            $validatedData[$field] = $value;
        }
    }

    /**
     * 展开通配符字段名
     *
     * 将 items.*.name 展开为 ['items.0.name', 'items.1.name', ...]，
     * 依次遍历 data 中对应的数组索引。
     *
     * @param string $field 含 * 的字段名
     * @param array  $data  完整数据
     * @return array 展开后的字段名列表
     */
    private function expandWildcardField(string $field, array $data): array
    {
        $parts = explode('.', $field);
        $starIndex = array_search('*', $parts, true);
        $prefix = implode('.', array_slice($parts, 0, $starIndex));
        $suffix = implode('.', array_slice($parts, $starIndex + 1));

        $expanded = [];
        $baseData = $this->getNestedValue($data, $prefix);

        if (is_array($baseData)) {
            foreach (array_keys($baseData) as $index) {
                $expanded[] = $suffix !== ''
                    ? "{$prefix}.{$index}.{$suffix}"
                    : "{$prefix}.{$index}";
            }
        }

        return $expanded;
    }

    /**
     * 获取嵌套数据值
     *
     * 从 data 中按点号路径提取值：getNestedValue(['a' => ['b' => 1]], 'a.b') → 1
     *
     * @param array  $data 数据源
     * @param string $path 点号分隔路径
     * @return mixed 提取的值，路径不存在返回 null
     */
    private function getNestedValue(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * 检查嵌套路径是否存在于数据中
     *
     * @param array  $data 数据源
     * @param string $path 点号分隔路径
     * @return bool 路径是否存在
     */
    private function hasNestedKey(array $data, string $path): bool
    {
        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return false;
            }
            $value = $value[$key];
        }

        return true;
    }

    /**
     * 注册内置规则
     */
    private function registerBuiltinRules(): void
    {
        $builtinRules = [
            new RequiredRule(),
            new EmailRule(),
            new MinRule(),
            new MaxRule(),
            new BetweenRule(),
            new InRule(),
            new RegexRule(),
            new ConfirmedRule(),
            new UrlRule(),
            new IpRule(),
            new NumericRule(),
            new AlphaRule(),
            new AlnumRule(),
            new DateRule(),
            new SameRule(),
            new DifferentRule(),
            new JsonRule(),
            new ArrayRule(),
            new BooleanRule(),
            new StartsWithRule(),
            new EndsWithRule(),
            new AfterRule(),
            new BeforeRule(),
            new ProhibitedRule(),
            new ProhibitedIfRule(),
        ];

        foreach ($builtinRules as $rule) {
            $this->rules[$rule->getName()] = $rule;
        }

        // 注册内置控制规则（sometimes 和 nullable）
        $this->rules['sometimes'] = new class implements RuleInterface {
            public function validate(string $field, mixed $value, array $params, array $data): ?string
            {
                return null;
            }

            public function getName(): string
            {
                return 'sometimes';
            }
        };

        $this->rules['nullable'] = new class implements RuleInterface {
            public function validate(string $field, mixed $value, array $params, array $data): ?string
            {
                return null;
            }

            public function getName(): string
            {
                return 'nullable';
            }
        };
    }

    /**
     * 解析规则定义
     *
     * @param array|string $ruleSet 规则定义
     * @return array 解析后的规则列表，每项为 [规则名, 参数数组]
     */
    private function parseRules(array|string $ruleSet): array
    {
        if (is_string($ruleSet)) {
            $ruleStrings = explode('|', $ruleSet);
        } else {
            $ruleStrings = [];
            foreach ($ruleSet as $item) {
                if ($item instanceof RuleInterface) {
                    $name = $item->getName();
                    if (!isset($this->rules[$name])) {
                        $this->rules[$name] = $item;
                    }
                    $ruleStrings[] = $name;
                } else {
                    $ruleStrings[] = (string) $item;
                }
            }
        }

        $parsed = [];

        foreach ($ruleStrings as $ruleStr) {
            $ruleStr = trim($ruleStr);
            if ($ruleStr === '') {
                continue;
            }

            $colonPos = strpos($ruleStr, ':');

            if ($colonPos === false) {
                $ruleName = $ruleStr;
                $params = [];
            } else {
                $ruleName = substr($ruleStr, 0, $colonPos);
                $paramStr = substr($ruleStr, $colonPos + 1);
                $params = $this->parseParams($ruleName, $paramStr);
            }

            $parsed[] = [$ruleName, $params];
        }

        return $parsed;
    }

    /**
     * 解析规则参数
     */
    private function parseParams(string $ruleName, string $paramStr): array
    {
        if ($ruleName === 'in') {
            return array_map('trim', explode(',', $paramStr));
        }

        return array_map('trim', explode(',', $paramStr));
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
        $template = $messages["{$field}.{$ruleName}"]
            ?? $this->defaultMessages[$ruleName]
            ?? $errorKey;

        $attribute = $messages["{$field}.attribute"] ?? $field;

        $replacements = [
            ':attribute' => $attribute,
            ':value'     => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
        ];

        foreach ($params as $index => $param) {
            $replacements[":param_{$index}"] = (string) $param;
        }

        return strtr($template, $replacements);
    }
}

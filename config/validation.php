<?php

declare(strict_types=1);

/**
 * kode/validation 默认错误消息模板
 *
 * 占位符说明：
 *   :attribute  - 字段名（支持别名）
 *   :value      - 字段当前值
 *   :param_0    - 规则第一个参数
 *   :param_1    - 规则第二个参数
 */
return [
    'required'        => ':attribute 不能为空',
    'email'           => ':attribute 不是有效的邮箱地址',
    'min'             => ':attribute 不能小于 :param_0',
    'max'             => ':attribute 不能大于 :param_0',
    'between'         => ':attribute 必须在 :param_0 到 :param_1 之间',
    'in'              => ':attribute 不在允许的范围内',
    'regex'           => ':attribute 格式不正确',
    'confirmed'       => ':attribute 两次输入不一致',
    'url'             => ':attribute 不是有效的 URL 地址',
    'ip'              => ':attribute 不是有效的 IP 地址',
    'numeric'         => ':attribute 必须是数字',
    'alpha'           => ':attribute 只能包含字母',
    'alpha_num'       => ':attribute 只能包含字母和数字',
    'date'            => ':attribute 不是有效的日期格式',
    'same'            => ':attribute 与 :param_0 不一致',
    'different'       => ':attribute 不能与 :param_0 相同',
    'json'            => ':attribute 不是有效的 JSON 格式',
    'array'           => ':attribute 必须是数组类型',
    'boolean'         => ':attribute 必须是布尔值',
    'starts_with'     => ':attribute 必须以 :param_0 开头',
    'ends_with'       => ':attribute 必须以 :param_0 结尾',
    'after'           => ':attribute 必须晚于 :param_0',
    'before'          => ':attribute 必须早于 :param_0',
    'prohibited'      => ':attribute 不允许存在',
    'prohibited_if'   => '当 :param_0 为 :param_1 时，:attribute 不允许存在',
    'string'          => ':attribute 必须是字符串',
    'integer'         => ':attribute 必须是整数',
    'float'           => ':attribute 必须是浮点数',
    'distinct'        => ':attribute 中的值不能重复',
    'size'            => ':attribute 必须为 :param_0',
    'gt'              => ':attribute 必须大于 :param_0',
    'gte'             => ':attribute 必须大于等于 :param_0',
    'lt'              => ':attribute 必须小于 :param_0',
    'lte'             => ':attribute 必须小于等于 :param_0',
    'accepted'        => ':attribute 必须接受（yes/on/1/true）',
    'declined'        => ':attribute 必须拒绝（no/off/0/false）',
    'digits'          => ':attribute 必须是 :param_0 位数字',
    'digits_between'  => ':attribute 必须是 :param_0 到 :param_1 位数字',
    'required_if'     => '当 :param_0 为 :param_1 时，:attribute 不能为空',
    'required_unless' => '当 :param_0 不为 :param_1 时，:attribute 不能为空',
    'required_with'   => '当 :param_0 存在时，:attribute 不能为空',
    'chinese'         => ':attribute 必须包含中文字符',
    'english'         => ':attribute 只能包含英文字母',
    'pure_digits'     => ':attribute 只能包含纯数字',
    'special_chars'   => ':attribute 必须包含特殊字符',
    'start_with_english' => ':attribute 必须以英文字母开头',
    'chinese_alpha_num'  => ':attribute 只能包含中文、英文和数字',
    'username'        => ':attribute 必须以英文字母开头，后续只能使用字母、数字和下划线',
    'prefix_mixed'    => ':attribute 前 :param_0 位必须为英文字母，后续只能包含字母和数字',
    'closure'         => ':attribute 验证失败',
    'length'          => ':attribute 长度必须为 :param_0 个字符',
    'uuid'            => ':attribute 必须是有效的 UUID 格式',
    'future'          => ':attribute 必须是未来的日期',
    'past'            => ':attribute 必须是过去的日期',
    'timezone'        => ':attribute 必须是有效的时区',
    'mac_address'     => ':attribute 必须是有效的 MAC 地址',
];

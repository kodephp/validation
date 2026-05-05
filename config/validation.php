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
    'required'   => ':attribute 不能为空',
    'email'      => ':attribute 不是有效的邮箱地址',
    'min'        => ':attribute 不能小于 :param_0',
    'max'        => ':attribute 不能大于 :param_0',
    'between'    => ':attribute 必须在 :param_0 到 :param_1 之间',
    'in'         => ':attribute 不在允许的范围内',
    'regex'      => ':attribute 格式不正确',
    'confirmed'  => ':attribute 两次输入不一致',
    'url'        => ':attribute 不是有效的 URL 地址',
    'ip'         => ':attribute 不是有效的 IP 地址',
    'numeric'    => ':attribute 必须是数字',
    'alpha'      => ':attribute 只能包含字母',
    'alpha_num'  => ':attribute 只能包含字母和数字',
    'date'       => ':attribute 不是有效的日期格式',
    'same'       => ':attribute 与 :param_0 不一致',
    'different'  => ':attribute 不能与 :param_0 相同',
];

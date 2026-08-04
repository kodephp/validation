<?php

declare(strict_types=1);

/**
 * kode/validation 默认错误消息模板
 *
 * 占位符说明：
 *   :attribute  - 字段显示名（支持别名与通配符映射）
 *   :field      - 原始字段名
 *   :value      - 字段当前值
 *   :params     - 全部参数，以「, 」连接
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

    // v1.9.0 新增：中国本地化
    'mobile'          => ':attribute 不是有效的手机号码',
    'id_card'         => ':attribute 不是有效的身份证号码',
    'bank_card'       => ':attribute 不是有效的银行卡号',
    'postal_code'     => ':attribute 不是有效的邮政编码',
    'chinese_name'    => ':attribute 不是有效的中文姓名',
    'plate_number'    => ':attribute 不是有效的车牌号码',

    // v1.9.0 新增：通用格式
    'ulid'            => ':attribute 必须是有效的 ULID',
    'semver'          => ':attribute 必须是有效的语义化版本号',
    'base64'          => ':attribute 必须是有效的 Base64 字符串',
    'hex_color'       => ':attribute 必须是有效的十六进制颜色值',
    'slug'            => ':attribute 只能包含小写字母、数字和连字符',
    'ipv4'            => ':attribute 不是有效的 IPv4 地址',
    'ipv6'            => ':attribute 不是有效的 IPv6 地址',
    'port'            => ':attribute 必须是 1-65535 之间的端口号',
    'domain'          => ':attribute 不是有效的域名',
    'latitude'        => ':attribute 必须是 -90 到 90 之间的纬度值',
    'longitude'       => ':attribute 必须是 -180 到 180 之间的经度值',
    'ascii'           => ':attribute 只能包含可打印的 ASCII 字符',
    'lowercase'       => ':attribute 必须全部为小写',
    'uppercase'       => ':attribute 必须全部为大写',

    // v1.9.0 新增：逻辑与条件
    'not_in'            => ':attribute 不能使用该值',
    'not_regex'         => ':attribute 格式不符合要求',
    'filled'            => ':attribute 提交时不能为空',
    'present'           => ':attribute 字段必须存在',
    'missing'           => ':attribute 字段不允许提交',
    'multiple_of'       => ':attribute 必须是 :param_0 的倍数',
    'date_format'       => ':attribute 必须符合 :param_0 格式',
    'enum'              => ':attribute 不在允许的枚举值范围内',
    'contains'          => ':attribute 必须包含 :params',
    'doesnt_start_with' => ':attribute 不能以 :params 开头',
    'doesnt_end_with'   => ':attribute 不能以 :params 结尾',
];

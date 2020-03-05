<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/19
 * Time: 10:57
 */

/**
 * 错误代码分配
 *
 * 公共: 10xx
 * 会话：11xx
 * 模型：12xx - 13xx
 * 业务：14xx - 15xx
 */

const CODE_SUCCEED = 0;
const CODE_ERROE = 1;
const CODE_EXCEPTION = 2;

const CODE_COM_CAPTCHA = 1001;          // 全局:验证码
const CODE_COM_PARAM = 1002;            // 全局:输入参数
const CODE_COM_UNABLE_PROCESS = 1004;   // 全局:无法处理
const CODE_COM_CSRF_INVALID = 1006;     // 全局:CSRF无效

const CODE_MODEL_TRANSACTION = 1201;    // 模型:事务
const CODE_MODEL_OPTIMISTIC_LOCK = 1202; // 模型:乐观锁

const CODE_CONV_VERIFY = 1101;          // 会话:验证
const CODE_CONV_NOACCESS = 1102;        // 会话:访问
const CODE_CONV_LOGIN = 1103;           // 会话:登陆
const CODE_CONV_LASTING = 1104;         // 会话:持久令牌
const CODE_CONV_AUTHOR_INVALID = 1105;  // 会话:授权无效
const CODE_CONV_ACCESS_CONTROL = 1106;  // 会话:被访问控制阻止

const CODE_DICT = [
    CODE_SUCCEED => 'succeed',
    CODE_ERROE => 'error',
    CODE_EXCEPTION => 'server exception',

    CODE_COM_CAPTCHA => '验证码错误',

    CODE_CONV_VERIFY => 'Access is invalid',
    CODE_CONV_NOACCESS => 'No permission to access',
    CODE_CONV_LOGIN => 'Login failed',
    CODE_CONV_LASTING => 'Invalid iasting',

];

const STATUS_TEXTS = [
    100 => 'Continue',
    101 => 'Switching Protocols',
    102 => 'Processing',            // RFC2518
    103 => 'Early Hints',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    207 => 'Multi-Status',          // RFC4918
    208 => 'Already Reported',      // RFC5842
    226 => 'IM Used',               // RFC3229
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    308 => 'Permanent Redirect',    // RFC7238
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Payload Too Large',
    414 => 'URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Range Not Satisfiable',
    417 => 'Expectation Failed',
    418 => 'I\'m a teapot',                                               // RFC2324
    421 => 'Misdirected Request',                                         // RFC7540
    422 => 'Unprocessable Entity',                                        // RFC4918
    423 => 'Locked',                                                      // RFC4918
    424 => 'Failed Dependency',                                           // RFC4918
    425 => 'Too Early',                                                   // RFC-ietf-httpbis-replay-04
    426 => 'Upgrade Required',                                            // RFC2817
    428 => 'Precondition Required',                                       // RFC6585
    429 => 'Too Many Requests',                                           // RFC6585
    431 => 'Request Header Fields Too Large',                             // RFC6585
    449 => 'Retry With',
    451 => 'Unavailable For Legal Reasons',                               // RFC7725
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    506 => 'Variant Also Negotiates',                                     // RFC2295
    507 => 'Insufficient Storage',                                        // RFC4918
    508 => 'Loop Detected',                                               // RFC5842
    510 => 'Not Extended',                                                // RFC2774
    511 => 'Network Authentication Required',                             // RFC6585
];

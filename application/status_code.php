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
 * 支付：2000 - 2199
 */
const CODE_SUCCEED = 0;

const CODE_COM_CAPTCHA = 1001;          // 全局:验证码
const CODE_COM_PARAM = 1002;            // 全局:输入参数
const CODE_COM_DATA_NOT_EXIST = 1003;   // 全局:数据不存在
const CODE_COM_UNABLE_PROCESS = 1004;   // 全局:无法处理
const CODE_COM_CSRF_INVALID = 1006;     // 全局:CSRF无效
const CODE_COM_REQUEST_INVALID = 1007;  // 全局:请求无效
const CODE_COM_VERIFY_FAILURE = 1007;   // 全局:验证失败

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
    CODE_COM_CAPTCHA => '验证码错误',
    CODE_CONV_VERIFY => 'Access is invalid',
    CODE_CONV_NOACCESS => 'No permission to access',
    CODE_CONV_LOGIN => 'Login failed',
    CODE_CONV_LASTING => 'Invalid iasting',

];
const CODE_MODEL_NOT_EXIST = 1201; // 模型不存在

//业务
const CODE_LOGIC_OPEN_ROOM = 1401; //开房流程失败
const CODE_LOGIC_STAY_ROOM = 1402; //续住流程失败
const CODE_LOGIC_REPLACE_ROOM = 1403; //换房流程失败

const CODE_LOGIC_SCHEDULE = 1410; //排期操作失败
const CODE_LOGIC_COST_CALCULATE = 1411; //计算费用失败

const CODE_LOGIC_PAY = 2000;
const CODE_LOGIC_PAY_WECHAT = 2010;

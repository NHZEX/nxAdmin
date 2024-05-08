<?php

/** @noinspection PhpUnused */
/** 非框架强依赖功能迁移出来，弃用该文件 */

use think\db\ConnectionInterface;
use think\db\PDOConnection;
use think\facade\App;
use think\facade\Db;
use think\facade\Request;
use think\Response;
use think\response\View;
use think\route\Resource;
use function Zxin\Str\strcut_omit;

/**
 * 渲染模板输出
 * @param array    $vars     模板变量
 * @param int      $code     状态码
 * @param callable $filter   内容过滤
 * @return View
 */
function view_current($vars = [], $code = 200, $filter = null): View
{
    /** @var View $view */
    $view = Response::create('', 'view', $code);
    return $view->assign($vars)->filter($filter);
}

function db_transaction(callable $callback, string $name = null)
{
    return Db::connect($name)->transaction($callback);
}

function return_raw_value($x)
{
    return $x;
}

/**
 * @param string $ua
 * @return array
 */
function parse_user_agent(string $ua)
{
    static $preg = '~(?<product>[\w\.]+)\/(?<version>[\w\.]+)\s?(?:\((?<comment>[\w\.]+)\))?~';

    if (preg_match_all($preg, $ua, $m, PREG_SET_ORDER)) {
        return $m;
    } else {
        return [];
    }
}

/**
 * url_hash
 * @param string|null $url
 * @param string      $prefix
 * @return string
 */
function url_hash(?string $url, string $prefix = 'page-'): string
{
    if (null === $url) {
        $url = Request::baseUrl();
    }

    $info = parse_url($url);
    $url = $info ? ($info['path'] ?? '/') : '/';
    return $prefix . crc32($url);
}

/**
 * 获取随机字符串
 * @param int $length
 * @param string|null $chars
 * @return string
 */
function get_rand_str(int $length = 8, ?string $chars = null): string
{
    if (empty($chars)) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    }
    $text = '';
    $chars_max_index = \strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $text .= $chars[random_int(0, $chars_max_index)];
    }
    return $text;
}

/**
 * 递归取出数组所有值 (递归重建数组索引)
 * @param array       $arr
 * @param string|null $filter_key
 * @return array
 * @link https://stackoverflow.com/a/11943744/10242420
 */
function array_values_recursive(array $arr, ?string $filter_key = null)
{
    foreach ($arr as $key => $value) {
        if (\is_array($value)) {
            $arr[$key] = array_values_recursive($value, $filter_key);
        }
    }

    if ($filter_key === null) {
        $arr = array_values($arr);
    } elseif (isset($arr[$filter_key])) {
        $arr[$filter_key] = array_values($arr[$filter_key]);
    }

    return $arr;
}

/**
 * 查询当前链接 mysql 版本
 * @param string|null $connect
 * @return string
 */
function query_mysql_version(string $connect = null)
{
    $sql = 'select version() as mysqlver';
    if ($connect) {
        $_version = Db::connect($connect, true)->query($sql);
    } else {
        $_version = Db::connect()->query($sql);
    }
    return array_pop($_version)['mysqlver'];
}

/**
 * 查询数据库版本
 * @param string|ConnectionInterface|null $connect
 * @param bool                            $driver
 * @return string
 */
function db_version(string|ConnectionInterface|null $connect = null, bool $driver = false): string
{
    // 暂不支持分布式数据库
    if (!$connect instanceof ConnectionInterface) {
        /** @var PDOConnection|object $connect */
        $connect = ($connect ? Db::connect($connect, true) : Db::connect());
    }
    if (!$connect instanceof PDOConnection) {
        throw new RuntimeException('only support PDOConnection');
    }
    try {
        $ref = new ReflectionMethod($connect, 'initConnect');
        $ref->setAccessible(true);
        $ref->invoke($connect);
    } catch (ReflectionException|\PDOException $e) {
        throw new RuntimeException('invoke method initConnect() exception: ' . $e->getMessage(), -1, $e);
    }
    /** @var PDO $pdo */
    $pdo = $connect->getPdo();
    $prefix = $driver ? ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . ' ') : '';
    return $prefix . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
}

/**
 * 查询当前链接 mysql 是否存在指定库
 * @param string      $database
 * @param string|null $connect
 * @return bool
 */
function query_mysql_exist_database(string $database, string $connect = null): bool
{
    /** @noinspection SqlNoDataSourceInspection SqlDialectInspection */
    $sql = "select * from `INFORMATION_SCHEMA`.`SCHEMATA` where `SCHEMA_NAME`='{$database}'";
    if ($connect) {
        $list = Db::connect($connect, true)->query($sql);
    } else {
        $list = Db::connect()->query($sql);
    }
    return (is_countable($list) ? \count($list) : 0) > 0;
}

/**
 * 多字节字符串按照字节长度进行截取
 * @deprecated
 * @param  string $string 字符串
 * @param  int $length 截取长度
 * @param  string $dot 省略符
 * @param  string|null $charset 编码
 * @return string
 */
function mb_strcut_omit(string $string, int $length, string $dot = '...', ?string $charset = null): string
{
    return strcut_omit($string, $length, $dot, $charset);
}

/**
 * @param string $rule
 * @param string $route
 * @param array  $ruleModel
 * @return Resource
 */
function roule_resource(string $rule, string $route, array $ruleModel = [])
{
    $r = App::getInstance()->route;
    $r->rest($ruleModel + ROUTE_DEFAULT_RESTFULL, true);
    $result = $r->resource($rule, $route);
    $r->rest(ROUTE_DEFAULT_RESTFULL, true);
    return $result;
}

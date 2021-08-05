<?php

/** @noinspection PhpUnused */

use Swoole\Coroutine;
use Swoole\Server;
use think\db\PDOConnection;
use think\facade\App;
use think\facade\Db;
use think\facade\Request;
use think\Response;
use think\response\View;
use think\route\Resource;
use think\swoole\pool\Proxy;
use function Zxin\Str\strcut_omit;
use function Zxin\Util\base64_urlsafe_decode;
use function Zxin\Util\base64_urlsafe_encode;

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

/**
 * @param array|object $data
 * @return string
 * @throws \app\Exception\JsonException
 */
function json_encode_throw_on_error($data): string
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (JSON_ERROR_NONE !== $last_error = json_last_error()) {
        $last_error_msg = json_last_error_msg();
        json_encode([]);    // 复位错误
        throw new \app\Exception\JsonException(
            sprintf('Json Encode Fail: %d - %s', $last_error, $last_error_msg)
        );
    }

    return $json;
}

/**
 * @param string $json
 * @return array
 * @throws \app\Exception\JsonException
 */
function json_decode_throw_on_error(string $json): array
{
    $data = json_decode($json, true);

    if (JSON_ERROR_NONE !== $last_error = json_last_error()) {
        $last_error_msg = json_last_error_msg();
        json_decode('[]');    // 复位错误
        throw new \app\Exception\JsonException(
            sprintf('Json Decode Fail: %d - %s', $last_error, $last_error_msg)
        );
    }

    return $data;
}

function return_raw_value($x)
{
    return $x;
}

/**
 * Base64 Url安全编码
 * @param string $data
 * @return string
 * @link http://php.net/manual/zh/function.base64-encode.php
 */
function base64url_encode(string $data): string
{
    return base64_urlsafe_encode($data);
}

/**
 * Base64 Url安全解码
 * @param string $data
 * @param bool   $strict
 * @return false|string
 * @link http://php.net/manual/zh/function.base64-encode.php
 */
function base64url_decode(string $data, bool $strict = true)
{
    return base64_urlsafe_decode($data, $strict);
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
 * 当前运行环境是否CLI
 * @return bool
 */
function is_cli()
{
    return 'cli' === PHP_SAPI;
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
    $chars_max_index = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $text .= $chars[mt_rand(0, $chars_max_index)];
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
        if (is_array($value)) {
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
 * @param string|null $connect
 * @param bool        $driver
 * @return string
 */
function db_version(?string $connect = null, bool $driver = false): string
{
    // 暂不支持分布式数据库
    /** @var PDOConnection|object $connect */
    $connect = ($connect ? Db::connect($connect, true) : Db::connect());
    $call = function ($connection) {
        try {
            if (!$connection instanceof PDOConnection) {
                throw new RuntimeException('only support PDOConnection');
            }
            $ref = new ReflectionMethod($connection, 'initConnect');
            $ref->setAccessible(true);
            $ref->invoke($connection);
        } catch (ReflectionException $e) {
            throw new RuntimeException('invoke method initConnect() exception', -1, $e);
        }
    };
    if ($connect instanceof Proxy) {
        $initConnect = function () use ($call) {
            $connection = $this->getPoolConnection();
            if ($connection->{$this::KEY_RELEASED}) {
                throw new RuntimeException("Connection already has been released!");
            }
            $call($connection);
        };
        $initConnect->call($connect);
    } else {
        $call($connect);
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
    return count($list) > 0;
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

function preload_statistics(): string
{
    if (!extension_loaded('Zend OPcache') || !function_exists('opcache_get_status')) {
        return 'opcache does not exist';
    }
    $status = opcache_get_status(false);
    if (!isset($status['preload_statistics'])) {
        return 'opcache preload not activated';
    }
    $status = $status['preload_statistics'];
    return sprintf(
        'mem: %.2fMB, function: %d, class: %d, script: %d',
        $status['memory_consumption'] / 1024 / 1024,
        count($status['functions']),
        count($status['classes']),
        count($status['scripts'])
    );
}

/**
 * @param mixed $value
 * @param int   $options
 * @param int   $depth
 * @return false|string
 */
function json_encode_ex($value, int $options = 0, int $depth = 512)
{
    $options |= JSON_UNESCAPED_UNICODE;
    $options |= JSON_UNESCAPED_SLASHES;
    if (PHP_VERSION_ID >= 70300) {
        $options |= JSON_THROW_ON_ERROR;
    }
    return json_encode($value, $options, $depth);
}

/**
 * @param string $value
 * @param bool   $assoc
 * @param int    $depth
 * @param int    $options
 * @return mixed
 */
function json_decode_ex(string $value, bool $assoc = true, int $depth = 512, int $options = 0)
{
    if (PHP_VERSION_ID >= 70300) {
        $options |= JSON_THROW_ON_ERROR;
    }
    return json_decode($value, $assoc, $depth, $options);
}

function get_server_software()
{
    if (is_cli()) {
        if (swoole_loaded()) {
            return \app()->has(Server::class) ? ('swoole/' . SWOOLE_VERSION) : 'cli';
        } else {
            return 'cli';
        }
    } else {
        return $_SERVER['SERVER_SOFTWARE'] ?? 'unknown';
    }
}

function swoole_loaded(): bool
{
    return extension_loaded('swoole');
}

function safe_get_coroutine_id()
{
    if (swoole_loaded()) {
        return Coroutine::getCid();
    } else {
        return -1;
    }
}

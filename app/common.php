<?php

use think\facade\App;
use think\facade\Db;
use think\facade\Request;
use think\Response;
use think\response\View;
use think\route\Resource;

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
 * @param array  $tree
 * @param string|array $name
 * @param string $key
 * @param int    $level
 * @return array
 */
function tree_to_table(array $tree, $name = ['name', '__name'], string $key = 'children', int $level = 0)
{
    if (is_array($name)) {
        [$nameKey, $newNameKey] = $name;
    } elseif (strpos($name, '|') > 0) {
        [$nameKey, $newNameKey] = explode('|', $name);
    } else {
        $nameKey = $name;
        $newNameKey = '__' . $name;
    }
    $data = [];
    foreach ($tree as $item) {
        $item['__level'] = $level;
        // └
        $item[$newNameKey] = str_repeat('&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;', $level) . ($item[$nameKey] ?? '');
        if (isset($item[$key])) {
            $children = $item[$key];
            unset($item[$key]);
            $data[] = $item;
            $data = array_merge($data, tree_to_table($children, $name, $key, $level + 1));
        } else {
            $data[] = $item;
        }
    }
    return $data;
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
 * @param $data
 * @return string
 * @link http://php.net/manual/zh/function.base64-encode.php
 */
function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 Url安全解码
 * @param $data
 * @return bool|string
 * @link http://php.net/manual/zh/function.base64-encode.php
 */
function base64url_decode(string $data): string
{
    if ($remainder = strlen($data) % 4) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
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
 * @param string $url
 * @param string $prefix
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
 * 生成 uuid v4
 * @return string|false
 * @link https://stackoverflow.com/a/15875555/10242420
 */
function uuidv4()
{
    try {
        $data = random_bytes(16);
    } catch (Exception $e) {
        return false;
    }

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * 获取随机字符串
 * @param int $length
 * @param string|null $chars
 * @return string
 */
function get_rand_str(int $length = 8, ?string $chars = null)
{
    $chars || $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
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
 * 是否关联数组
 * @param array $arr
 * @return bool
 * @link https://github.com/laravel/framework/blob/5.7/src/Illuminate/Support/Arr.php#L357
 */
function is_assoc(array $arr)
{
    $keys = array_keys($arr);
    return array_keys($keys) !== $keys;
}

/**
 * 是否关联数组
 * @param array $arr
 * @return bool
 * @link https://stackoverflow.com/a/173479/10242420
 */
function is_assoc2(array $arr)
{
    if ([] === $arr) {
        return false;
    }
    if (true === isset($arr[0])) {
        return false;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/**
 * 查询当前链接 mysql 版本
 * @param string $connect
 * @return string
 */
function query_mysql_version(string $connect = null)
{
    $sql = 'select version() as mysqlver';
    if ($connect) {
        $_version = Db::connect($connect, true)->query($sql);
    } else {
        $_version = Db::query($sql);
    }
    return array_pop($_version)['mysqlver'];
}

/**
 * 查询数据库版本
 * @param string $connect
 * @param bool   $driver
 * @return string
 */
function db_version(?string $connect = null, bool $driver = false): string
{
    /** @var PDO $pdo */
    $connect = ($connect ? Db::connect($connect, true) : Db::connect())->getConnection();
    ((
        function () {
            $this->initConnect();
        }
    )->bindTo($connect, $connect))();
    $pdo = $connect->getPdo();
    $prefix = $driver ? ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . ' ') : '';
    return $prefix . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
}

/**
 * 查询当前链接 mysql 是否存在指定库
 * @param string $database
 * @param string $connect
 * @return bool
 */
function query_mysql_exist_database(string $database, string $connect = null)
{
    /** @noinspection SqlNoDataSourceInspection */
    $sql = "select * from `INFORMATION_SCHEMA`.`SCHEMATA` where `SCHEMA_NAME`='{$database}'";
    if ($connect) {
        $list = Db::connect($connect, true)->query($sql);
    } else {
        $list = Db::query($sql);
    }
    return count($list) > 0;
}

/**
 * 多维数组指定多字段排序
 * 排序：SORT_ASC升序 , SORT_DESC降序
 * 示例：$this->multiaArraySort($arr, 'num', SORT_DESC, 'sort', SORT_ASC)
 * @copyright https://blog.csdn.net/qq_35296546/article/details/78812176
 * @return array
 * @throws Exception
 */
function sortArrByManyField()
{
    $args = func_get_args();
    if (empty($args)) {
        return [];
    }
    $arr = array_shift($args);
    if (!is_array($arr)) {
        throw new Exception("第一个参数不为数组");
    }
    foreach ($args as $key => $field) {
        if (is_string($field)) {
            $temp = [];
            foreach ($arr as $index => $val) {
                $temp[$index] = $val[$field];
            }
            $args[$key] = $temp;
        }
    }
    $args[] = &$arr; //引用值
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

/**
 * 多字节字符串按照字节长度进行截取
 * @param  string $string 字符串
 * @param  int $length 截取长度
 * @param  string $dot 省略符
 * @param  string $charset 编码
 * @return string
 */
function mb_strcut_omit(string $string, int $length, string $dot = '...', ?string $charset = null): string
{
    if (strlen($string) > $length) {
        $charset || $charset = mb_internal_encoding();
        $dotlen = strlen($dot);
        return mb_strcut($string, 0, $length - $dotlen, $charset) . $dot;
    }

    return $string;
}

/**
 * Env获取
 * @param string $key
 * @param        $default
 * @param mixed  ...$argv
 * @return mixed
 * @deprecated
 */
function env_get(string $key, $default, ...$argv)
{
    $key = sprintf($key, ...$argv);
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    return app('env')->get($key, $default);
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

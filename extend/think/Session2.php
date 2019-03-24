<?php
/**
 * Created by PhpStorm.
 * User: Auoor
 * Date: 2019/3/3
 * Time: 20:31
 */

namespace think;

use think\exception\ClassNotFoundException;
use think\facade\Cookie;
use think\facade\Request;

/**
 * Class Session2
 * @package think
 */
class Session2 extends Session
{
    /** @var string 会话ID */
    protected $sessionId = null;

    /** @var bool 是否允许透传SessionID */
    protected $sessionUseTransSid = false;
    /** @var int SessionID长度 */
    protected $sessionSidLength = 36;

    /** @var array Session Cookie 选项 */
    protected $cookieOption = [
        'lifetime' => 0,
        'secure' => false,
        'httponly' => true,
    ];

    /** @var string SessionName */
    protected $sessionName = 'PHPSESSION';

    /**
     * session_id设置
     * @access public
     * @param  string     $id session_id
     * @return void
     */
    public function setId(?string $id)
    {
        $this->sessionId = $id;
    }

    /**
     * 获取session_id
     * @access public
     * @param  bool $regenerate 不存在是否自动生成
     * @return string
     */
    public function getId(bool $regenerate = true)
    {
        if ($regenerate && empty($this->sessionId)) {
            $this->sessionId = get_rand_str($this->sessionSidLength);
        }
        return $this->sessionId;
    }

    /**
     * 获取Session名称
     * @return string
     */
    public function getName()
    {
        return $this->sessionName;
    }

    /**
     * @param array $config
     */
    protected function loadSessionConfig(array $config)
    {
        // 需要载入Session环境设置
        $this->sessionName = $config['name'] ?? ini_get('session.name');
        $this->sessionUseTransSid = boolval($config['use_trans_sid'] ?? ini_get('session.use_trans_sid'));
        if (isset($config['sid_length']) && $config['sid_length'] > 0) {
            $this->sessionSidLength = (int) $config['sid_length'];
        }
        [
            'lifetime' => $cookie_lifetime,
            'secure' => $cookie_secure,
            'httponly' => $cookie_httponly,
        ] = session_get_cookie_params();
        $this->cookieOption['lifetime'] = $config['expire'] ?? $cookie_lifetime;
        $this->cookieOption['secure'] = $config['secure'] ?? $cookie_secure;
        $this->cookieOption['httponly'] = $config['httponly'] ?? $cookie_httponly;

        // 需要立即设置的Session环境
        if (PHP_SESSION_ACTIVE !== session_status()) {
            if (isset($config['expire'])) {
                ini_set('session.gc_maxlifetime', (int) $config['expire']);
            }
            if (isset($config['path'])) {
                ini_set('session.save_path', $config['path']);
            }
            if (isset($config['cache_limiter'])) {
                ini_set('session.cache_limiter', $config['cache_limiter']);
            }
            if (isset($config['cache_expire'])) {
                ini_set('session.cache_expire', $config['cache_expire']);
            }
        }

        // 而外的Session驱动配置
        $this->prefix = $config['prefix'] ?? '';
        $this->lock = $config['use_lock'] ?? false;
    }

    /**
     * 配置
     * @access public
     * @param  array $config
     * @return void
     */
    public function setConfig(array $config = [])
    {
        $this->config = array_merge($this->config, array_change_key_case($config));
        $this->loadSessionConfig($this->config);
    }

    /**
     * 初始化Session
     * @param array $config
     */
    public function init(array $config = [])
    {
        $config = $config ?: $this->config;

        // 防止Session脱离控制，禁止自动初始化
        if (ini_get('session.auto_start') || PHP_SESSION_ACTIVE === session_status()) {
            session_unset();
            session_destroy();
            $this->setId(session_id());
        }

        // 接管SessionCookie
        ini_set('session.use_cookies', 0);

        // 载入Session设置
        $this->loadSessionConfig($config);

        // 选中Session驱动
        if (!empty($config['type'])) {
            // 读取session驱动
            if (false !== strpos($config['type'], '\\')) {
                $class = $config['type'];
            } else {
                $class = '\\think\\session\\driver\\' . ucwords($config['type']);
            }
            // 检查驱动类
            if (!class_exists($class) || !session_set_save_handler(new $class($config))) {
                throw new ClassNotFoundException('error session handler:' . $class, $class);
            }
        }

        // 设置SessionID
        if (empty($this->getId(false))) {
            if (isset($config['id']) && !empty($config['id'])) {
                $this->setId($config['id']);
            } elseif (isset($config['var_session_id'])
                && !empty($config['var_session_id'])
                && $value = Request::request($config['var_session_id'], false)
            ) {
                $this->setId($value);
            } elseif (true === $this->sessionUseTransSid
                && $value = Request::request($this->sessionName, false)
            ) {
                $this->setId($value);
            } elseif (Cookie::has($this->config['name'])) {
                // 没有输入自定义SessionId则尝试从Cookie获取
                $this->setId(Cookie::get($this->config['name']));
            } else {
                $this->getId();
            }
        }

        // 启动Sessopn
        $this->start();
    }

    /**
     * 启动session
     * @access public
     * @return void
     */
    public function start()
    {
        // 设置SessionID
        session_id($this->getId(false));
        // 启动Session
        session_start();
        // 创建Cookie
        $this->createSessionCookie();
        $this->init = true;
    }

    /**
     * 启动Session
     */
    public function boot()
    {
        if ($this->init !== true) {
            $this->init();
        }
    }

    /**
     * 创建SessionCookie
     */
    protected function createSessionCookie()
    {
        $sessionId = $this->getId();
        $sessionName = $this->config['name'];

        if (empty($sessionId)) {
            return;
        }
        if (Cookie::get($sessionName) === $sessionId) {
            return;
        }

        Cookie::set(
            $this->sessionName,
            $sessionId,
            [
                // cookie 保存时间
                'expire'    => $this->cookieOption['lifetime'],
                // cookie 保存路径
                'path'      => '/',
                // cookie 有效域名
                'domain'    => '',
                //  cookie 启用安全传输
                'secure'    => $this->cookieOption['secure'],
                // httponly设置
                'httponly'  => $this->cookieOption['httponly'],
            ]
        );
    }

    public function destroy()
    {
        if (!empty($_SESSION)) {
            $_SESSION = [];
        }

        session_unset();
        session_destroy();

        $this->init       = null;
        $this->lockDriver = null;
    }
}

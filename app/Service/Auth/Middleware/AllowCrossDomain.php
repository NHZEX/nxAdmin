<?php
declare (strict_types = 1);

namespace app\Service\Auth\Middleware;

use Closure;
use think\Config;
use think\Request;
use think\Response;
use function join;

/**
 * 跨域请求支持
 */
class AllowCrossDomain
{
    protected $cookieDomain;

    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods'     => '',
        'Access-Control-Allow-Headers'     => '',
    ];

    public function __construct(Config $config)
    {
        $this->header['Access-Control-Allow-Credentials'] = $config->get('cross.credentials', false)
            ? 'true'
            : 'false';
        $this->header['Access-Control-Allow-Methods'] = join(', ', $config->get('cross.methods', []));
        $this->header['Access-Control-Allow-Headers'] = join(', ', $config->get('cross.headers', []));
        $this->cookieDomain = $config->get('cookie.domain', '');
    }

    /**
     * 允许跨域请求
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param array   $header
     * @return Response
     */
    public function handle($request, Closure $next, ?array $header = [])
    {
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;

        if (!isset($header['Access-Control-Allow-Origin'])) {
            $origin = $request->header('origin');

            if ($origin && ('' == $this->cookieDomain || strpos($origin, $this->cookieDomain))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }

        if ($request->method(true) == 'OPTIONS') {
            return Response::create()->code(204)->header($header);
        }

        return $next($request)->header($header);
    }
}

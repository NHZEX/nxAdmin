<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/22
 * Time: 18:19
 */

namespace captcha;

use app\exception\BusinessResult;
use app\exception\JsonException;
use basis\IP;
use facade\Redis;
use think\facade\App;

/**
 * Class Captcha
 * @package app\common\captcha
 *
 * @property string $seKey
 * @property string $codeSet
 * @property int $expire
 * @property bool $useImgBg
 * @property int $fontSize
 * @property bool $useCurve
 * @property bool $useNoise
 * @property int $imageH
 * @property int $imageW
 * @property int $length
 * @property string $fontttf
 * @property int[] $bg
 * @property string $reset
 */
class Captcha
{
    private $message = null;

    protected $config = [
        'seKey' => 'ThinkPHP.CN',
        // 验证码加密密钥
        'codeSet' => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
        // 验证码字符集合
        'expire' => 1800,
        // 验证码过期时间（s）
        'useImgBg' => false,
        // 使用背景图片
        'fontSize' => 25,
        // 验证码字体大小(px)
        'useCurve' => true,
        // 是否画混淆曲线
        'useNoise' => true,
        // 是否添加杂点
        'imageH' => 0,
        // 验证码图片高度
        'imageW' => 0,
        // 验证码图片宽度
        'length' => 5,
        // 验证码位数
        'fontttf' => '',
        // 验证码字体，不设置随机获取
        'bg' => [243, 251, 254],
        // 背景颜色
        'reset' => true,
        // 验证成功后是否重置
    ];

    private $im = null; // 验证码图片实例
    private $color = null;
    private $code;
    private $codeContent;
    private $background_path = '';
    private $tff_path = '';

    /**
     * 架构方法 设置参数
     * @access public
     * @param  array $config 配置参数
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->background_path = App::getRootPath() . 'extend/captcha/assets/bgs/';
        $this->tff_path = App::getRootPath() . 'extend/captcha/assets/';
    }

    /**
     * 使用 $this->name 获取配置
     * @access public
     * @param  string $name 配置名称
     * @return mixed    配置值
     */
    public function __get($name)
    {
        return $this->config[$name];
    }

    /**
     * 设置验证码配置
     * @access public
     * @param  string $name 配置名称
     * @param  string $value 配置值
     * @return void
     */
    public function __set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 检查配置
     * @access public
     * @param  string $name 配置名称
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * 获取消息
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     *
     *      高中的数学公式咋都忘了涅，写出来
     *        正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     */
    private function writeCurve()
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $px = $py = 0;

        // 曲线前部分
        $A = mt_rand(1, $this->imageH / 2); // 振幅
        $b = mt_rand(-$this->imageH / 4, $this->imageH / 4); // Y轴方向偏移量
        $f = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
        $T = mt_rand($this->imageH, $this->imageW * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand($this->imageW / 2, $this->imageW * 0.8); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel($this->im, $px + $i, $py + $i, $this->color); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    $i--;
                }
            }
        }

        // 曲线后部分
        $A   = mt_rand(1, $this->imageH / 2); // 振幅
        $f   = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
        $T   = mt_rand($this->imageH, $this->imageW * 2); // 周期
        $w   = (2 * M_PI) / $T;
        $b   = $py - $A * sin($w * $px + $f) - $this->imageH / 2;
        $px1 = $px2;
        $px2 = $this->imageW;

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel($this->im, $px + $i, $py + $i, $this->color);
                    $i--;
                }
            }
        }
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    private function writeNoise()
    {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for ($i = 0; $i < 10; $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate($this->im, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; $j++) {
                // 绘杂点
                imagestring(
                    $this->im,
                    5,
                    mt_rand(-10, $this->imageW),
                    mt_rand(-10, $this->imageH),
                    $codeSet[mt_rand(0, 29)],
                    $noiseColor
                );
            }
        }
    }

    /**
     * 绘制背景图片
     * 注：如果验证码输出图片比较大，将占用比较多的系统资源
     */
    private function background()
    {
        $path = __DIR__ . '/../assets/bgs/';
        $dir  = dir($path);

        $bgs = [];
        while (false !== ($file = $dir->read())) {
            if ('.' != $file[0] && substr($file, -4) == '.jpg') {
                $bgs[] = $path . $file;
            }
        }
        $dir->close();

        $gb = $bgs[array_rand($bgs)];

        list($width, $height) = @getimagesize($gb);
        // Resample
        $bgImage = @imagecreatefromjpeg($gb);
        @imagecopyresampled($this->im, $bgImage, 0, 0, 0, 0, $this->imageW, $this->imageH, $width, $height);
        @imagedestroy($bgImage);
    }

    /**
     * 输出验证码并把验证码的
     * @access public
     * @param string $code 要生成验证码的标识
     * @return \think\Response
     */
    public function entry(&$code = '')
    {
        // 图片宽(px)
        $this->imageW || $this->imageW = $this->length * $this->fontSize * 1.5 + $this->length * $this->fontSize / 2;
        // 图片高(px)
        $this->imageH || $this->imageH = $this->fontSize * 2.5;
        // 建立一幅 $this->imageW x $this->imageH 的图像
        $this->im = imagecreate($this->imageW, $this->imageH);
        // 设置背景
        imagecolorallocate($this->im, $this->bg[0], $this->bg[1], $this->bg[2]);

        // 验证码字体随机颜色
        $this->color = imagecolorallocate($this->im, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
        // 验证码使用随机字体
        $ttfPath = $this->tff_path . 'ttfs/';

        if (empty($this->fontttf)) {
            $dir = dir($ttfPath);
            $ttfs = [];
            while (false !== ($file = $dir->read())) {
                if ('.' != $file[0] && substr($file, -4) == '.ttf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();
            $this->fontttf = $ttfs[array_rand($ttfs)];
        }
        $this->fontttf = $ttfPath . $this->fontttf;

        if ($this->useImgBg) {
            $this->background();
        }

        if ($this->useNoise) {
            // 绘杂点
            $this->writeNoise();
        }
        if ($this->useCurve) {
            // 绘干扰线
            $this->writeCurve();
        }

        // 绘验证码
        $code = []; // 验证码
        $codeNX = 0; // 验证码第N个字符的左边距
        for ($i = 0; $i < $this->length; $i++) {
            $code[$i] = $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)];
            $codeNX += mt_rand($this->fontSize * 1.2, $this->fontSize * 1.6);
            imagettftext(
                $this->im,
                $this->fontSize,
                mt_rand(-40, 40),
                $codeNX,
                $this->fontSize * 1.6,
                $this->color,
                $this->fontttf,
                $code[$i]
            );
        }
        $code = join('', $code);

        // 保存验证码
        $this->code = $this->codeHash($code);

        ob_start();
        // 输出图像
        imagepng($this->im);
        $this->codeContent = ob_get_clean();
        imagedestroy($this->im);

        return $this->codeContent;
    }

    private function codeHash($code)
    {
        $code = strtoupper($code);

        $code = hash('md5', $code);
        $key = hash('md5', $this->seKey);
        $result = hash_hmac('sha1', $code, $key);

        return $result;
    }

    public function send()
    {
        $head = [
            'Content-Length' => strlen($this->codeContent),
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ];

        return response(
            $this->codeContent,
            200,
            $head
        )->contentType('image/png');
    }

    /**
     * 验证验证码是否正确
     * @access public
     * @param string $code 用户验证码
     * @param string $hashCode 验证字符串
     * @return bool 用户验证码是否正确
     */
    public function check($code, $hashCode)
    {
        $result = $this->codeHash($code) === $hashCode;

        return $result;
    }

    /**
     * 获取生成的验证码
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * 保存验证码到 redis
     * @param string $ctoken
     * @throws JsonException
     */
    public function saveToRedis(string $ctoken)
    {
        $ua = request()->header('User-Agent');
        $pack = [
            'hash_code' => $this->getCode(),
            'access_ip' => IP::getIp(),
            'access_ip_2' => IP::getIp(true),
            'expire_time' => time() + $this->expire,
            'ua_sign' => md5($ua),
        ];
        Redis::getSelf()->set("captcha:ctoken_{$ctoken}", json_encode_throw_on_error($pack), $this->expire);
    }

    /**
     * 验证验证码是否正确
     * @access public
     * @param $ctoken
     * @param string $code 用户验证码
     * @return bool 用户验证码是否正确
     */
    public function checkToRedis(string $ctoken, string $code)
    {
        $redis = Redis::getSelf();
        $captcha_key = "captcha:ctoken_{$ctoken}";
        try {
            $pack = $redis->get($captcha_key);
            if (!$pack) {
                throw new BusinessResult('验证码失效.');
            }
            $pack = json_decode_throw_on_error($pack);

            if (!isset($pack['expire_time']) || time() > $pack['expire_time']) {
                throw new BusinessResult('验证码失效..');
            }
            if (!isset($pack['access_ip']) || IP::getIp() !== $pack['access_ip']) {
                throw new BusinessResult('验证码无效.');
            }
            $ua = request()->header('User-Agent');
            if (!isset($pack['ua_sign']) || md5($ua) !== $pack['ua_sign']) {
                throw new BusinessResult('验证码无效..');
            }
            if (!isset($pack['hash_code']) || !$this->check($code, $pack['hash_code'])) {
                throw new BusinessResult('验证码错误.');
            }
        } catch (BusinessResult $result) {
            $redis->delete($captcha_key);
            $this->message = $result->getMessage();

            return false;
        } catch (JsonException $exception) {
            $this->message = "解码失败: {$exception->getMessage()}";

            return false;
        }
        $redis->delete($captcha_key);

        return true;
    }
}

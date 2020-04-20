<?php

namespace app\Command;

use app\Exception\BusinessResult;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Certificate extends Command
{
    const CA_CHECKSUM_URL = 'https://curl.haxx.se/ca/cacert.pem.sha256';

    public function configure()
    {
        $this
            ->setName('cert:update')
            ->setDescription('更新CA文件')
            ->addOption(
                'force',
                'F',
                Option::VALUE_NONE,
                '强制覆盖'
            );
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int|void|null
     * @throws BusinessResult
     */
    public function execute(Input $input, Output $output)
    {
        $input->getOption('force');

        if (false === file_exists(CA_ROOT_PATH)) {
            $output->error('缺失初始证书文件，请确保安全的情况下前往（https://curl.haxx.se/docs/caextract.html）下载');
            $output->error('证书存放位置：.\\runtime\\cacert.pem');
            return;
        }

        $sha256 = $this->updateCertificateSha256();
        $this->checkAndUpdateCertificate($sha256);
    }

    /**
     * @return bool|string
     * @throws BusinessResult
     */
    protected function updateCertificateSha256()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::CA_CHECKSUM_URL);
        // 重定向相关选项
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        // 以变量输出返回值
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 输出HTTP协议头
        curl_setopt($ch, CURLOPT_HEADER, false);
        // 不传输内容
        curl_setopt($ch, CURLOPT_NOBODY, false);
        // 尝试获取远程文档修改时间
        curl_setopt($ch, CURLOPT_FILETIME, true);
        // 证书验证相关
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, CA_ROOT_PATH);
        // 执行获取结果
        $result_cacert_sha256 = curl_exec($ch);
        // 获取请求错误
        if (0 !== curl_errno($ch)) {
            throw new BusinessResult('请求失败：' . curl_error($ch));
        }
        $remote_file_time = curl_getinfo($ch, CURLINFO_FILETIME);
        // 写出校验和文件
        file_put_contents(CA_ROOT_CHECKSUM_PATH, $result_cacert_sha256);
        // 同步文件时间
        $remote_file_time > 0 && touch(CA_ROOT_CHECKSUM_PATH, $remote_file_time);
        // 提取 sha256 部分
        $result_cacert_sha256 = substr($result_cacert_sha256, 0, 64);
        // 关闭连接
        curl_close($ch);

        $this->output->info('获取CA_SHA文件');
        $this->output->info('  - 最后更新时间：' . date('Y-m-d H:i:s', $remote_file_time));
        $this->output->info('  - 最新CA检验值：' . $result_cacert_sha256);

        return $result_cacert_sha256;
    }

    /**
     * 检查并更新CA根证书
     * @param string $cacert_sha256
     * @return bool
     */
    public function checkAndUpdateCertificate(string $cacert_sha256)
    {
        $CA_URL = 'https://curl.haxx.se/ca/cacert.pem';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $CA_URL);
        // 重定向相关选项
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        // 以变量输出返回值
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 输出HTTP协议头
        curl_setopt($ch, CURLOPT_HEADER, true);
        // 不传输内容
        curl_setopt($ch, CURLOPT_NOBODY, true);
        // 尝试获取远程文档修改时间
        curl_setopt($ch, CURLOPT_FILETIME, true);
        // 证书验证相关
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, CA_ROOT_PATH);
        // 执行获取结果
        curl_exec($ch);
        $remote_file_time = curl_getinfo($ch, CURLINFO_FILETIME);
        curl_close($ch);

        $local_cacert_sha256 = hash_file('sha256', CA_ROOT_PATH);

        $this->output->info('获取CA_ROOT文件');
        $this->output->info('  - 最后更新时间：' . date('Y-m-d H:i:s', $remote_file_time));
        $this->output->info('  - 本地文件时间：' . date('Y-m-d H:i:s', filemtime(CA_ROOT_PATH)));
        $this->output->info('  - 本地CA检验值：' . $local_cacert_sha256);

        if (filemtime(CA_ROOT_PATH) !== $remote_file_time
            && $local_cacert_sha256 !== $cacert_sha256
        ) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $CA_URL);
            // 重定向相关选项
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            // 以变量输出返回值
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // 输出HTTP协议头
            curl_setopt($ch, CURLOPT_HEADER, false);
            // 不传输内容
            curl_setopt($ch, CURLOPT_NOBODY, false);
            // 尝试获取远程文档修改时间
            curl_setopt($ch, CURLOPT_FILETIME, true);
            // 证书验证相关
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, CA_ROOT_PATH);
            // 执行获取结果
            $ca_file = curl_exec($ch);
            $remote_file_time = curl_getinfo($ch, CURLINFO_FILETIME);
            curl_close($ch);
            // 写出证书文件
            file_put_contents(CA_ROOT_PATH, $ca_file);
            // 同步文件时间
            $remote_file_time > 0 && touch(CA_ROOT_PATH, $remote_file_time);

            $this->output->info('CA更新成功');
        } else {
            $this->output->info('CA无需更新');
            return true;
        }
        return true;
    }
}

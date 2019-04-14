<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/23
 * Time: 11:59
 */
declare(strict_types=1);

namespace app\command;

use basis\Util;
use Exception;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use think\App;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use ZipArchive;

class DeployDiff extends Command
{
    /** @var App */
    protected $app;

    public function configure()
    {
        $this
            ->setName('dep:diff')
            ->addOption('mode', 'm', Option::VALUE_OPTIONAL, '模式选择 [git]', 'git')
            ->setDescription('执行系统更新 [TEST]');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function execute(Input $input, Output $output): int
    {
        $this->app = App::getInstance();
        $output->info('whoami: ' . Util::whoami());
        $mode = $input->getOption('mode');

        switch ($mode) {
            case 'git':
                $git_dir = $this->app->getRootPath() . '.git';
                if (!is_dir($git_dir)) {
                    $this->output->error("{$git_dir} does not exist");
                    return 1;
                }
                $currBranchesName = trim(shell_exec('git rev-parse --abbrev-ref --symbolic-full-name HEAD') ?: '');
                $currBranchesUpstream = trim(shell_exec('git rev-parse --abbrev-ref --symbolic-full-name @{u}') ?: '');
                $this->output->info("curr branches name: {$currBranchesName}");
                $this->output->info("curr branches upstream: {$currBranchesUpstream}");

                $result = $this->gitDiffToArchive(
                    'b334cff9bdc2073dd702afbc1bef187cda52db0b',
                    'de45fe47e0a7b2de2fd91ceadb70a4524d651ff0',
                    $this->app->getRootPath() . 'update.pack.zip'
                );
                break;
            default:
                $this->output->error("Invalid mode: {$mode}");
                $result = 1;
        }
        return $result;
    }

    /**
     * @param string $commit1
     * @param string $commit2
     * @param string $archivePath
     * @return int
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function gitDiffToArchive(string $commit1, string $commit2, string $archivePath): int
    {
        $command = ['git', 'diff', '--name-status', $commit1, $commit2];

        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            $this->output->warning('获取差异失败: diff执行异常');
            return $process->getExitCode();
        }

        $metadata = [
            'add' => [],
            'modify' => [],
            'delete' => [],
        ];

        /**
         * A: 添加
         * C: 复制
         * D: 删除
         * M: 修改内容或模式
         * R: 重命名
         * T: 更改文件类型
         * U: 文件未合并（您必须先完成合并才能提交）
         * X: “未知”更改类型（最有可能是错误，请报告）
         */
        foreach (explode(PHP_EOL, $process->getOutput()) as $line) {
            $this->output->writeln($line);

            if (empty($line)
                || strpos($line, 'warning: CRLF will be replaced by LF in') === 0
                || strpos($line, 'The file will have its original line endings in your working directory.') === 0
            ) {
                continue;
            }

            $status = $line[0];

            switch ($status) {
                case 'A':
                case 'C':   // TODO 重命名或复制分数（表示百分比的来源和目标之间的相似性移动或复制） 需要改进C的兼容性
                    $metadata['add'][] = [
                        'rs' => $status,
                        'file_path' => trim(substr($line, 1)),
                    ];
                    break;
                case 'M':
                case 'T':
                    $metadata['modify'][] = [
                        'rs' => $status,
                        'file_path' => trim(substr($line, 1)),
                    ];
                    break;
                case 'D':
                    $metadata['delete'][] = [
                        'rs' => $status,
                        'file_path' => trim(substr($line, 1)),
                    ];
                    break;
                case 'R':
                    [, $oldFile, $newFile] = preg_split('/\s+/', $line);
                    $metadata['add'][] = [
                        'rs' => 'R-A',
                        'file_path' => trim($newFile),
                    ];
                    $metadata['delete'][] = [
                        'rs' => 'R-D',
                        'file_path' => trim($oldFile),
                    ];
                    break;
                default:
                    throw new Exception("Unknown git-diff status ({$line}).");
            }
        }

        $zip = new ZipArchive();
        $zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($metadata['add'] as &$add) {
            $this->output->writeln("read {$commit2}:{$add['file_path']}");
            $content = $this->gitShow($commit2, $add['file_path']);
            $add['file_hash'] = base64url_encode(hash('sha256', $content, true));
            $zip->addFromString($add['file_hash'], $content);
        }

        foreach ($metadata['modify'] as &$add) {
            $this->output->writeln("read {$commit2}:{$add['file_path']}");
            $content = $this->gitShow($commit2, $add['file_path']);
            $add['file_hash'] = base64url_encode(hash('sha256', $content, true));
            $zip->addFromString($add['file_hash'], $content);
        }

        $zip->addFromString('.metadata.info', VarExporter::export($metadata));
        $zip->setArchiveComment('Creation time: ' . date(DATE_ATOM));
        $zip->close();

        return 0;
    }

    /**
     * @param string $commit
     * @param string $file
     * @return string
     */
    private function gitShow(string $commit, string $file)
    {
        $process = new Process(['git', 'show', "{$commit}:{$file}"]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }
}

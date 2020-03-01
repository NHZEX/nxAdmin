<?php
declare(strict_types=1);

namespace app\Service\Auth;

use RuntimeException;
use SplFileObject;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use function array_pop;
use function count;
use function explode;
use function implode;
use function ksort;

/**
 * Trait InteractsWithSyncModel
 * @package app\Service\Auth
 * @property Permission $permission
 */
trait InteractsWithSyncModel
{
    private $increase = 1;

    /**
     * 刷新权限到数据库
     */
    public function refresh()
    {
        $this->scanAuthAnnotation();

        $original = [];

        $output = [
            'features'   => $this->getNodes(),
            'permission' => $this->fillPermission($this->getPermissions(), $original),
            'permission2features' => [],
            'features2permission' => [],
        ];

        $permission = Permission::getInstance();
        if ($permission->hasStorage()) {
            foreach ($output['permission'] as $key => $item) {
                if ($info = $permission->queryPermission($key)) {
                    $item['sort'] = $info['sort'];
                    $item['desc'] = $info['desc'];
                    $output['permission'][$key] = $item;
                }
            }
        }

        $permission2features = &$output['permission2features'];
        foreach ($output['permission'] as $permission => $data) {
            $permission2features[$permission] = array_merge(
                $permission2features[$permission] ?? [],
                $data['allow'] ?? []
            );
        }

        $features2permission = &$output['features2permission'];
        foreach ($output['permission2features'] as $permission => $features) {
            foreach ($features as $feature) {
                if (isset($features2permission[$feature])) {
                    throw new RuntimeException('features mapping permission only one');
                }
                $features2permission[$feature] = $permission;
            }
        }

        $this->export($output);
    }

    public function export(array $data)
    {
        $filename = app_path() . 'auth_storage.php';

        if (is_file($filename) && is_readable($filename)) {
            $sf = new SplFileObject($filename, 'r');
            $sf->seek(2);
            [, $lastHash] = explode(':', $sf->current() ?: ':');
            $lastHash = trim($lastHash);
            $contents = $sf->fread($sf->getSize() - $sf->ftell());
            if ($lastHash !== md5($contents)) {
                unset($lastHash);
            }
        }

        try {
            $nodes_data = VarExporter::export($data);
        } catch (ExceptionInterface $e) {
            $nodes_data = '[]';
        }

        $contents = "return {$nodes_data};\n";
        $hash = md5($contents);

        if (isset($lastHash) && $lastHash === $hash) {
            return true;
        }

        $date = date('c');
        $info = "// update date: {$date}\n// hash: {$hash}";

        $tempname = stream_get_meta_data($tf = tmpfile())['uri'];
        fwrite($tf, "<?php\n{$info}\n{$contents}");
        copy($tempname, $filename);

        return true;
    }

    /**
     * @param array $data
     * @param array $original
     * @return array
     */
    protected function fillPermission(array $data, array $original): array
    {
        $result = [];
        $original = $original['permission'] ?? [];
        foreach ($data as $permission => $control) {
            // 填充父节点
            $pid = $this->fillParent($result, $original, $permission);
            // 生成插入数据
            if (isset($original[$permission])) {
                $sort = $original[$permission]['sort'];
                $desc = $original[$permission]['desc'];
            } else {
                $sort = 0;
                $desc = '';
            }
            $result[$permission] = [
                'pid' => $pid,
                'name' => $permission,
                'sort' => $sort,
                'desc' => $desc,
                'allow' => array_values($control),
            ];
        }

        ksort($result);
        return $result;
    }


    /**
     * 填充父节点
     * @param array  $data
     * @param array  $original
     * @param string $permission
     * @return string
     */
    protected function fillParent(array &$data, array $original, string $permission)
    {
        $delimiter = '.';
        $parents = explode($delimiter, $permission) ?: [];
        if (1 === count($parents)) {
            return self::ROOT_NODE;
        }
        array_pop($parents);
        $result = implode($delimiter, $parents);

        while (count($parents)) {
            $curr = implode($delimiter, $parents);
            array_pop($parents);
            $parent = implode($delimiter, $parents) ?: self::ROOT_NODE;

            if (isset($original[$curr])) {
                $sort = $original[$curr]['sort'];
                $desc = $original[$curr]['desc'];
            } else {
                $sort = 0;
                $desc = '';
            }
            $data[$curr] = [
                'pid' => $parent,
                'name' => $curr,
                'sort' => $sort,
                'desc' => $desc,
                'allow' => null,
            ];
        }

        return $result;
    }
}

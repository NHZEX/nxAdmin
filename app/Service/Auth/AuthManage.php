<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Service\Auth\Model\Permission;
use ReflectionException;
use RuntimeException;
use function array_pop;
use function count;
use function explode;
use function implode;
use function ksort;

class AuthManage
{
    const PARENT_NODE = '__NULL__';
    const ROOT_NODE = '__ROOT__';

    protected $scan;


    public function __construct(AuthScan $authScan)
    {
        $this->scan = $authScan;
    }

    /**
     *
     * @throws ReflectionException
     */
    public function refresh()
    {
        $permissions = $this->scan->scanAuthAnnotation();

        Permission::transaction(function () use ($permissions) {
            $original = Permission::select();
            Permission::where('id', '>', 0)->delete();

            $original = $original->column('sort,name,control,pid,desc', 'name');

            foreach ($permissions as $permission => $control) {
                $pid = $this->fillParent($original, $permission);
                if (isset($original[$permission]) && $original[$permission]['control'] !== self::PARENT_NODE) {
                    $original[$permission]['control'] = implode(',', $control);
                    $original[$permission]['pid'] = $pid;
                } else {
                    $original[$permission] = [
                        'sort' => 0,
                        'pid' => $pid,
                        'name' => $permission,
                        'control' => implode(',', $control),
                        'desc' => '',
                    ];
                }
            }

            ksort($original);
            $i = 1;
            foreach ($original as &$item) {
                $item['id'] = $i++;
            }

            (new Permission())->saveAll($original, false);
            // Permission::insertAll($original); TODO 数据会错乱
        });
    }

    /**
     * 填充父节点
     * @param array $data
     * @param       $permission
     * @return string
     */
    protected function fillParent(array &$data, $permission)
    {
        $parents = explode('.', $permission) ?: [];
        array_pop($parents);
        $result = implode('.', $parents);

        while (count($parents)) {
            $curr = implode('.', $parents);
            array_pop($parents);
            $parent = implode('.', $parents) ?: self::ROOT_NODE;

            if (isset($data[$curr])) {
                if ($data[$curr]['control'] !== self::PARENT_NODE) {
                    throw new RuntimeException('父节点不允许分配权限');
                }
            } else {
                $data[$curr] = [
                    'sort' => 0,
                    'name' => $curr,
                    'control' => self::PARENT_NODE,
                    'desc' => '',
                ];
            }
            $data[$curr]['pid'] = $parent;
        }

        return $result;
    }

}

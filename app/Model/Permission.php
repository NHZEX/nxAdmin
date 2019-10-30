<?php
/**
 * Created by PhpStorm.
 * Date: 2019/1/16
 * Time: 16:14
 */

namespace app\Model;

use app\Logic\Permission as PermissionLogic;
use app\Logic\ScanningNode;
use think\Model;
use Throwable;

/**
 * Class Permission
 *
 * @property int        $id          节点id
 * @property int        $pid         父节点id
 * @property int        $genre       节点类型 =1.控制器 =2.方法
 * @property string     $nkey        节点命名key
 * @property string     $hash        nkey的hash
 * @property string     $lkey        节点逻辑key
 * @property int        $level       节点层级
 * @property string     $controller  节点控制器
 * @property string     $action      节点方法
 * @property int        $sort        节点排序 默认255
 * @property string     $class_name  节点类名
 * @property string     $alias_name  节点别名
 * @property string     $description 节点描述
 * @property int        $flags       选项标识
 * @property-read mixed $login_flag
 * @property-read mixed $permission_flag
 * @property-read mixed $menu_flag
 */
class Permission extends Base
{
    protected $table = 'permission';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;

    const GENRE_CONTROLLER = 1;
    const GENRE_ACTION = 2;

    const LEVEL_FIRST = 1;
    const LEVEL_SECOND = 2;

    //标识
    const FLAG_LOGIN = 1; //登录后启用
    const FLAG_PERMISSION = 2; //权限启用
    const FLAG_MENU = 4; //菜单启用
    const FLAG_DICT = [
        'login' => self::FLAG_LOGIN,
        'permission' => self::FLAG_PERMISSION,
        'menu' => self::FLAG_MENU,
    ];

    public static $increase_id = 0;
    public static $info_cache = [];

    public static function onBeforeInsert(Model $permission)
    {
        if (empty($permission->action)) {
            $permission->setAttr('action', '');
        }
        if (empty($permission->alias_name)) {
            $permission->setAttr('alias_name', '');
        }
        if (empty($permission->description)) {
            $permission->setAttr('description', '');
        }
    }

    public function queryFlags($query, $mapName = 'queryMap')
    {
        if (!isset($query[$mapName])) {
            return $this->where([]);
        }

        $map = $query[$mapName];
        if (isset($map['flags'])) {
            $flagMap = $map['flags'];
            $flag = self::FLAG_DICT[$flagMap];

            return $this->whereRaw("flags & ${flag} > 0");
        }
        return $this;
    }

    /**
     * 登录标识获取器
     * @return mixed
     */
    public function getLoginFlagAttr()
    {
        return $this->getData('flags') & self::FLAG_LOGIN;
    }

    /**
     * 权限标识获取器
     * @return mixed
     */
    public function getPermissionFlagAttr()
    {
        return $this->getData('flags') & self::FLAG_PERMISSION;
    }

    /**
     * 菜单标识获取器
     * @return mixed
     */
    public function getMenuFlagAttr()
    {
        return $this->getData('flags') & self::FLAG_MENU;
    }

    public static function getHashByFlag($flag)
    {
        return self::whereRaw("flags & ${flag} > 0")->column('hash');
    }

    /**
     * 生成节点/更新节点
     * @return bool
     * @throws Throwable
     */
    public static function generateNodes()
    {
        $that = new static();
        try {
            $that->startTrans();
            $that->lock(true)->max('id');

            self::$info_cache = $that->field(['nkey', 'alias_name', 'description', 'flags'])
                ->column('alias_name, description, flags', 'nkey');

            //清空表格
            $that->where('id', '>', '0')->delete();

            //获取自增id
            self::$increase_id = $that->max($that->getPk());

            foreach (ScanningNode::parseMca() as $node) {
                [
                    'class' => $class_name,
                    'controller' => $controllers,
                    'actions' => $actions,
                    'actions_info' => $actions_info,
                    'controller_info' => $controller_info,
                ] = $node;

                //命名处理
                $controllers_class = strtolower(array_pop($controllers));
                $controllers = array_map('strtolower', $controllers);
                $controllers[] = $controllers_class;
                $actions_lower = array_map('strtolower', $actions);

                //写入controller类
                $aciontPid = self::writeControllerId(
                    $class_name,
                    $controller_info
                );

                //写入controller类下的方法
                foreach ($actions_lower as $key => $action) {
                    // 尝试获取注释
                    $action_desc = $actions_info[$actions[$key]] ?? null;

                    self::writeActionId(
                        $aciontPid,
                        $action,
                        $class_name,
                        $action_desc
                    );
                }
            }
            $that->commit();
        } catch (Throwable $e) {
            $that->rollback();
            throw $e;
        }
        return true;
    }

    /**
     * @param string     $class_name
     * @param array|null $controller_info
     * @return int
     */
    private static function writeControllerId(
        string $class_name,
        ?array $controller_info
    ): int {
        $that = new self();
        $that->id = ++self::$increase_id;
        $that->pid = 0;

        $that->genre = self::GENRE_CONTROLLER;
        $that->action = '';
        $that->class_name = $class_name;
        if (is_array($controller_info)) {
            $that->alias_name = $controller_info['name'] ?? null;
            $that->description = $controller_info['desc'] ?? null;
        }

        $node = PermissionLogic::computeNode($class_name, '');
        $that->nkey = $node->nkey;
        $that->hash = $node->hash;
        $that->lkey = $that->id;
        $that->level = self::LEVEL_FIRST;

        if (isset(self::$info_cache[$that->nkey])) {
            $that->alias_name = self::$info_cache[$that->nkey]['alias_name'];
            $that->description = self::$info_cache[$that->nkey]['description'];
            $that->flags = self::$info_cache[$that->nkey]['flags'];
        }

        $that->save();

        return $that->id;
    }

    /**
     * @param string $pid
     * @param string $action
     * @param string $class_name
     * @param array  $action_info
     * @return int
     */
    private static function writeActionId(
        string $pid,
        string $action,
        string $class_name,
        ?array $action_info
    ): int {
        $that = new self();
        $that->id = ++self::$increase_id;
        $that->pid = $pid;

        $that->genre = self::GENRE_ACTION;
        $that->action = $action;
        $that->class_name = $class_name;
        if (is_array($action_info)) {
            $that->alias_name = $action_info['name'] ?? null;
            $that->description = $action_info['desc'] ?? null;
        }

        $node = PermissionLogic::computeNode($class_name, $action);
        $that->nkey = $node->nkey;
        $that->hash = $node->hash;
        $that->lkey = "{$that->pid}-{$that->id}";
        $that->level = self::LEVEL_SECOND;

        if (isset(self::$info_cache[$that->nkey])) {
            $that->alias_name = self::$info_cache[$that->nkey]['alias_name'];
            $that->description = self::$info_cache[$that->nkey]['description'];
            $that->flags = self::$info_cache[$that->nkey]['flags'];
        }

        $that->save();
        return $that->id;
    }
}

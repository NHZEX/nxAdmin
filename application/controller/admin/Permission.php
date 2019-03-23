<?php
/**
 * Created by PhpStorm.
 * User: Johnson
 * Date: 2019/1/17
 * Time: 14:54
 */

namespace app\controller\admin;

use app\logic\Permission as PermissionLogic;
use app\model\Permission as PermissionModel;
use think\Db;
use think\facade\App;
use think\model\Collection;

class Permission extends Base
{
    /**
     * 首页
     * User: Johnson
     * @return mixed
     */
    public function node()
    {
        $this->assign([
            'url_table' => url('nodeList'),
            'url_update' => url('update'),
            'url_generate' => url('generateNodes'),
            'url_save_flags' => url('saveFlags'),
            'url_export' => url('exportNodes'),
        ]);
        return $this->fetch();
    }

    /**
     * 获取节点列表
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function nodeList()
    {
        /** @var Collection|PermissionModel[] $result */
        $query = $this->request->param();

        $result = (new PermissionModel)->queryFlags($query)->select();

        if (!$result->isEmpty()) {
            $result->append(['login_flag', 'permission_flag', 'menu_flag']);
        }
        return self::showTable([
            'data' => $result,
            'count' => $result->count(),
        ]);
    }

    /**
     * 修改别名、注释
     * User: Johnson
     * @param null $id
     * @return \think\Response
     */
    public function update($id = null)
    {
        $data = $this->request->param(null, null, 'htmlspecialchars');
        $result = PermissionModel::update($data, ['id' => $id], ['alias_name', 'description']);
        return self::showData(CODE_SUCCEED, $result);
    }

    /**
     * 重新生成节点
     * User: Johnson
     * @throws \Throwable
     */
    public function generateNodes()
    {
        if (!App::isDebug()) {
            return self::showMsg(CODE_COM_UNABLE_PROCESS, '调试模式未开启不能修改权限节点');
        }
        //重新生成节点
        PermissionModel::generateNodes();
        return self::showMsg(CODE_SUCCEED, '操作成功');
    }

    /**
     * @return \think\Response
     */
    public function exportNodes()
    {
        if (!App::isDebug()) {
            return self::showMsg(CODE_COM_UNABLE_PROCESS, '调试模式未开启不能修改权限节点');
        }
        PermissionLogic::exportNodes();
        return self::showMsg(CODE_SUCCEED);
    }

    /**
     * 批量修改标识操作
     * User: Johnson
     * @return \think\Response
     * @throws \Throwable
     */
    public function saveFlags()
    {
        if (!App::isDebug()) {
            return self::showMsg(CODE_COM_UNABLE_PROCESS, '调试模式未开启不能修改权限节点');
        }
        $data = $this->request->param('data');
        try {
            Db::startTrans();
            foreach ($data as $index => $item) {
                $flag = 0;
                if ($item['login']) {
                    $flag = $flag | PermissionModel::FLAG_LOGIN;
                }
                if ($item['permission']) {
                    $flag = $flag | PermissionModel::FLAG_PERMISSION;
                }
                if ($item['menu']) {
                    $flag = $flag | PermissionModel::FLAG_MENU;
                }
                PermissionModel::wherePk($item['id'])->update(['flags' => $flag,]);
            }
            // 重新设置节点缓存
            PermissionLogic::refreshCache();
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        return self::showMsg(CODE_SUCCEED);
    }
}

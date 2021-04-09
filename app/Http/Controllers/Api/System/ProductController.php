<?php


namespace App\Http\Controllers\Api\System;


use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    public function create(Request $request){
        $form = $request->get('form');
        if (!$form['username']) {
            return $this->paramsError('用户名不能为空');
        }
        if (!$form['password']) {
            return $this->paramsError('密码不能为空');
        }
        if ($form['password'] != $form['repeat_password']) {
            return $this->paramsError('两次密码不一致');
        }


        $form['create_time'] = time();
        $form['update_time'] = time();

        $ret = SysProject::create($form);
        if (!$ret) {
            return $this->error('添加失败');
        }
        return $this->success();
    }

    public function delete(Request $request){
        $id = $request->get('id');
        if (!$id) {
            return $this->paramsError('参数有误');
        }
        $model = SysProject::find($id);
        if (!$model) {
            return $this->paramsError('信息不存在');
        }
        //删除状态
        $model->status = -1;
        $model->update_time = time();
        $ret = $model->save();
        if (!$ret) {
            return $this->error('操作失败');
        }
        return $this->success();
    }

    public function edit(Request $request){
        $form = $request->get('form');
        if (!($id = $form['id'])) {
            return $this->paramsError('参数有误');
        }
        $model = SysProject::find($id);
        if (!$model) {
            return $this->paramsError('用户不存在');
        }
        //初始化数据
        $model->mobile = $form['mobile'];
        $model->realname = $form['realname'];
        $model->role_id = $form['role_id'];
        $model->status = $form['status'];
        $model->update_time = time();

        $ret = $model->save();
        if (!$ret) {
            return $this->error('修改失败');
        }
        return $this->success();
    }

    public function lists(Request $request){
        $currentPage = $request->get('currentPage', 1);
        $pageSize = $request->get('pageSize', 10);
        $key = $request->get('key', '');
        $offset = ($currentPage - 1) * $pageSize;

        $lists = SysProject::select([
            'sys_user.id',
            'sys_user.status',
            'sys_user.username',
            'sys_user.realname',
            'sys_user.role_id',
            DB::raw('sys_role.name as role_name'),
            'sys_user.avatar',
            'sys_user.email',
            'sys_user.mobile',
            'sys_user.create_time'
        ])->leftJoin('sys_role', 'role_id', '=', 'sys_role.id')
            ->where('sys_user.username', 'like', "%$key%")
            ->where('sys_user.status', '>', 0)
            ->limit($pageSize)
            ->offset($offset)
            ->get()
            ->toArray();

        return $this->success([
            'lists' => $lists
        ]);
    }
}

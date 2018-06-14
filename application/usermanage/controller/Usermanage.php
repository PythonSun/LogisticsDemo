<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 10:04
 */

namespace app\UserManage\controller;
use think\Controller;
use think\Request;

class Usermanage extends Controller
{
    public function usermanage(){
        $userinfo = \app\index\model\Admin::getsessioninfo();
        $this->assign('loginusername',$userinfo["fullname"]);
        return $this->fetch();
    }

    public function getusermanage()
    {
        $page = $_GET['page'];
        $limit = $_GET['limit'];
        if(isset($_GET['user_id'])){
            $user_id = intval($_GET['user_id']);
            $tablelist = \app\index\model\Admin::queryuserinfo($page,$limit,$user_id);
        }else{
            $tablelist = \app\index\model\Admin::queryuserinfo($page,$limit);
        }
        return $tablelist;
    }

    public function updateuser()
    {
        $param = Request::instance()->param();
        //dump($param['param']);
        $user = $param['param'];
        $result = \app\index\model\Admin::updateuser($user);
        return $result;
    }

    public function  deleteuser()
    {
        $param = $_POST;
        //dump($param['param']);
        $user = $param['param'];
        $result = \app\index\model\Admin::deleteuser($user);
        return $result;
    }

    public function updatedepartment(){
        $param = Request::instance()->param();
        $department = $param['organizeinfo'];
        $result = \app\index\model\Admin::updatedepartment($department);
        return $result;
    }

    /*退出登录*/
    public function logout(){
        $ret = \app\index\model\Admin::logout();
        return $ret;
    }

    /*获取登录session*/
    public function getsession(){
        $ret = \app\index\model\Admin::getsessioninfo();
        return $ret;
    }

    /*获取用户名检索表*/
    public function getserachfullname(){
        $condition = array();
        if(array_key_exists('serachText',$_POST)){
            $condition['serachText'] = $_POST['serachText'];
            $userinfo = \app\index\model\Admin::serachuserlike($condition);
            return $userinfo;
        }else{
            return null;
        }

    }
}
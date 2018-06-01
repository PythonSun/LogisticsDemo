<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/31
 * Time: 10:02
 */

namespace app\index\controller;


use think\Controller;

class Common extends Controller
{
    public function getserachmanarger()
    {
        $condition = array();
        if(array_key_exists('serachText',$_POST))
        {
            $condition['serachText'] = $_POST['serachText'];
        }
        if(array_key_exists('organize_id',$_POST))
        {
            $condition['organize_id'] = $_POST['organize_id'];
        }
        if(array_key_exists('role_name',$_POST))
        {
            $condition['role_name'] = $_POST['role_name'];
        }
        if(array_key_exists('organize_name',$_POST))
        {
            $condition['organize_name'] = $_POST['organize_name'];
        }
        $dbproductinfo = \app\index\model\Admin::serachuser($condition);
        return $dbproductinfo;
    }

    public function getserachdepartment()
    {
        $condition = array();
        if(array_key_exists('organize_name',$_POST))
        {
            $condition['name'] = $_POST['organize_name'];
        }
        $dbproductinfo = \app\index\model\Admin::serachdepartment($condition);
        return $dbproductinfo;
    }
}
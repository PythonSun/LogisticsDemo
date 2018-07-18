<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 9:55
 */

namespace app\usermanage\controller;


use think\Controller;
use think\Exception;
use think\Request;

class Associateorder extends Controller
{
    public function associateorder()
    {
        $companytable = \app\index\model\Admin::querydepartmentinfo(0);
        if(!empty($companytable))
            $this->assign("companylist",$companytable);
        return $this->fetch();
    }

    public function getdepartmentinfo()
    {
        $param = $_POST;
        $result = \app\index\model\Admin::querydepartmentinfo($param['param']);
        return $result;

    }

    public function getdspmanagerinfo()
    {
        $dep_id = $_POST['param'];
        $result = \app\index\model\Admin::getuserinfobydepid($dep_id);
        return $result;
    }

    public function getorder()
    {
        $page = $_GET['page'];
        $limit = $_GET['limit'];
        if(!isset($_GET['queryInfo']))
        {
            return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
        }
        $queryInfo = $_GET['queryInfo'];
        if($queryInfo['order_type'] == 0)
        {
            //订货单
            $tablelist = \app\index\model\Admin::querygoodsorder($page,$limit,$queryInfo);
            return $tablelist;
        }
        else
        {
            //售后单：更换/借样等
            $tablelist = \app\index\model\Admin::querysalesedconfirmorder($queryInfo['order_type'],$page,$limit,$queryInfo);
            return $tablelist;
        }

    }

    public function transferorder()
    {
        $param =  $_POST['param'];
        $result = \app\index\model\Admin::updatecsbelong($param);
        return $result;
    }
}
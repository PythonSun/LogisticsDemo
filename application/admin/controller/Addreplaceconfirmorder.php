<?php

namespace app\admin\controller;

use think\Controller;
use think\Exception;
use think\Request;

class Addreplaceconfirmorder extends Controller
{
    /*新增订单渲染方法*/
    public function addreplaceconfirmorder()
    {
        $organizeid = session("user_session");
        $depid = $organizeid["organize_id"];  //部门id
        $this->assign("depid", $depid);
        $date = date('Ymd');
        $this->assign("date", $date);
        /*$orderid = \app\index\model\Admin::getcsinfomaxid();
        $this->assign("orderid", $orderid);*/
        $companytable = \app\index\model\Admin::querydepartmentinfo(0);
        if (!empty($companytable))
            $this->assign("companylist", $companytable);
        return $this->fetch();
    }

    public function editreplaceconfirmorder()
    {
        $cs_id = $_GET['cs_id'];
        $organizeid = session("user_session");
        $depid = $organizeid["organize_id"];  //部门id
        $this->assign("depid", $depid);
        $date = date('Ymd');
        $this->assign("date", $date);
        /*$orderid = \app\index\model\Admin::getcsinfomaxid();
        $this->assign("orderid", $orderid);*/
        $companytable = \app\index\model\Admin::querydepartmentinfo(0);
        if (!empty($companytable))
            $this->assign("companylist", $companytable);
        $this->assign("cs_id", $cs_id);
        return $this->fetch('addreplaceconfirmorder');
    }
    /**新增订单（包含审批 清单）**/
    public function addconfirmorder()
    {
        $date_now = date("Y-m-d H:i:s");
        $cs_info = $_POST['cs_info'];
        $custom_info = $_POST['custom_info'];
        $delivery_info = $_POST['delivery_info'];
        $return_info = $_POST['return_info'];
        $cs_belong = $_POST['cs_belong'];
        $cs_examine = $_POST['cs_examine'];
        $order_goods_manager = $_POST['order_goods_manager'];

        $cs_info_id = \app\index\model\Admin::getcsinfomaxid('cs_belong','cs_id');
        $cs_info['write_date'] = $date_now;
        $cs_info['cs_id'] = $cs_info_id;
        $cs_belong['cs_id'] = $cs_info['cs_id'];
        $cs_belong['cs_belong_create_time'] = $date_now;

        $cs_belong_id = \app\index\model\Admin::getmaxtableidretid('cs_belong', 'cs_belong_id');
        $cs_belong['cs_belong_id'] = $cs_belong_id+1;
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);
        if (empty($ret_cs_belog)) {
            return false;
        }

        $custom_info_id = \app\index\model\Admin::getmaxtableidretid('custom_info', 'custom_info_id');
        $custom_info['custom_info_id'] = $custom_info_id+1;
        $ret_custom_info = \app\index\model\Admin::addcustominfo($custom_info);

        if (empty($ret_custom_info)) {
            return false;//添加失败删除
        }

        $delivery_info_id = \app\index\model\Admin::getmaxtableidretid('delivery_info', 'delivery_info_id');
        $delivery_info['delivery_info_id'] = $delivery_info_id+1;
        $ret_delivery_info = \app\index\model\Admin::adddeliveryinfo($delivery_info);
        if (empty($ret_delivery_info)) {
            return false;
        }

        $return_info_id = \app\index\model\Admin::getmaxtableidretid('return_info', 'return_info_id');
        $return_info['return_info_id'] = $return_info_id+1;
        $ret_return_info = \app\index\model\Admin::addreturninfo($return_info);
        if (empty($ret_return_info)) {
            return false;
        }
        $num = count($order_goods_manager);
        for ($i = 0; $i < $num; $i++) {
            $order_goods_manager_id = \app\index\model\Admin::getmaxtableidretid('order_goods_manager', 'order_goods_manager_id');
            $order_goods_manager[$i]['order_goods_manager_id'] = $order_goods_manager_id+1;
            $order_goods_manager[$i]['cs_id'] = $cs_info['cs_id'];
            $retmanager = \app\index\model\Admin::addordergoodsmanager($order_goods_manager[$i]);
        }
        $length = count($cs_examine);
        $cs_examine_id_array =array();
        for ($i = 0; $i < $length; $i++) {
            $cs_examine[$i]['cs_id'] = $cs_info['cs_id'];
            $user_id = $cs_examine[$i]['submit_user_id'];
            if ($i == 0) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($user_id, '总监');
                if (empty($dbleader)) {
                    return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];
            } else if ($i == 1) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($user_id, '总经理');
                if (empty($dbleader)) {
                    return false;
                    //return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];
            } else if ($i == 2) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($user_id, '财务部');
                if (empty($dbleader)) {
                    return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];
            }
            $cs_examine_id = \app\index\model\Admin::getmaxtableidretid('cs_examine', 'cs_examine_id');
            $cs_examine[$i]['cs_examine_id'] = $cs_examine_id+1;
            $cs_examine_id_array[$i]= $cs_examine_id+1;
            $rettest = \app\index\model\Admin::addcsexamine($cs_examine[$i]);
        }

        $cs_info['return_info_id'] = $return_info_id +1;
        $cs_info['custom_info_id'] = $custom_info_id +1;
        $cs_info['delivery_info_id'] = $delivery_info_id +1;
        $cs_info['cs_examine_ids'] = $cs_examine_id_array[0] . ',' . $cs_examine_id_array[1] . ',' . $cs_examine_id_array[2];
        $ret_confirm_order = \app\index\model\Admin::addconfirmorder($cs_info);
        if (empty($ret_confirm_order)) {
            $cs_belong_id = \app\index\model\Admin::getmaxtableidretid('cs_belong', 'cs_belong_id');

            \app\index\model\Admin::deleterowtableid('cs_belong', 'cs_belong_id', $cs_belong_id);
            \app\index\model\Admin::deleterowtableid('custom_info', 'custom_info_id', $custom_info_id);
            \app\index\model\Admin::deleterowtableid('delivery_info', 'delivery_info_id', $delivery_info_id);
            \app\index\model\Admin::deleterowtableid('return_info', 'return_info_id', $return_info_id);
            //删除上面的表
            //还有 order_goods_manager  cs_examine
            return false;
        }
        return $cs_info_id;
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

    public function getcsallinfo()
    {
        $param = $_POST;
        //return $param['param'];
        return \app\index\model\Admin::getallcsinfobycsid($param['param']);
    }
}
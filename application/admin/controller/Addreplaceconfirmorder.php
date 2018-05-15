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
        $date = date('Y-m-d');
        $this->assign("date", $date);
        $companytable = \app\index\model\Admin::querydepartmentinfo(0);
        if (!empty($companytable))
            $this->assign("companylist", $companytable);
        $this->assign("cs_id", "");
        $this->assign("current_user_type", 1);
        return $this->fetch();
    }

    public function editreplaceconfirmorder()
    {
        $cs_id = $_GET['cs_id'];
        $current_user_type = $_GET['current_user_type'];

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
        $this->assign("current_user_type", $current_user_type);
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

        $logistic_info = $_POST['logistic_info'];

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
        $ret_custom_info = \app\index\model\Admin::updatecustominfo($custom_info);

        if (empty($ret_custom_info)) {
            return false;//添加失败删除
        }

        $delivery_info_id = \app\index\model\Admin::getmaxtableidretid('delivery_info', 'delivery_info_id');
        $delivery_info['delivery_info_id'] = $delivery_info_id+1;
        $ret_delivery_info = \app\index\model\Admin::updatedeliveryinfo($delivery_info);
        if (empty($ret_delivery_info)) {
            return false;
        }

        $return_info_id = \app\index\model\Admin::getmaxtableidretid('return_info', 'return_info_id');
        $return_info['return_info_id'] = $return_info_id+1;
        $ret_return_info = \app\index\model\Admin::updatereturninfo($return_info);
        if (empty($ret_return_info)) {
            return false;
        }
        if(array_key_exists('order_goods_manager',$_POST)){
            $order_goods_manager = $_POST['order_goods_manager'];
            $num = count($order_goods_manager);
            for ($i = 0; $i < $num; $i++) {
                //order_goods_manager
                $order_goods_manager_id = \app\index\model\Admin::getmaxtableidretid('order_goods_manager', 'order_goods_manager_id');
                $order_goods_manager[$i]['order_goods_manager_id'] = $order_goods_manager_id+1;
                $order_goods_manager[$i]['cs_id'] = $cs_info['cs_id'];
                $retmanager = \app\index\model\Admin::updateordergoodsmanager($order_goods_manager[$i]);
                //order_goods_logistics
                $ogl_id = \app\index\model\Admin::getmaxtableidretid('order_goods_logistics', 'order_goods_manager_id');
                $order_goods_manager[$i]['ogl_id'] = $ogl_id+1;
                $order_goods_manager[$i]['ogl_time_stamp'] = $date_now;
                $order_goods_manager[$i]['user_id'] = $cs_belong['build_user_id']; //暂时不知道是报那个的id,先写经理的
                $retmanager = \app\index\model\Admin::updateordergoodslogistics($order_goods_manager[$i]);
            }

        }



        $length = count($cs_examine);
        $cs_examine_ids ="";
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
            $cs_examine_id = \app\index\model\Admin::getmaxtableidretid('cs_examine', 'cs_examine_id')+1;
            $cs_examine[$i]['cs_examine_id'] = $cs_examine_id;
            $cs_examine_ids.= "$cs_examine_id,";
            $rettest = \app\index\model\Admin::updatecsexamine($cs_examine[$i]);
        }

        //payment_info
        $payment_info = $_POST['payment_info'];
        $payment_info_id = \app\index\model\Admin::getmaxtableidretid('payment_info', 'payment_info_id');
        $payment_info['payment_info_id'] = $payment_info_id+1;
        \app\index\model\Admin::updatepaymentinfo($payment_info);


        $cs_info['return_info_id'] = $return_info_id +1;
        $cs_info['custom_info_id'] = $custom_info_id +1;
        $cs_info['delivery_info_id'] = $delivery_info_id +1;
        $cs_info['payment_info_id'] = $payment_info_id +1;
        $cs_info['cs_examine_ids'] = $cs_examine_ids;
        $ret_confirm_order = \app\index\model\Admin::updateconfirmorder($cs_info);

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

        if(array_key_exists('logistic_info',$_POST))
        {
            foreach ( $logistic_info as $item  )
            {
                $id = \app\index\model\Admin::getmaxtableidretid('logistics_info', 'logistics_id');
                $item['logistics_id'] = $id+1;
                $item['time_stamp'] = $date_now;
                $item['cs_id'] = $cs_info['cs_id'];
                $item['user_id'] = $cs_belong['build_user_id'];
                \app\index\model\Admin::updatelogisticinfo($item);
            }
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

    /**审批订单 审批提交内容保存**/
    public function deliverorder()
    {
        $cs_examine = $_POST['param'];
        if(empty($cs_examine) || count($cs_examine) == 0)
        {
            return "数据为空！";
        }
        foreach ($cs_examine as $examine)
        {
            \app\index\model\Admin::updatecsexamine($examine);
        }
        return "提交成功！";

    }
    /**审批订单 审批提交内容保存**/
    public function returnorder()
    {
        $cs_info = $_POST['cs_info'];
        \app\index\model\Admin::updateconfirmorder($cs_info);

        $cs_examine = $_POST['cs_examine'];

        \app\index\model\Admin::updatecsexamine($cs_examine);
        return "提交成功！";

    }
    /**经理修改订单 提交内容保存**/
    public function managereditanddeliverorder()
    {
        $date_now = date("Y-m-d H:i:s");

        //cs_belong
        $cs_belong = $_POST['cs_belong'];
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);
        if (empty($ret_cs_belog)) {
            return false;
        }

        ///custom_info
        $custom_info = $_POST['custom_info'];
        $ret_custom_info = \app\index\model\Admin::updatecustominfo($custom_info);
        if (empty($ret_custom_info)) {
            return false;//添加失败删除
        }
        //delivery_info
        $delivery_info = $_POST['delivery_info'];
        $ret_delivery_info = \app\index\model\Admin::updatedeliveryinfo($delivery_info);
        if (empty($ret_delivery_info)) {
            return false;
        }

        //return_info
        $return_info = $_POST['return_info'];
        $ret_return_info = \app\index\model\Admin::updatereturninfo($return_info);
        if (empty($ret_return_info)) {
            return false;
        }
        //order_goods_manager  order_goods_logistics
        if(array_key_exists('order_goods_manager',$_POST)){
            $order_goods_manager = $_POST['order_goods_manager'];
            $num = count($order_goods_manager);
            for ($i = 0; $i < $num; $i++) {
                //order_goods_manager
                if($order_goods_manager[$i]['order_goods_manager_id'] == '')
                {
                    $order_goods_manager_id = \app\index\model\Admin::getmaxtableidretid('order_goods_manager', 'order_goods_manager_id');
                    $order_goods_manager[$i]['order_goods_manager_id'] = $order_goods_manager_id+1;
                }
                $retmanager = \app\index\model\Admin::updateordergoodsmanager($order_goods_manager[$i]);
                //order_goods_logistics
                if($order_goods_manager[$i]['ogl_id'] == '')
                {
                    $ogl_id = \app\index\model\Admin::getmaxtableidretid('order_goods_logistics', 'order_goods_manager_id');
                    $order_goods_manager[$i]['ogl_id'] = $ogl_id+1;
                }
                $order_goods_manager[$i]['ogl_time_stamp'] = $date_now;
                $order_goods_manager[$i]['user_id'] = $cs_belong['build_user_id']; //暂时不知道是报那个的id,先写经理的
                $retmanager = \app\index\model\Admin::updateordergoodslogistics($order_goods_manager[$i]);
            }

        }
        //cs_examine
        $cs_examine = $_POST['cs_examine'];
        $length = count($cs_examine);
        $cs_examine_ids ="";
        for ($i = 0; $i < $length; $i++) {
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
            $cs_examine_id = \app\index\model\Admin::getmaxtableidretid('cs_examine', 'cs_examine_id')+1;
            $cs_examine[$i]['cs_examine_id'] = $cs_examine_id;
            $cs_examine_ids.= "$cs_examine_id,";
            $rettest = \app\index\model\Admin::updatecsexamine($cs_examine[$i]);
        }

        //cs_info
        $cs_info = $_POST['cs_info'];
        $cs_info['cs_examine_ids'] = $cs_examine_ids;
        $ret_confirm_order = \app\index\model\Admin::updateconfirmorder($cs_info);

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
        if(array_key_exists('logistic_info',$_POST))
        {
            $logistic_info = $_POST['logistic_info'];
            foreach ( $logistic_info as $item  )
            {
                if( $item['logistics_id'] == "")
                {
                    $id = \app\index\model\Admin::getmaxtableidretid('logistics_info', 'logistics_id');
                    $item['logistics_id'] = $id+1;
                    $item['time_stamp'] = $date_now;
                    $item['cs_id'] = $cs_info['cs_id'];
                    $item['user_id'] = $cs_belong['build_user_id'];
                }
                \app\index\model\Admin::updatelogisticinfo($item);
            }
        }
        if(array_key_exists('order_goods_delete_row',$_POST))
        {
            $order_goods_delete_row = $_POST['order_goods_delete_row'];
            foreach ($order_goods_delete_row as $item )
            {
                \app\index\model\Admin::deleterowtableid('order_goods_manager', 'order_goods_manager_id', $item);
            }
        }

        if(array_key_exists('logistic_info_delete_row',$_POST))
        {
            $logistic_info_delete_row = $_POST['logistic_info_delete_row'];
            foreach ($logistic_info_delete_row as $item )
            {
                \app\index\model\Admin::deleterowtableid('logistic_info', 'logistic_info_id', $item);
            }
        }
    }
    /**经理修改订单 内容保存**/
    public function managereditandsaveorder()
    {
        $date_now = date("Y-m-d H:i:s");
        //cs_belong
        $cs_belong = $_POST['cs_belong'];
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);
        if (empty($ret_cs_belog)) {
            return false;
        }

        ///custom_info
        $custom_info = $_POST['custom_info'];
        $ret_custom_info = \app\index\model\Admin::updatecustominfo($custom_info);
        if (empty($ret_custom_info)) {
            return false;//添加失败删除
        }
        //delivery_info
        $delivery_info = $_POST['delivery_info'];
        $ret_delivery_info = \app\index\model\Admin::updatedeliveryinfo($delivery_info);
        if (empty($ret_delivery_info)) {
            return false;
        }

        //return_info
        $return_info = $_POST['return_info'];
        $ret_return_info = \app\index\model\Admin::updatereturninfo($return_info);
        if (empty($ret_return_info)) {
            return false;
        }
        //order_goods_manager  order_goods_logistics
        if(array_key_exists('order_goods_manager',$_POST)){
            $order_goods_manager = $_POST['order_goods_manager'];
            $num = count($order_goods_manager);
            for ($i = 0; $i < $num; $i++) {
                //order_goods_manager
                if($order_goods_manager[$i]['order_goods_manager_id'] == '')
                {
                    $order_goods_manager_id = \app\index\model\Admin::getmaxtableidretid('order_goods_manager', 'order_goods_manager_id');
                    $order_goods_manager[$i]['order_goods_manager_id'] = $order_goods_manager_id+1;
                }
                $retmanager = \app\index\model\Admin::updateordergoodsmanager($order_goods_manager[$i]);
                //order_goods_logistics
                if($order_goods_manager[$i]['ogl_id'] == '')
                {
                    $ogl_id = \app\index\model\Admin::getmaxtableidretid('order_goods_logistics', 'order_goods_manager_id');
                    $order_goods_manager[$i]['ogl_id'] = $ogl_id+1;
                }
                $order_goods_manager[$i]['ogl_time_stamp'] = $date_now;
                $order_goods_manager[$i]['user_id'] = $cs_belong['build_user_id']; //暂时不知道是报那个的id,先写经理的
                $retmanager = \app\index\model\Admin::updateordergoodslogistics($order_goods_manager[$i]);
            }

        }
        //cs_examine
        $cs_examine = $_POST['cs_examine'];
        $length = count($cs_examine);
        for ($i = 0; $i < $length; $i++) {
            \app\index\model\Admin::updatecsexamine($cs_examine[$i]);
        }

        //cs_info
        $cs_info = $_POST['cs_info'];
        $ret_confirm_order = \app\index\model\Admin::updateconfirmorder($cs_info);

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
        if(array_key_exists('logistic_info',$_POST))
        {
            $logistic_info = $_POST['logistic_info'];
            foreach ( $logistic_info as $item  )
            {
                if( $item['logistics_id'] == "")
                {
                    $id = \app\index\model\Admin::getmaxtableidretid('logistics_info', 'logistics_id');
                    $item['logistics_id'] = $id+1;
                    $item['time_stamp'] = $date_now;
                    $item['cs_id'] = $cs_info['cs_id'];
                    $item['user_id'] = $cs_belong['build_user_id'];
                }
                \app\index\model\Admin::updatelogisticinfo($item);
            }
        }
        if(array_key_exists('order_goods_delete_row',$_POST))
        {
            $order_goods_delete_row = $_POST['order_goods_delete_row'];
            foreach ($order_goods_delete_row as $item )
            {
                \app\index\model\Admin::deleterowtableid('order_goods_manager', 'order_goods_manager_id', $item);
            }
        }

        if(array_key_exists('logistic_info_delete_row',$_POST))
        {
            $logistic_info_delete_row = $_POST['logistic_info_delete_row'];
            foreach ($logistic_info_delete_row as $item )
            {
                \app\index\model\Admin::deleterowtableid('logistic_info', 'logistic_info_id', $item);
            }
        }
    }

    /**经理修改订单 内容保存**/
    public function logisticeditandsaveorder()
    {
        $date_now = date("Y-m-d H:i:s");
        //cs_belong
        $cs_belong = $_POST['cs_belong'];
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);
        if (empty($ret_cs_belog)) {
            return false;
        }
        $payment_info = $_POST['payment_info'];
        \app\index\model\Admin::updatepaymentinfo($payment_info);

        //order_goods_manager  order_goods_logistics
        if(array_key_exists('order_goods_manager',$_POST)){
            $order_goods_manager = $_POST['order_goods_manager'];
            $num = count($order_goods_manager);
            for ($i = 0; $i < $num; $i++) {
                //order_goods_manager
                if($order_goods_manager[$i]['order_goods_manager_id'] == '')
                {
                    $order_goods_manager_id = \app\index\model\Admin::getmaxtableidretid('order_goods_manager', 'order_goods_manager_id');
                    $order_goods_manager[$i]['order_goods_manager_id'] = $order_goods_manager_id+1;
                }
                $retmanager = \app\index\model\Admin::updateordergoodsmanager($order_goods_manager[$i]);
                //order_goods_logistics
                if($order_goods_manager[$i]['ogl_id'] == '')
                {
                    $ogl_id = \app\index\model\Admin::getmaxtableidretid('order_goods_logistics', 'order_goods_manager_id');
                    $order_goods_manager[$i]['ogl_id'] = $ogl_id+1;
                }
                $order_goods_manager[$i]['ogl_time_stamp'] = $date_now;
                $order_goods_manager[$i]['user_id'] = $cs_belong['build_user_id']; //暂时不知道是报那个的id,先写经理的
                $retmanager = \app\index\model\Admin::updateordergoodslogistics($order_goods_manager[$i]);
            }

        }

        //cs_info
        $cs_info = $_POST['cs_info'];
        $ret_confirm_order = \app\index\model\Admin::updateconfirmorder($cs_info);

    }

}
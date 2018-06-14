<?php
namespace app\admin\controller;
use think\Controller;

class Addrepairconfirmorder extends Controller
{
    /*新增订单渲染方法*/
    public function addrepairconfirmorder()
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
        $this->assign("current_user_type", $this->getcurrentusertype());

        $userinfo = Array();
        $userinfo['user_id'] =$organizeid['user_id'];
        $userinfo['department_id'] =$organizeid['organize_id'];
        $ret= \app\index\model\Admin::getclassinfobyproperty('organize','organize_id',$userinfo['department_id']);
        $userinfo['organize_id'] =0;
        if(!empty($ret))
            $userinfo['organize_id'] = $ret[0]['parent_id'];
        $userinfo['phone'] =$organizeid['phone'];
        $this->assign("userinfo", json_encode($userinfo));

        $this->init();

        return $this->fetch();
    }

    public function init()
    {
        $producttype = \app\index\model\Admin::getclassinfo('product_type','product_type_id');
        $brand = \app\index\model\Admin::getclassinfo('product_brand','brand_id');
        $place = \app\index\model\Admin::getclassinfo('product_place','place_id');
        $uncproduct = \app\index\model\Admin::getuncproduct();
        if (!empty($brand)){
            $this->assign('producttypelist',$producttype);
        }
        if (!empty($brand)){
            $this->assign('brandlist',$brand);
        }
        if (!empty($place)){
            $this->assign('placelist',$place);
        }
        if (!empty($uncproduct)){
            $this->assign('uncproductlist',$uncproduct);
        }
    }

    public function  getcurrentusertype()
    {
        $queryuserinfo = session("user_querypower");
        $rolename = $queryuserinfo['role_name'];
        if( $rolename == "管理人员" || $rolename == "部长/主管"||$rolename == "物流部人员")
        {
            return 5;
        }
        elseif ($rolename == "财务人员")
        {
            return 4;
        }

        elseif($rolename == "总经理")
        {
            return 3;
        }
        elseif($rolename == "部门总监")
        {
            return 2;
        }
        elseif($rolename == "区域经理")
        {
            return 1;
        }
    }

    public function editrepairconfirmorder()
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

        $userinfo = array();
        $userinfo['user_id'] =$organizeid['user_id'];
        $userinfo['department_id'] =$organizeid['organize_id'];
        $ret= \app\index\model\Admin::getclassinfobyproperty('organize','organize_id',$userinfo['department_id']);
        $userinfo['organize_id'] =0;
        if(!empty($ret))
            $userinfo['organize_id'] = $ret[0]['parent_id'];
        $userinfo['phone'] =$organizeid['phone'];
        $this->assign("userinfo", json_encode($userinfo));

        $this->init();

        return $this->fetch('addrepairconfirmorder');
    }
    /**新增订单（包含审批 清单）**/
    public function addrepairorder()
    {
        $data = array();
        $index = 0;
        $date_now = date("Y-m-d H:i:s");
        $cs_info = $_POST['cs_info'];
        $custom_info = $_POST['custom_info'];
        $delivery_info = $_POST['delivery_info'];
        $return_info = $_POST['return_info'];
        $cs_belong = $_POST['cs_belong'];
        $cs_examine = $_POST['cs_examine'];

        $cs_info_id = \app\index\model\Admin::getcsinfomaxid('cs_belong','cs_id');
        $cs_info['write_date'] = $date_now;
        $cs_info['cs_id'] = $cs_info_id;
        $cs_info['return_info_id'] = '-1';
        $cs_info['custom_info_id'] = '-1';
        $cs_info['delivery_info_id'] = '-1';
        $cs_info['payment_info_id'] = '-1';
        $cs_info['cs_examine_ids'] = "";
        $ret_confirm_order = \app\index\model\Admin::updateconfirmorder($cs_info);
        $data[$index][0] = 'cs_info';
        $data[$index][1] = 'cs_id';
        $data[$index][2] = $cs_info_id;
        $index++;
        if (empty($ret_confirm_order)||$ret_confirm_order == false) {
            $this->deldata($data);
            return false;
        }

        $cs_belong['cs_id'] = $cs_info['cs_id'];
        $cs_belong['cs_belong_create_time'] = $date_now;

        $cs_belong_id = \app\index\model\Admin::getmaxtableidretid('cs_belong', 'cs_belong_id')+1;
        $cs_belong['cs_belong_id'] = $cs_belong_id;
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);
        $data[$index][0] = 'cs_belong';
        $data[$index][1] = 'cs_belong_id';
        $data[$index][2] = $cs_belong_id;
        $index++;
        if (empty($ret_cs_belog)||$ret_cs_belog == false) {
            $this->deldata($data);
            return false;
        }

        $custom_info_id = \app\index\model\Admin::getmaxtableidretid('custom_info', 'custom_info_id')+1;
        $custom_info['custom_info_id'] = $custom_info_id;
        $ret_custom_info = \app\index\model\Admin::updatecustominfo($custom_info);
        $data[$index][0] = 'custom_info';
        $data[$index][1] = 'custom_info_id';
        $data[$index][2] = $custom_info_id;
        $index++;
        if (empty($ret_custom_info)||$ret_custom_info == false) {
            $this->deldata($data);
            return false;//添加失败删除
        }

        $delivery_info_id = \app\index\model\Admin::getmaxtableidretid('delivery_info', 'delivery_info_id')+1;
        $delivery_info['delivery_info_id'] = $delivery_info_id;
        $ret_delivery_info = \app\index\model\Admin::updatedeliveryinfo($delivery_info);
        $data[$index][0] = 'delivery_info';
        $data[$index][1] = 'delivery_info_id';
        $data[$index][2] = $delivery_info_id;
        $index++;
        if (empty($ret_delivery_info)||$ret_delivery_info == false) {
            $this->deldata($data);
            return false;
        }

        $return_info_id = \app\index\model\Admin::getmaxtableidretid('return_info', 'return_info_id')+1;
        $return_info['return_info_id'] = $return_info_id;
        $ret_return_info = \app\index\model\Admin::updatereturninfo($return_info);
        $data[$index][0] = 'return_info';
        $data[$index][1] = 'return_info_id';
        $data[$index][2] = $delivery_info_id;
        $index++;
        if (empty($ret_return_info)||$ret_return_info == false) {
            $this->deldata($data);
            return false;
        }
        if(array_key_exists('order_goods_manager',$_POST)){
            $order_goods_manager = $_POST['order_goods_manager'];
            $num = count($order_goods_manager);
            for ($i = 0; $i < $num; $i++) {
                //order_goods_manager
                $order_goods_manager_id = \app\index\model\Admin::getmaxtableidretid('order_goods_manager', 'order_goods_manager_id')+1;
                $order_goods_manager[$i]['order_goods_manager_id'] = $order_goods_manager_id;
                $order_goods_manager[$i]['cs_id'] = $cs_info['cs_id'];
                $data[$index][0] = 'order_goods_manager';
                $data[$index][1] = 'order_goods_manager_id';
                $data[$index][2] = $order_goods_manager_id;
                $index++;
                $retmanager = \app\index\model\Admin::updateordergoodsmanager($order_goods_manager[$i]);
                if(empty($retmanager)||$retmanager == false)
                {
                    $this->deldata($data);
                    return false;
                }
                //order_goods_logistics
                $ogl_id = \app\index\model\Admin::getmaxtableidretid('order_goods_logistics', 'order_goods_manager_id')+1;
                $order_goods_manager[$i]['ogl_id'] = $ogl_id;
                $order_goods_manager[$i]['ogl_time_stamp'] = $date_now;
                $order_goods_manager[$i]['user_id'] = $cs_belong['build_user_id']; //暂时不知道是报那个的id,先写经理的
                $retmanager = \app\index\model\Admin::updateordergoodslogistics($order_goods_manager[$i]);
                $data[$index][0] = 'order_goods_logistics';
                $data[$index][1] = 'order_goods_manager_id';
                $data[$index][2] = $ogl_id;
                $index++;
                if(empty($retmanager)||$retmanager == false)
                {
                    $this->deldata($data);
                    return false;
                }
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
                    $this->deldata($data);
                    return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];

            } else if ($i == 1) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($user_id, '总经理');
                if (empty($dbleader)) {
                    $this->deldata($data);
                    return false;
                    //return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];
            } else if ($i == 2) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($user_id, '财务人员');
                if (empty($dbleader) ) {
                    $this->deldata($data);
                    return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];
            }
            $cs_examine_id = \app\index\model\Admin::getmaxtableidretid('cs_examine', 'cs_examine_id')+1;
            $cs_examine[$i]['cs_examine_id'] = $cs_examine_id;
            $cs_examine_ids.= "$cs_examine_id,";
            $data[$index][0] = 'cs_examine';
            $data[$index][1] = 'cs_examine_id';
            $data[$index][2] = $cs_examine_id;
            $index++;
            $rettest = \app\index\model\Admin::updatecsexamine($cs_examine[$i]);
            if(empty($rettest) || $rettest == false)
            {
                $this->deldata($data);
                return false;
            }
        }

        //payment_info
        $payment_info = $_POST['payment_info'];
        $payment_info_id = \app\index\model\Admin::getmaxtableidretid('payment_info', 'payment_info_id')+1;
        $payment_info['payment_info_id'] = $payment_info_id;
        $data[$index][0] = 'return_info';
        $data[$index][1] = 'return_info_id';
        $data[$index][2] = $delivery_info_id;
        $index++;
        $rettest = \app\index\model\Admin::updatepaymentinfo($payment_info);
        if(empty($rettest) || $rettest == false)
        {
            $this->deldata($data);
            return false;
        }

        $cs_info['return_info_id'] = $return_info_id ;
        $cs_info['custom_info_id'] = $custom_info_id ;
        $cs_info['delivery_info_id'] = $delivery_info_id ;
        $cs_info['payment_info_id'] = $payment_info_id ;
        $cs_info['cs_examine_ids'] = $cs_examine_ids;
        \app\index\model\Admin::updateconfirmorder($cs_info);

        return true;
    }

    public function deldata($data)
    {
        if(empty($data))
            return ;
        foreach ($data as $item)
        {
            \app\index\model\Admin::deleterowtableid($item[0],$item[1],$item[2]);
        }
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

        foreach ($cs_examine as $examine)
        {
            \app\index\model\Admin::updatecsexamine($examine);
        }
        return "提交成功！";

    }
    /**经理修改订单 提交内容保存**/
    public function managereditanddeliverorder()
    {

        $date_now = date("Y-m-d H:i:s");

        //cs_belong
        $cs_belong = $_POST['cs_belong'];
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);

        ///custom_info
        $custom_info = $_POST['custom_info'];
        $ret_custom_info = \app\index\model\Admin::updatecustominfo($custom_info);
        //delivery_info
        $delivery_info = $_POST['delivery_info'];
        $ret_delivery_info = \app\index\model\Admin::updatedeliveryinfo($delivery_info);


        //return_info
        $return_info = $_POST['return_info'];
        $ret_return_info = \app\index\model\Admin::updatereturninfo($return_info);


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

        $cs_info = $_POST['cs_info'];

        //cs_examine
        $cs_examine = $_POST['cs_examine'];
        $length = count($cs_examine);
        $cs_examine_ids ="";
        for ($i = 0; $i < $length; $i++) {
            if ($i == 0) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($cs_belong['build_user_id'], '总监');
                if (empty($dbleader)) {
                    return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];

            } else if ($i == 1) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($cs_belong['build_user_id'], '总经理');
                if (empty($dbleader)) {
                    return false;
                    //return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];
            } else if ($i == 2) {
                $dbleader = \app\index\model\Admin::getdepleaderbyuserid($cs_belong['build_user_id'], '财务人员');
                if (empty($dbleader)) {
                    return false;
                }
                $cs_examine[$i]['examine_user_id'] = $dbleader[0]['user_id'];
                $cs_examine[$i]['cs_examine_name'] = $dbleader[0]['fullname'];
            }
            $cs_examine[$i]['cs_id'] = $cs_info['cs_id'];
            $cs_examine_id = \app\index\model\Admin::getmaxtableidretid('cs_examine', 'cs_examine_id')+1;
            $cs_examine[$i]['cs_examine_id'] = $cs_examine_id;
            $cs_examine_ids.= "$cs_examine_id,";
            $rettest = \app\index\model\Admin::updatecsexamine($cs_examine[$i]);
        }
        //cs_info

        $cs_info['cs_examine_ids'] = $cs_examine_ids;
        $ret_confirm_order = \app\index\model\Admin::updateconfirmorder($cs_info);


        if(array_key_exists('order_goods_delete_row',$_POST))
        {
            $order_goods_delete_row = $_POST['order_goods_delete_row'];
            foreach ($order_goods_delete_row as $item )
            {
                \app\index\model\Admin::deleterowtableid('order_goods_manager', 'order_goods_manager_id', $item);
            }
        }

        return true;

    }
    /**经理修改订单 内容保存**/
    public function managereditandsaveorder()
    {
        $date_now = date("Y-m-d H:i:s");
        //cs_belong
        $cs_belong = $_POST['cs_belong'];
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);

        ///custom_info
        $custom_info = $_POST['custom_info'];
        $ret_custom_info = \app\index\model\Admin::updatecustominfo($custom_info);

        //delivery_info
        $delivery_info = $_POST['delivery_info'];
        $ret_delivery_info = \app\index\model\Admin::updatedeliveryinfo($delivery_info);


        //return_info
        $return_info = $_POST['return_info'];
        $ret_return_info = \app\index\model\Admin::updatereturninfo($return_info);

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

        if(array_key_exists('order_goods_delete_row',$_POST))
        {
            $order_goods_delete_row = $_POST['order_goods_delete_row'];
            foreach ($order_goods_delete_row as $item )
            {
                \app\index\model\Admin::deleterowtableid('order_goods_manager', 'order_goods_manager_id', $item);
            }
        }

    }

    /**物流修改订单 内容保存**/
    public function logisticeditandsaveorder()
    {
        $date_now = date("Y-m-d H:i:s");
        $del_unc_ofg_detail_id_arr = null;

        //payment_info
        $payment_info = $_POST['payment_info'];
        \app\index\model\Admin::updatepaymentinfo($payment_info);


        $cs_info = $_POST['cs_info'];

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
                $order_goods_manager[$i]['user_id'] = $cs_info['cur_process_user_id']; //暂时不知道是报那个的id,先写经理的
                $retmanager = \app\index\model\Admin::updateordergoodslogistics($order_goods_manager[$i]);
            }

        }




        $uoi_id = "";
        $unc_ofg_info = $_POST['unc_ofg_info'];
        $unc_ofg_detail = $_POST['unc_ofg_detail'];
        $arr = array();
        if (!empty($unc_ofg_info)){
            array_push($arr,526);
            /*******是否新增******/
            $uoi_id = $unc_ofg_info['uoi_id'];
            if ($uoi_id == ""){
                $uoi_id = \app\index\model\Admin::getmaxtableidretid('unc_ofg_info', 'uoi_id') + 1;
                $unc_ofg_info['uoi_date'] = $date_now;
                $unc_ofg_info['uoi_id'] = $uoi_id;
            }else{
                //$uoi_id = "";
            }
            $retfee_info = \app\index\model\Admin::updateunc_ofg_info($unc_ofg_info);
            array_push($arr,538);
        }else{
            array_push($arr,540);
            $uoi_id = "0";
            $unc_ofg_info_id = $cs_info['unc_ofg_info_id'];
            if (!empty($unc_ofg_info_id)){
                array_push($arr,543);
                \app\index\model\Admin::deleterowtableid('unc_ofg_info','uoi_id',$unc_ofg_info_id);
                $detail = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.unc_ofg_detail','unc_ofg_info_id',$unc_ofg_info_id);
                $datail_length = count($detail);
                for ($i = 0; $i < $datail_length; $i++){
                    array_push($arr,548);
                    \app\index\model\Admin::deleterowtableid('unc_ofg_detail','uod_id',$detail[$i]['uod_id']);
                }
            }
        }
        if (!empty($unc_ofg_info) && !empty($unc_ofg_detail)) {
            $unc_ofg_detail_length = count($unc_ofg_detail);
            for ($i = 0; $i < $unc_ofg_detail_length; $i++) {
                $uod_id = $unc_ofg_detail[$i]['uod_id'];
                if ($uod_id == "") {
                    $uod_id = \app\index\model\Admin::getmaxtableidretid('unc_ofg_detail', 'uod_id') + 1;
                    $unc_ofg_detail[$i]['uod_id'] = $uod_id;
                    $unc_ofg_detail[$i]['unc_ofg_info_id'] = $unc_ofg_info['uoi_id'];
                }
                $retunc_ofg_detail = \app\index\model\Admin::updateunc_ofg_detail($unc_ofg_detail[$i]);
            }
        }
        //cs_info
        $cs_info['unc_ofg_info_id'] = $uoi_id;
        $ret_confirm_order = \app\index\model\Admin::updateconfirmorder($cs_info);

        if(array_key_exists('del_unc_ofg_detail_id_arr',$_POST)){
            $del_unc_ofg_detail_id_arr = $_POST['del_unc_ofg_detail_id_arr'];
            if(!empty($del_unc_ofg_detail_id_arr))
            {
                foreach ($del_unc_ofg_detail_id_arr as $item)
                {
                    \app\index\model\Admin::deleterowtableid('unc_ofg_detail','uod_id',$item);
                }
            }


        }

        //cs_examine
        if(array_key_exists('cs_examine',$_POST)){
            $cs_examine = $_POST['cs_examine'];
            if(!empty($cs_examine) && count($cs_examine)>0)
            {
                foreach ( $cs_examine as $item  )
                {
                    \app\index\model\Admin::updatecsexamine($item);
                }
            }




        }
    }

    /*将订单状态改为取消*/
    public function cancelcsinfo(){
        $cs_id = $_POST['cs_id'];
        $ret = \app\index\model\Admin::cancelcsinfobyid($cs_id);
        return $ret;
    }
}
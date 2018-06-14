<?php
namespace app\admin\controller;
use think\Controller;

class Addborrowconfirmorder extends Controller
{
    /*新增订单渲染方法*/
    public function addborrowconfirmorder()
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

    public function editborrowconfirmorder()
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

        return $this->fetch('addborrowconfirmorder');
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

   
    /*将订单状态改为取消*/
    public function cancelcsinfo(){
        $cs_id = $_POST['cs_id'];
        $ret = \app\index\model\Admin::cancelcsinfobyid($cs_id);
        return $ret;
    }

    public function printuncorder(){
        $printdata = $_GET['printdata'];
        $strJson = str_replace('/$/@','"',$printdata);
        $obj = json_decode($strJson);
        $template_name = "非定型产品确认单.xls";
        $file_name = '非定型产品确认单';
        $file_extend = 'xlsx';
        \app\index\model\Admin::printuncordergoods($file_name,$file_extend,$template_name,$obj);
    }
}
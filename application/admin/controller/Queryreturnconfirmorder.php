<?php
namespace app\admin\controller;
use think\Controller;
use think\Input;

class Queryreturnconfirmorder extends Controller
{
	/*查询订单渲染方法*/
    public function queryreturnconfirmorder(){

        $producttype = \app\index\model\Admin::getclassinfo('dsp_logistic.product_type','product_type_id');
        $this->assign('productlist',$producttype);

        $productPlace = \app\index\model\Admin::getclassinfo('dsp_logistic.product_place','place_id');
        $this->assign('placelist',$productPlace);

        $productBrand = \app\index\model\Admin::getclassinfo('dsp_logistic.product_brand','brand_id');
        $this->assign('brandlist',$productBrand);

        $uncProduct = \app\index\model\Admin::getclassinfo('dsp_logistic.unc_product','unc_product_id');
        $this->assign('unclist',$uncProduct);

        /*获取权限设置*/
        $userinfo = \app\index\model\Admin::getsessioninfo();
        $role_id = intval($userinfo["role_id"]);
        $role_info = \app\index\model\Admin::queryroleinfo($role_id);

        $exportgoodspower = 0;
        $exportreplacepower =0;
        $exportborrowpower = 0;
        $exportreturnpower = ($role_info[0]['return_goods_permission'])&0x10;
        $exportpartspower = 0;
        $exportrepairpower = 0;
        $exportalternativepower = 0;

        $editreturnpower = ($role_info[0]['return_goods_permission'])&0x02;
        $deletereturnpower = ($role_info[0]['return_goods_permission'])&0x04;

        $this->assign('editreturnpower',$editreturnpower);
        $this->assign('deletereturnpower',$deletereturnpower);

        $this->assign('exportgoodspower',$exportgoodspower);
        $this->assign('exportreplacepower',$exportreplacepower);
        $this->assign('exportborrowpower',$exportborrowpower);
        $this->assign('exportreturnpower',$exportreturnpower);
        $this->assign('exportpartspower',$exportpartspower);
        $this->assign('exportrepairpower',$exportrepairpower);
        $this->assign('exportalternativepower',$exportalternativepower);

        $queryuserinfo = session("user_querypower");
        $rolename = $queryuserinfo['role_name'];
        if( $rolename == "管理人员" || $rolename == "部长/主管"||$rolename == "物流部人员")
        {
            $this->assign('current_user_type',5);
        }
        elseif ($rolename == "财务人员")
        {
            $this->assign('current_user_type',4);
        }

        elseif($rolename == "总经理")
        {
            $this->assign('current_user_type',3);
        }
        elseif($rolename == "部门总监")
        {
            $this->assign('current_user_type',2);
        }
        elseif($rolename == "区域经理")
        {
            $this->assign('current_user_type',1);
        }
        elseif($rolename == "部门助理")
        {
            $this->assign('current_user_type',6);
        }

        return $this->fetch();
    }

    public function getexamineorder(){
        $queryuserinfo = session("user_querypower");
        $page = $_GET['page'];
        $limit = $_GET['limit'];
        $type = 0x03;

        if(isset($_GET['queryInfo']))
            $tablelist = \app\index\model\Admin::queryConfirmOrder($queryuserinfo,$page,$limit,$type,$_GET['queryInfo']);
        else
            $tablelist = \app\index\model\Admin::queryConfirmOrder($queryuserinfo,$page,$limit,$type,null);
        return $tablelist;
    }

    /*退货确认单*/
    public function exportreturnconfirmorder(){
        $queryuserinfo = session("user_querypower");
        $organizename = "";
        if (array_key_exists('organizename',$queryuserinfo)){
            $organizename = $queryuserinfo["organizename"];
        }
        $departmentname = "";
        if (array_key_exists('departmentname',$queryuserinfo)){
            $departmentname = $queryuserinfo["departmentname"];
        }
        $areamanager = "";
        if (array_key_exists('areamanager',$queryuserinfo)){
            $areamanager = $queryuserinfo["areamanager"];
        }
        $template_name = "订单登记系统导出.xlsx";
        $type=0x03;
        $param = json_decode($_GET['param']);
        $file_name = $_GET['file_name'];
        $file_extend = 'xlsx';

        $ret = \app\index\model\Admin::queryexportcsinfoconfirmorder($param,$type,$organizename,$departmentname,$areamanager);
        \app\index\model\Admin::exportcsinfoconfirmorder($file_name,$file_extend,$template_name,$ret,$type);
    }

    /*退货确认单*/
    public function printreturnconfirmorder(){
        $template_name = "退货确认单.xlsx";
        $type=0x03;
        $cs_id = $_GET['cs_id'];
        $file_name = '退货确认单';
        $file_extend = 'xlsx';

        $ret = \app\index\model\Admin::queryprintcsinfoorder($cs_id,$type);
        \app\index\model\Admin::printreplaceconfirmorder($file_name,$file_extend,$template_name,$ret,$type);
    }
}
<?php
namespace app\admin\controller;
use think\Controller;
use think\Input;

class Querygoodsconfirmorder extends Controller
{
	/*查询订单渲染方法*/
    public function querygoodsconfirmorder(){
        $organizeInfo = \app\index\model\Admin::getclassinfo('dsp_logistic.organize','organize_id');
        $this->assign('organizelist',$organizeInfo);

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

        $exportgoodspower = ($role_info[0]['order_goods_permission'])&0x10;
        $exportreplacepower = 0;
        $exportborrowpower = 0;
        $exportreturnpower = 0;
        $exportpartspower = 0;
        $exportrepairpower = 0;
        $exportalternativepower = 0;
        $editgoodspower = ($role_info[0]['order_goods_permission'])&0x02;
        $deletegoodspower = ($role_info[0]['order_goods_permission'])&0x04;

        $this->assign('editgoodspower',$editgoodspower);
        $this->assign('deletegoodspower',$deletegoodspower);
        $this->assign('exportgoodspower',$exportgoodspower);
        $this->assign('exportreplacepower',$exportreplacepower);
        $this->assign('exportborrowpower',$exportborrowpower);
        $this->assign('exportreturnpower',$exportreturnpower);
        $this->assign('exportpartspower',$exportpartspower);
        $this->assign('exportrepairpower',$exportrepairpower);
        $this->assign('exportalternativepower',$exportalternativepower);
        return $this->fetch();
    }

    public function getexamineorder(){
        $queryuserinfo = session("user_querypower");
        $page = $_GET['page'];
        $limit = $_GET['limit'];
        $type = 0x01;
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
        if(isset($_GET['queryInfo'])){
            $queryInfo = $_GET['queryInfo'];
            if(array_key_exists("organizename",$queryInfo)){
                $queryInfo["organizename"] = $queryInfo["organizename"];
            }else{
                $queryInfo["organizename"] = "";
            }

            $queryInfo["departmentname"] = $queryInfo["departname"];
            $mode = $this->getquerymode($queryInfo);
            if($mode == 0)
            {
                if($areamanager == "" && $organizename == "" &&$departmentname == "")
                    $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex($page,$limit); //物流，财务查
                else
                    $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex1($page,$limit,$organizename,$departmentname,$areamanager); //销售部查
            }
            elseif ($mode == 1)
            {
                $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex2($page,$limit,$organizename,$departmentname,$areamanager,$queryInfo);
            }
            elseif ($mode == 2 && $areamanager == "" && $organizename == "" &&$departmentname == "")
            {
                $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex3($page,$limit,$organizename,$departmentname,$areamanager,$queryInfo);
            }
            elseif ($mode == 3 && $areamanager == "" && $organizename == "" &&$departmentname == "")
            {
                $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex4($page,$limit,$organizename,$departmentname,$areamanager,$queryInfo);
            }
            elseif ($mode == 4 && $areamanager == "" && $organizename == "" &&$departmentname == "")
            {
                $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex5($page,$limit,$organizename,$departmentname,$areamanager,$queryInfo);
            }
            elseif ($mode == 5 )
            {
                if($areamanager == "" && $organizename == "" &&$departmentname == "")
                    $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex($page,$limit,$queryInfo['orderstate']);
                else
                    $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex2($page,$limit,$organizename,$departmentname,$areamanager,$queryInfo);
            }
            else
                $tablelist = \app\index\model\Admin::querygoodsorderinfo($organizename,$departmentname,$areamanager,$type,$page,$limit,$queryInfo);
        }else{
            //$tablelist = \app\index\model\Admin::querygoodsorderinfo($organizename,$departmentname,$areamanager,$type,$page,$limit);
           if($areamanager == "" && $organizename == "" &&$departmentname == "")
               $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex($page,$limit); //物流，财务查
           else
               $tablelist = \app\index\model\Admin::querygoodsorderinfo_ex1($page,$limit,$organizename,$departmentname,$areamanager); //销售部查
        }
        return $tablelist;
    }
    //根据条件返回查询方式
    public function getquerymode($queryInfo)
    {
        if($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
            $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
            $queryInfo['orderstate'] == "" && $queryInfo['order_id'] == ""&&
            $queryInfo['receiver_name'] == "" && $queryInfo['yard'] == ""&&
            $queryInfo['couriernumber'] == "" && $queryInfo['freightmode'] == ""
            ) //都为空
            return 0;
        elseif (($queryInfo['startdate'] != "" || $queryInfo['enddate'] != ""||
            $queryInfo['departmentname'] != "" || $queryInfo['areamanager'] != ""
            )&& $queryInfo['order_id'] == ""&&
            $queryInfo['receiver_name'] == "" && $queryInfo['yard'] == ""&&
            $queryInfo['couriernumber'] == "" && $queryInfo['freightmode'] == ""
             ) //日期，部门，经理有一不为空，除订单状态外其他为空
            return 1;
        elseif ($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
                $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
                $queryInfo['order_id'] == ""&&
                $queryInfo['receiver_name'] != "" && $queryInfo['yard'] == ""&&
                $queryInfo['couriernumber'] == "" && $queryInfo['freightmode'] == ""
            )//收货人不为空，除订单状态外其他为空
            return 2;
        elseif ($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
                $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
                $queryInfo['order_id'] == ""&& $queryInfo['receiver_name'] == "" &&
                ($queryInfo['yard'] != "" || $queryInfo['couriernumber'] != "" )&&
                $queryInfo['freightmode'] == "") //货场，运单 有一不为空 ，除订单状态外其他为空
            return 3;
        elseif ($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
            $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
            $queryInfo['order_id'] == ""&& $queryInfo['receiver_name'] == "" &&
            $queryInfo['yard'] == "" && $queryInfo['couriernumber'] == "" &&
            $queryInfo['freightmode'] != "") //运费方式不为空 ，除订单状态外其他为空
            return 4;
        elseif($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
            $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
            $queryInfo['orderstate'] != "" && $queryInfo['order_id'] == ""&&
            $queryInfo['receiver_name'] == "" && $queryInfo['yard'] == ""&&
            $queryInfo['couriernumber'] == "" && $queryInfo['freightmode'] == ""
            ) //订单状态不为空 ,其他为空
            return 5;

        return -1;
    }

    /*导出订货确认单*/
    public function exportorderconfirmorder(){
        $template_name = "订货确认单导出表格.xlsx";
        $param = json_decode($_GET['param']);
        $file_name = $_GET['file_name'];
        $file_extend = 'xlsx';
        /*查询订货确认单*/
        $ret = \app\index\model\Admin::queryexportgoodsconfirmorder($param);
        /*导出订货确认单*/
        \app\index\model\Admin::exportgoodsconfirmorder($file_name,$file_extend,$template_name,$ret);
    }
}
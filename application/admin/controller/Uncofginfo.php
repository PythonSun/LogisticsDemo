<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/05/08
 * Time: 16:11
 */

namespace app\admin\controller;
use think\Controller;
use think\Input;

class Uncofginfo extends Controller
{
    public function uncofginfo(){
        $type = $_GET['type'];
        if(isset($_GET['cs_id'])){
            $cs_id = $_GET['cs_id'];
            $this->assign('cs_id',$cs_id);
        }else{
            $this->assign('cs_id',0);
        }

        if(isset($_GET['parenttype'])){
            $parenttype = $_GET['parenttype'];
            $this->assign('parenttype',$parenttype);
        }
        $user_session = session("user_session");
        $role_id = intval($user_session["role_id"]);
        $role_info = \app\index\model\Admin::queryroleinfo($role_id);
        $print_power = ($role_info[0]['order_goods_permission'])&0x08;
        $this->assign('print_power',$print_power);
        $login_user_id = $user_session['user_id'];
        $login_user_name = $user_session['fullname'];
        $date = date("Y-m-d");
        $this->assign('date',$date);
        $this->assign('type',$type);
        
        //$this->assign('user_id',$login_user_id);
        //$this->assign('user_name',$login_user_name);
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

    public function checkid(){
        $uoi_manual_ofg_id = $_POST['id'];
        $uoi_id = $_POST['uoi_id'];
        $unc_ofg_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.unc_ofg_info','uoi_manual_ofg_id',$uoi_manual_ofg_id);
        if (empty($unc_ofg_info)){
            return true;
        }else{
            if ($uoi_id == ""){
                return false;
            }
            $length = count($unc_ofg_info);
            for ($i = 0; $i < $length; $i++){
                if ($uoi_id != $unc_ofg_info[$i]['uoi_id']){
                    return false;
                }
            }
            return true;
        }
    }


    /*打印非常规订单确认单*/
    public function printuncofginfo(){
        $template_name = "非定型产品确认单.xls";
        $file_name = $_GET['file_name'];
        $file_extend = $_GET['file_extend'];
        $uoi_id = $_GET['uoi_id'];
        $ret = \app\index\model\Admin::queryprintuncofginfoorder($uoi_id);
        \app\index\model\Admin::printuncordergoods($file_name,$file_extend,$template_name,$ret);
    }
}
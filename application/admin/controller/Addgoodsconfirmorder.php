<?php
namespace app\admin\controller;
use think\Controller;
use think\Input;

class Addgoodsconfirmorder extends Controller
{
	/*新增订单渲染方法*/
    public function addgoodsconfirmorder(){
        $producttype = \app\index\model\Admin::getclassinfo('product_type','product_type_id');
        $brand = \app\index\model\Admin::getclassinfo('product_brand','brand_id');
        $place = \app\index\model\Admin::getclassinfo('product_place','place_id');
        if (!empty($brand)){
            $this->assign('producttypelist',$producttype);
        }
        if (!empty($brand)){
            $this->assign('brandlist',$brand);
        }
        if (!empty($place)){
            $this->assign('placelist',$place);
        }
        $date = date('Ymd');
        $this->assign("date", $date);
        $companytable = \app\index\model\Admin::querydepartmentinfo(0);
        if (!empty($companytable))
            $this->assign("companylist", $companytable);
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

                                                                                            //改
    public function addcs(){
        $user_session = session("user_session");
        $login_user_id = $user_session['user_id'];
        $date_now = date("Y-m-d H:i:s");
        $logistics_info = $_POST['logistics_info'];
        $order_goods_cs_info = $_POST['order_goods_cs_info'];
        $ofg_info = $_POST['ofg_info'];
        $fee_info = $_POST['fee_info'];
        $cs_belong = $_POST['cs_belong'];
        $order_goods_cs_undeliver_goods_info = null;
        $ogcugi_length = 0;
        if(array_key_exists('order_goods_cs_undeliver_goods_info',$_POST)){
            $order_goods_cs_undeliver_goods_info = $_POST['order_goods_cs_undeliver_goods_info'];
            $ogcugi_length = count($order_goods_cs_undeliver_goods_info);
        }
        $ofg_info_id = \app\index\model\Admin::getmaxtableidretid('ofg_info', 'ofg_info_id') + 1;
        $ofg_info['ofg_info_id'] = $ofg_info_id;
        $retofginfo = \app\index\model\Admin::updateofginfo($ofg_info);

        if (empty($retofginfo)){
            return "错误  52";
        }
        $fee_info_id = \app\index\model\Admin::getmaxtableidretid('fee_info', 'fee_info_id') + 1;
        $fee_info['fee_info_id'] = $fee_info_id;
        $retfeeinfo = \app\index\model\Admin::updatefeeinfo($fee_info);
        if (empty($retfeeinfo)){
            return "错误  57";
        }
        $order_goods_cs_info['ofg_info_id'] = $ofg_info_id;
        $order_goods_cs_info['fee_info_id'] = $fee_info_id;
        $cs_info_id = \app\index\model\Admin::getcsinfomaxid('order_goods_cs_info','cs_id');
        $order_goods_cs_info['cs_id'] = $cs_info_id;
        $logistics_id = \app\index\model\Admin::getmaxtableidretid('logistics_info', 'logistics_id') + 1;
        $logistics_info['logistics_id'] = $logistics_id;
        $logistics_info['user_id'] = $login_user_id;
        $logistics_info['cs_id'] = $cs_info_id;
        $ret_logistics =\app\index\model\Admin::updatelogisticsinfo($logistics_info);
        if (empty($ret_logistics)) {
            return false;
        }
        $ogcugi_id_arr =  array();
        if (!empty($order_goods_cs_undeliver_goods_info)){
            for ($i = 0; $i < $ogcugi_length; $i++){
                $ogcugi_id = \app\index\model\Admin::getmaxtableidretid('order_goods_cs_undeliver_goods_info', 'ogcugi_id') + 1;
                $order_goods_cs_undeliver_goods_info[$i]['ogcugi_id'] = $ogcugi_id;
                $order_goods_cs_undeliver_goods_info[$i]['cs_id'] = $cs_info_id;
                $retogcugi = \app\index\model\Admin::updateogcugi($order_goods_cs_undeliver_goods_info[$i]);
                if (empty($retogcugi)){
                    return "错误  70";
                }
                $ogcugi_id_arr[$i] = $ogcugi_id;
            }
        }
        $cs_belong['cs_id'] = $cs_info_id;
        $cs_belong['cs_belong_create_time'] = $date_now;

        $ret_cs_belog = \app\index\model\Admin::addcsbelong($cs_belong);
        if (empty($ret_cs_belog)) {
            return false;
        }

        //还有cs_belong
        $retcsinfo = \app\index\model\Admin::addordergoodscsinfo($order_goods_cs_info);

        if (empty($retcsinfo)){
            //删除相关添加的表  未完
            $cs_belog_id = \app\index\model\Admin::getcsinfomaxid('cs_belong','cs_belong_id');
            \app\index\model\Admin::deleterowtableid('ofg_info', 'ofg_info_id', $ofg_info_id);
            \app\index\model\Admin::deleterowtableid('fee_info', 'fee_info_id', $fee_info_id);

            \app\index\model\Admin::deleterowtableid('cs_belong', 'cs_belong_id', $cs_belog_id);
            $ogcugi_id = \app\index\model\Admin::getcsinfomaxid('ofg_iorder_goods_cs_undeliver_goods_infonfo','ogcugi_id');
            for ($i = 0; $i < $ogcugi_length; $i++){
                $delid = $ogcugi_id_arr[$i];
                \app\index\model\Admin::deleterowtableid('ofg_iorder_goods_cs_undeliver_goods_infonfo', 'ogcugi_id', $delid);
            }
            return "错误  404";
        }
        return true;
    }
}
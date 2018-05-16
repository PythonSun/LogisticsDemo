<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/05/08
 * Time: 17:17
 */

namespace app\admin\controller;
use think\Controller;

class Uncofginfoitem extends Controller
{
    public function uncofginfoitem()
    {
        $producttype = \app\index\model\Admin::getclassinfo('product_type','product_type_id');
        $brand = \app\index\model\Admin::getclassinfo('product_brand','brand_id');
        $unc_product_list= \app\index\model\Admin::getclassinfo('unc_product','unc_product_id');
        if (!empty($brand)){
            $this->assign('producttypelist',$producttype);
        }
        if (!empty($brand)){
            $this->assign('brandlist',$brand);
        }
        if (!empty($unc_product_list)){
            $this->assign('unc_product_list',$unc_product_list);
        }
        return $this->fetch();
    }

    public function serachmodelinfo()
    {
        $sereachText = $_POST['serrchText'];
        $productType = $_POST['type'];
        $brand = $_POST['brand'];
        $dbproductinfo = \app\index\model\Admin::serachmodelinfo($sereachText,$productType,$brand);
        return $dbproductinfo;
    }

    public function coldserachmodelinfo()
    {
        $sereachText = $_POST['serrchText'];
        $productType = $_POST['type'];
        $brand = $_POST['brand'];
        $dbproductinfo = \app\index\model\Admin::coldserachmodelinfo($sereachText,$productType,$brand);
        return $dbproductinfo;
    }

    public function serachproductinfo(){
        $productinfo = self::coldserachmodelinfo();
        if (!empty($productinfo)){
            $place_id = $productinfo[0]['place_id'];
            $placeinfo = \app\index\model\Admin::getproductplace($place_id);
            if(!empty($placeinfo)){
                $retinfo = new \stdClass();
                $retinfo->product_info = $productinfo[0];
                $retinfo->place_info = $placeinfo[0];
                return $retinfo;
            }
        }
        return null;
    }
}
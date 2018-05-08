<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/05/04
 * Time: 17:18
 */

namespace app\admin\controller;
use think\Controller;

class Addgoodsconfirmorderitem extends Controller
{
    public function addgoodsconfirmorderitem()
    {
        $producttype = \app\index\model\Admin::getclassinfo('product_type','product_type_id');
        $brand = \app\index\model\Admin::getclassinfo('product_brand','brand_id');
        if (!empty($brand)){
            $this->assign('producttypelist',$producttype);
        }
        if (!empty($brand)){
            $this->assign('brandlist',$brand);
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

    public function serachproductinfo(){
        $productinfo = self::serachmodelinfo();
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
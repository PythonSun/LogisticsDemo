<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/04/27
 * Time: 11:38
 */

namespace app\admin\controller;
use think\Controller;

class Addconfirmorderitem extends Controller
{
    public function addconfirmorderitem()
    {
        $type = $_GET['type'];
        $this->assign('type',$type);
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
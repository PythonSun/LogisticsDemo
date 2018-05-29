<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 10:17
 */

namespace app\admin\controller;


use think\Controller;

class Addoreditpartsgoodsitem extends Controller
{
    public function addoreditpartsgoodsitem()
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
        $dbproductinfo = \app\index\model\Admin::serachmodelinfo($sereachText);
        return $dbproductinfo;
    }

    public function coldserachmodelinfo()
    {
        $sereachText = $_POST['serrchText'];
        $dbproductinfo = \app\index\model\Admin::coldserachmodel($sereachText);
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
        return '';
    }

    public function addmodel(){
        if(!array_key_exists('model_info',$_POST))
        {
            return $_POST;
        }

        $model = $_POST['model_info'];
        $product_info_id = \app\index\model\Admin::getmaxtableidretid('product_info','product_info_id') + 1;
        $model['product_info_id'] = $product_info_id;
        $retsql = \app\index\model\Admin::addproductinfo($model);
        if($retsql == null)
            return  '';
        return $model;
    }
}
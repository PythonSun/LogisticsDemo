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
        return $this->fetch();
    }

    public function serachmodelinfo()
    {
        $sereachText = $_POST['serrchText'];
        $dbproductinfo = \app\index\model\Admin::serachmodelinfo($sereachText);
        return $dbproductinfo;
    }
    //弃用
    public function coldserachmodelinfo()
    {
        $sereachText = $_POST['serrchText'];
        $dbproductinfo = \app\index\model\Admin::coldserachmodelinfo($sereachText);
        return $dbproductinfo;
    }
    //弃用
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
        return "";
    }

    public function coldserachmodel(){
        $sereachText = $_POST['serrchText'];
        $productinfo = \app\index\model\Admin::coldserachmodel($sereachText);
        if (!empty($productinfo)){
            $retinfo = new \stdClass();
            $retinfo->product_info = $productinfo[0];
            $retinfo->product_type= "";
            $retinfo->product_brand= "";
            $retinfo->product_place= "";
            $product_type_id = $productinfo[0]['product_type_id'];
            $brand_id = $productinfo[0]['brand_id'];
            $place_id = $productinfo[0]['place_id'];

            $product_type = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.product_type','product_type_id',$product_type_id);
            $product_brand = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.product_brand','brand_id',$brand_id);
            $product_place = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.product_place','place_id',$place_id);
            if(!empty($product_type)){

                $retinfo->product_type = $product_type[0];
            }
            if(!empty($product_brand)){

                $retinfo->product_brand = $product_brand[0];
            }
            if(!empty($product_place)){

                $retinfo->product_place = $product_place[0];
            }
            return $retinfo;
        }
        return "";
    }

    public function addmodel(){
        $model = $_POST['model'];
        $product_type = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.product_info','model',$model);
        $count = count($product_type);
        if(!empty($product_type) && $count > 0){
            return $product_type[0]['product_info_id'];
        }
        $product_info_name = $_POST['product_info_name'];
        $product_type_id = $_POST['product_type_id'];
        $brand_id = $_POST['brand_id'];
        $place_id = $_POST['place_id'];
        $product_info_id = \app\index\model\Admin::getmaxtableidretid('product_info','product_info_id') + 1;
        $product_info = array();
        $product_info['product_info_id'] = $product_info_id;
        $product_info['model'] = strtoupper($model);
        $product_info['product_info_name'] = $product_info_name;
        $product_info['product_type_id'] = $product_type_id;
        $product_info['brand_id'] = $brand_id;
        $product_info['place_id'] = $place_id;

        $retsql = \app\index\model\Admin::addproductinfo($product_info);
        return $product_info_id;
    }
}
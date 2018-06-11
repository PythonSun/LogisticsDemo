<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/31
 * Time: 10:02
 */

namespace app\index\controller;


use think\Controller;

class Common extends Controller
{
    public function getserachmanarger()
    {
        $condition = array();
        if(array_key_exists('serachText',$_POST))
        {
            $condition['serachText'] = $_POST['serachText'];
        }
        if(array_key_exists('organize_id',$_POST))
        {
            $condition['organize_id'] = $_POST['organize_id'];
        }
        if(array_key_exists('role_name',$_POST))
        {
            $condition['role_name'] = $_POST['role_name'];
        }
        if(array_key_exists('organize_name',$_POST))
        {
            $condition['organize_name'] = $_POST['organize_name'];
        }
        $dbproductinfo = \app\index\model\Admin::serachuser($condition);
        return $dbproductinfo;
    }

    public function getserachdepartment()
    {
        $condition = array();
        if(array_key_exists('organize_name',$_POST))
        {
            $condition['name'] = $_POST['organize_name'];
        }
        $dbproductinfo = \app\index\model\Admin::serachdepartment($condition);
        return $dbproductinfo;
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

    public function judgeusernameexist()
    {
        if(array_key_exists('username',$_POST))
        {
            $condition['username'] = $_POST['username'];
            return \app\index\model\Admin::serachusername($_POST['username']);
        }
        return true;
    }
}
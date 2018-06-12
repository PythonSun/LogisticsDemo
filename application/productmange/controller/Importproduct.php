<?php
namespace app\productmange\controller;
use think\Exception;
use think\Request;
use \think\File;
use think\Controller;
use think\Input;
use PHPExcel_IOFactory;
use PHPExcel;


class Importproduct extends Controller
{
	/*产品导入页面渲染方法*/
    public function importproduct(){
        $userinfo = \app\index\model\Admin::getsessioninfo();
        $this->assign('loginusername',$userinfo["fullname"]);
    	return $this->fetch();
    }

    /*文件上传*/
    public function uploadproductlist(){
        $modelinfo = "";
        $num = 0;
        $import_error = array();
        $file = request()->file('file');
        $info = $file->move(ROOT_PATH . 'public' . DS . 'cachefile');
        $pathname = $info->getPathname();
        $pathname = iconv("utf-8","gb2312",$pathname);
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($pathname);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        /*将当前数据信息get出来*/
        for($j=3;$j<=$highestRow;$j++){
            $a = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();//获取A(产品序号)列的值
            $b = (string)$objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();//获取B(产品名称)列的值
            $c = (string)$objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue();//获取C(产品型号)列的值
            $d = $objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue();//获取D(品牌)列的值
            $e = $objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();//获取E(类别)列的值
            $f = $objPHPExcel->getActiveSheet()->getCell("F".$j)->getValue();//获取F(生产中心)列的值

            if(($c == null)||($c == '')){
                $import_error[] = "序号为".$a."的".$b."产品型号为空，添加失败";
            }else{
                /*检测产品型号是否存在*/
                $ret = \app\index\model\Admin::detectmodelisexist($c);
                if($ret ==''){
                    $model = $c;
                    $product_info_name = $b;
                    $product_type_id= \app\index\model\Admin::producttype($e);
                    $brand_id = \app\index\model\Admin::productbrand($d);
                    $place_id = \app\index\model\Admin::productplace($f);
                    $num++;
                    if($num == 1){
                        $modelinfo .= "('".$model."','".$product_info_name."',".$product_type_id.",".$brand_id.",".$place_id.")";
                    }else{
                        $modelinfo .= ",('".$model."','".$product_info_name."',".$product_type_id.",".$brand_id.",".$place_id.")";
                    }
                    if($num == 30){
                        $return = \app\index\model\Admin::insertproductinfo($modelinfo);
                        $num = 0 ;
                        $modelinfo = "";
                    }
                }else{
                    $import_error[] = "序号为".$a."的".$b."产品型号已经存在，请勿重新添加";
                }
            }
        }
        if(!empty($import_error)){
            return json($failinfo = array('code' =>0 ,'error'=>$import_error));
        }else{
            return json($successinfo = array('code' =>200 ,'msg'=>'导入产品型号信息成功' ));
        }
    }

    public function logout(){
        $ret = \app\index\model\Admin::logout();
        return $ret;
    }

    /*获取登录session*/
    public function getsession(){
        $ret = \app\index\model\Admin::getsessioninfo();
        return $ret;
    }
}

<?php
namespace app\admin\controller;
use think\Controller;
use think\Input;

class Exportbigdata extends Controller
{
        public function exportbigdata()
        {
                $productPlace = \app\index\model\Admin::getclassinfo('dsp_logistic.product_place','place_id');
                $this->assign('placelist',$productPlace);

                $productBrand = \app\index\model\Admin::getclassinfo('dsp_logistic.product_brand','brand_id');
                $this->assign('brandlist',$productBrand);
	        return $this->fetch();
        }

        public function exportcsinfo()
        {
                $template_name = "大数据.xlsx";
                $param = json_decode($_GET['param']);
                $file_name = $_GET['file_name'];
                $file_extend = 'xlsx';
                $ret = \app\index\model\Admin::queryexportcsinfo($param);
                \app\index\model\Admin::exportcsinfo($file_name,$file_extend,$template_name,$ret);
        }
}
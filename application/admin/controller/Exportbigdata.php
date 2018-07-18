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

                /*获取权限设置*/
                $userinfo = \app\index\model\Admin::getsessioninfo();
                $role_id = intval($userinfo["role_id"]);
                $role_info = \app\index\model\Admin::queryroleinfo($role_id);
                $presalepower = ($role_info[0]['big_data_permission_manage'])&0x01;
                $aftersalepower = ($role_info[0]['big_data_permission_manage'])&0x02;

                $this->assign('presalepower',$presalepower);
                $this->assign('aftersalepower',$aftersalepower);
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

        public function exportordergoodsinfo()
        {
                $template_name = "缺货型号导出.xlsx";
                $param = json_decode($_GET['param']);
                $file_name = $_GET['file_name'];
                $file_extend = 'xlsx';
                $ret = \app\index\model\Admin::queryexportordergoodsinfo($param);
                \app\index\model\Admin::exportoutofstackinfo($file_name,$file_extend,$template_name,$ret);
        }
}
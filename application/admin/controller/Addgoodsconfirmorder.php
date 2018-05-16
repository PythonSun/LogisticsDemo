<?php
namespace app\admin\controller;
use think\Request;
use \think\File;
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
        $date = date('Y-m-d');
        $this->assign("date", $date);
        $companytable = \app\index\model\Admin::querydepartmentinfo(0);
        if (!empty($companytable))
            $this->assign("companylist", $companytable);
        $order_info = '-1';
        $this->assign("order_info", $order_info);
        $this->assign('type',1);
    	return $this->fetch();
    }

    public function editgoodsconfirmorder(){
        $type = $_GET['type'];

        $cs_id = $_GET['cs_id'];
        $cs_belong_id = $_GET['cs_belong_id'];
        $fee_info_id = $_GET['fee_info_id'];
        $ofg_info_id = $_GET['ofg_info_id'];
        $unc_ofg_info_id = $_GET['unc_ofg_info_id'];
        $order_info = self::getorderinfo($cs_id,$cs_belong_id,$fee_info_id,$ofg_info_id,$unc_ofg_info_id);

        if (!empty($order_info)){
            $str = json_encode($order_info);
            $this->assign("order_info", $str);
        }else{
            $this->assign("order_info", "");
        }

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
        $date = date('Y-m-d');
        $this->assign("date", $date);
        $companytable = \app\index\model\Admin::querydepartmentinfo(0);
        if (!empty($companytable))
            $this->assign("companylist", $companytable);
        $this->assign('type',$type);
        return $this->fetch('addgoodsconfirmorder');
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

    public function addcs(){
        $user_session = session("user_session");
        $login_user_id = $user_session['user_id'];
        $date_now = date("Y-m-d H:i:s");
        $logistics_info = $_POST['logistics_info'];
        $order_goods_cs_info = $_POST['order_goods_cs_info'];
        $ofg_info = $_POST['ofg_info'];
        $fee_info = $_POST['fee_info'];
        $cs_belong = $_POST['cs_belong'];
        $unc_ofg_info = $_POST['unc_ofg_info'];
        $unc_ofg_detail = $_POST['unc_ofg_detail'];
        $order_goods_cs_undeliver_goods_info = null;
        $cs_info_id = \app\index\model\Admin::getcsinfomaxid('cs_belong','cs_id');

        $order_goods_cs_info['unc_ofg_info_id'] = -1;
        $order_goods_cs_info['ofg_info_id'] = -1;
        $order_goods_cs_info['fee_info_id'] = -1;

        $order_goods_cs_info['cs_id'] = $cs_info_id;
        $retcsinfo = \app\index\model\Admin::updateordergoodscsinfo($order_goods_cs_info);
        $cs_belog_id = \app\index\model\Admin::getmaxtableidretid('cs_belong','cs_belong_id') + 1;
        $cs_belong['cs_belong_id'] = $cs_belog_id;
        $cs_belong['cs_id'] = $cs_info_id;
        $cs_belong['cs_belong_create_time'] = $date_now;
        $cs_belong['build_user_phone'] = $user_session['phone'];
        $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);
        if (empty($ret_cs_belog)) {
            return false;
        }
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
        $uoi_id = -1;
        if (!empty($unc_ofg_info)){
            $uoi_id = \app\index\model\Admin::getmaxtableidretid('unc_ofg_info', 'uoi_id') + 1;
            $unc_ofg_info['uoi_date'] = $date_now;
            $unc_ofg_info['uoi_id'] = $uoi_id;
            $retfee_info = \app\index\model\Admin::updateunc_ofg_info($unc_ofg_info);
            if (empty($retfee_info)){
                return "错误  80";
            }

            /*$cs_belog_id = \app\index\model\Admin::getmaxtableidretid('cs_belong','cs_belong_id') + 1;
            $cs_belong['cs_belong_id'] = $cs_belog_id;
            $cs_belong['cs_id'] = $unc_ofg_info['uoi_manual_ofg_id'];
            $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong);
            if (empty($ret_cs_belog)) {
                return false;
            }*/
        }

        $uod_id_arr =  array();
        if(!empty($unc_ofg_info) && !empty($unc_ofg_detail)){
            $unc_ofg_detail_length = count($unc_ofg_detail);
            for($i = 0; $i < $unc_ofg_detail_length; $i++){
                $uod_id = \app\index\model\Admin::getmaxtableidretid('unc_ofg_detail', 'uod_id') + 1;
                $unc_ofg_detail[$i]['uod_id'] = $uod_id;
                $uod_id_arr[$i] = $uod_id;
                $unc_ofg_detail[$i]['unc_ofg_info_id'] = $uoi_id;
                $retunc_ofg_detail = \app\index\model\Admin::updateunc_ofg_detail($unc_ofg_detail[$i]);
                if (empty($retunc_ofg_detail)){
                    return "错误  92";
                }
            }
        }

        $logistics_info_count = count($logistics_info);
        $logistics_id_arr =  array();
        for ($i = 0; $i < $logistics_info_count; $i++){
            $logistics_id = \app\index\model\Admin::getmaxtableidretid('logistics_info', 'logistics_id') + 1;
            $logistics_id_arr[$i] = $logistics_id;
            $logistics_info[$i]['logistics_id'] = $logistics_id;
            $logistics_info[$i]['user_id'] = $login_user_id;
            $logistics_info[$i]['cs_id'] = $cs_info_id;
            $ret_logistics =\app\index\model\Admin::updatelogisticsinfo($logistics_info[$i]);       //c错误
            if (empty($ret_logistics)) {
                return false;
            }
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


        $order_goods_cs_info['unc_ofg_info_id'] = $uoi_id;
        $order_goods_cs_info['ofg_info_id'] = $ofg_info_id;
        $order_goods_cs_info['fee_info_id'] = $fee_info_id;

        //$order_goods_cs_info['cs_id'] = $cs_info_id;
        $retcsinfo = \app\index\model\Admin::updateordergoodscsinfo($order_goods_cs_info);
        /*if (empty($retcsinfo)){
            //删除相关添加的表  未完

            \app\index\model\Admin::deleterowtableid('ofg_info', 'ofg_info_id', $ofg_info_id);
            \app\index\model\Admin::deleterowtableid('fee_info', 'fee_info_id', $fee_info_id);

            \app\index\model\Admin::deleterowtableid('cs_belong', 'cs_belong_id', $cs_belog_id);

            \app\index\model\Admin::deleterowtableid('unc_ofg_info', 'uoi_id', $uoi_id);

            $logistics_id_arr_length = count($logistics_id_arr);
            for ($i = 0; $i < $logistics_id_arr_length; $i++){
                $delid = $logistics_id_arr[$i];
                \app\index\model\Admin::deleterowtableid('logistics_info', 'logistics_id', $delid);
            }
            $uod_id_arr_length = count($uod_id_arr);
            for ($i = 0; $i < $uod_id_arr_length; $i++){
                $delid = $uod_id_arr_length[$i];
                \app\index\model\Admin::deleterowtableid('unc_ofg_detail', 'uod_id', $delid);
            }

            for ($i = 0; $i < $ogcugi_length; $i++){
                $delid = $ogcugi_id_arr[$i];
                \app\index\model\Admin::deleterowtableid('ofg_iorder_goods_cs_undeliver_goods_infonfo', 'ogcugi_id', $delid);
            }
            return "错误  404";
        }*/


        $file_new = $order_goods_cs_info['consult_sheet_file'];
        //$file_old = $order_goods_cs_info['consult_sheet_file_old'];
        if (!empty($file_new)){
            //文件移动
            $cachefilePath = ROOT_PATH . 'public' . DS . 'cachefile'.DS.$file_new;
            $filePath = ROOT_PATH . 'public' . DS . 'uploads'.DS.$file_new;
            rename($cachefilePath,$filePath);
        }
        return true;
    }

    public function editcs(){
        $res=[
            'code'=>1,
            'msg'=>'保存成功！',
        ];

        $user_session = session("user_session");
        $login_user_id = $user_session['user_id'];
        $date_now = date("Y-m-d H:i:s");
        $logistics_info = $_POST['logistics_info'];
        $order_goods_cs_info = $_POST['order_goods_cs_info'];
        $ofg_info = $_POST['ofg_info'];
        $fee_info = $_POST['fee_info'];
        $cs_belong = $_POST['cs_belong'];
        $unc_ofg_info = $_POST['unc_ofg_info'];
        $unc_ofg_detail = $_POST['unc_ofg_detail'];
        $del_logistics_id_arr = null;
        $del_unc_ofg_detail_id_arr = null;
        $del_ogcugi_id_arr = null;
        if(array_key_exists('del_logistics_id_arr',$_POST)){
            $del_logistics_id_arr = $_POST['del_logistics_id_arr'];
        }
        if(array_key_exists('del_unc_ofg_detail_id_arr',$_POST)){
            $del_unc_ofg_detail_id_arr = $_POST['del_unc_ofg_detail_id_arr'];
        }
        if(array_key_exists('del_ogcugi_id_arr',$_POST)){
            $del_ogcugi_id_arr = $_POST['del_ogcugi_id_arr'];
        }

        $order_goods_cs_undeliver_goods_info = null;

        //$order_goods_cs_info['unc_ofg_info_id'] = -1;                           /*******是否新增******/
        $cs_info_id = $order_goods_cs_info['cs_id'];

        $file_new = $order_goods_cs_info['consult_sheet_file'];
        $file_old = $order_goods_cs_info['consult_sheet_file_old'];
        if (!empty($file_old) && empty($file_new)){//没有新文件上传
            $order_goods_cs_info['consult_sheet_file'] = $file_old;
        }

        $retcsinfo = \app\index\model\Admin::updateordergoodscsinfo($order_goods_cs_info);

        $cs_belong_old = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.cs_belong','cs_belong_id',$cs_belong['cs_belong_id']);
        if (!empty($cs_belong_old)){
            if ($cs_belong_old[0]['build_user_id'] != $cs_belong['build_user_id']){
                $user_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.user','user_id',$cs_belong['build_user_id']);
                if (!empty($user_info)){
                    $cs_belong_old[0]['build_user_id'] = $user_info[0]['user_id'];
                    $cs_belong_old[0]['build_user_name'] = $user_info[0]['fullname'];
                    $cs_belong_old[0]['build_organize_id'] = $cs_belong['build_organize_id'];
                    $cs_belong_old[0]['build_organize_name'] = $cs_belong['build_organize_name'];
                    $cs_belong_old[0]['build_department_id'] = $cs_belong['build_department_id'];
                    $cs_belong_old[0]['build_department_name'] = $cs_belong['build_department_name'];
                    $ret_cs_belog = \app\index\model\Admin::updatecsbelong($cs_belong_old[0]);
                    if (empty($ret_cs_belog)) {
                        $res=[
                            'code'=> 0,
                            'msg'=>'数据提交失败！错误代码：1295',
                        ];
                        return json($res);
                    }
                }
            }
        }


        $ogcugi_length = 0;
        if(array_key_exists('order_goods_cs_undeliver_goods_info',$_POST)){
            $order_goods_cs_undeliver_goods_info = $_POST['order_goods_cs_undeliver_goods_info'];
            $ogcugi_length = count($order_goods_cs_undeliver_goods_info);
        }
        $retofginfo = \app\index\model\Admin::updateofginfo($ofg_info);

        /*if (empty($retofginfo)){
            return "错误  52";
        }*/
        $retfeeinfo = \app\index\model\Admin::updatefeeinfo($fee_info);
        /*if (empty($retfeeinfo)){
            return "错误  57";
        }*/
        $uoi_id = "";
        if (!empty($unc_ofg_info)){
            /*******是否新增******/
            $uoi_id = $unc_ofg_info['uoi_id'];
            if ($uoi_id == ""){
                $uoi_id = \app\index\model\Admin::getmaxtableidretid('unc_ofg_info', 'uoi_id') + 1;
                $unc_ofg_info['uoi_date'] = $date_now;
                $unc_ofg_info['uoi_id'] = $uoi_id;
            }else{
                $uoi_id = "";
            }
            $retfee_info = \app\index\model\Admin::updateunc_ofg_info($unc_ofg_info);
            /*if (empty($retfee_info)){
                return "错误  80";
            }*/
        }

        if(!empty($unc_ofg_info) && !empty($unc_ofg_detail)){
            $unc_ofg_detail_length = count($unc_ofg_detail);
            for($i = 0; $i < $unc_ofg_detail_length; $i++){
                $uod_id = $unc_ofg_detail[$i]['uod_id'];
                if ($uod_id == ""){
                    $uod_id = \app\index\model\Admin::getmaxtableidretid('unc_ofg_detail', 'uod_id') + 1;
                    $unc_ofg_detail[$i]['uod_id'] = $uod_id;
                }
                //$unc_ofg_detail[$i]['unc_ofg_info_id'] = $uoi_id;
                $retunc_ofg_detail = \app\index\model\Admin::updateunc_ofg_detail($unc_ofg_detail[$i]);
                /*if (empty($retunc_ofg_detail)){
                    return "错误  92";
                }*/
            }
        }

        $logistics_info_count = count($logistics_info);
        $logistics_id_arr =  array();
        for ($i = 0; $i < $logistics_info_count; $i++){
            $logistics_id = $logistics_info[$i]['logistics_id'];
            if (empty($logistics_id)){
                $logistics_id = \app\index\model\Admin::getmaxtableidretid('logistics_info', 'logistics_id') + 1;
                $logistics_id_arr[$i] = $logistics_id;
                $logistics_info[$i]['logistics_id'] = $logistics_id;
                $logistics_info[$i]['user_id'] = $login_user_id;
                $logistics_info[$i]['cs_id'] = $cs_info_id;
            }

            $ret_logistics =\app\index\model\Admin::updatelogisticsinfo($logistics_info[$i]);
            /*if (empty($ret_logistics)) {
                return false;
            }*/
        }
        if (!empty($order_goods_cs_undeliver_goods_info)){
            for ($i = 0; $i < $ogcugi_length; $i++){
                $ogcugi_id = $order_goods_cs_undeliver_goods_info[$i]['ogcugi_id'];
                if (empty($ogcugi_id)){
                    $ogcugi_id = \app\index\model\Admin::getmaxtableidretid('order_goods_cs_undeliver_goods_info', 'ogcugi_id') + 1;
                    $order_goods_cs_undeliver_goods_info[$i]['ogcugi_id'] = $ogcugi_id;
                    $order_goods_cs_undeliver_goods_info[$i]['cs_id'] = $cs_info_id;
                }

                $retogcugi = \app\index\model\Admin::updateogcugi($order_goods_cs_undeliver_goods_info[$i]);
                /*if (empty($retogcugi)){
                    return "错误  70";
                }*/
            }
        }

        if (!empty($uoi_id)){
            $order_goods_cs_info['unc_ofg_info_id'] = $uoi_id;
            $retcsinfo = \app\index\model\Admin::updateordergoodscsinfo($order_goods_cs_info);
        }
        if (!empty($del_logistics_id_arr)){
            $del_length = count($del_logistics_id_arr);
            for ($i = 0; $i < $del_length; $i++){
                \app\index\model\Admin::deleterowtableid('logistics_info','logistics_id',$del_logistics_id_arr[$i]);
            }
        }
        if (!empty($del_unc_ofg_detail_id_arr)){
            $del_length = count($del_unc_ofg_detail_id_arr);
            for ($i = 0; $i < $del_length; $i++){
                \app\index\model\Admin::deleterowtableid('unc_ofg_detail','uod_id',$del_unc_ofg_detail_id_arr[$i]);
            }
        }
        if (!empty($del_ogcugi_id_arr)){
            $del_length = count($del_ogcugi_id_arr);
            for ($i = 0; $i < $del_length; $i++){
                \app\index\model\Admin::deleterowtableid('order_goods_cs_undeliver_goods_info','ogcugi_id',$del_ogcugi_id_arr[$i]);
            }
        }
        //$order_goods_cs_info['cs_id'] = $cs_info_id;


        $cachefilePath = ROOT_PATH . 'public' . DS . 'cachefile'.DS.$file_new;
        $filePath = ROOT_PATH . 'public' . DS . 'uploads'.DS.$file_new;
        $filePath_old = ROOT_PATH . 'public' . DS . 'uploads'.DS.$file_old;

        if (!empty($file_old) && empty($file_new)){//没有新文件上传
        }else if (empty($file_old) && !empty($file_new)){//只有新文件
            if (file_exists($cachefilePath)){
                rename($cachefilePath,$filePath);
                /*if (unlink($cachefilePath)){
                }else{
                }*/
            }
        }else if (!empty($file_old) && !empty($file_new)){//新旧都有
            if (file_exists($cachefilePath)){
                rename($cachefilePath,$filePath);
                /*if (unlink($cachefilePath)){
                }else{
                }*/
            }
            if (file_exists($filePath_old)){
                if (unlink($filePath_old)){
                }else{
                }
            }
        }

        return json($res);
    }

    public function getorderinfo($cs_id,$cs_belong_id,$fee_info_id,$ofg_info_id,$unc_ofg_info_id){
        /*$cs_id = $_POST['cs_id'];
        $cs_belong_id = $_POST['cs_belong_id'];
        $fee_info_id = $_POST['fee_info_id'];
        $ofg_info_id = $_POST['ofg_info_id'];
        $unc_ofg_info_id = $_POST['unc_ofg_info_id'];*/
        $ret_info = array();
        $cs_belong = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.cs_belong','cs_belong_id',$cs_belong_id);
        $ret_info['cs_belong'] = "";
        if(!empty($cs_belong)){
            $ret_info['cs_belong'] = $cs_belong[0];
        }
        $cs_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.order_goods_cs_info','cs_id',$cs_id);
        if (empty($cs_info)){
            return null;
        }
        $ret_info['cs_info'] = $cs_info[0];
        $ret_info['ofg_info'] = "";
        if ($ofg_info_id >= 1){
            $ofg_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.ofg_info','ofg_info_id',$ofg_info_id);
            if (!empty($ofg_info)){
                $ret_info['ofg_info'] = $ofg_info[0];
            }
        }
        $ret_info['fee_info'] = "";
        if ($fee_info_id >= 1){
            $fee_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.fee_info','fee_info_id',$fee_info_id);
            if (!empty($fee_info)){
                $ret_info['fee_info'] = $fee_info[0];
            }
        }
        $ret_info['unc_ofg_info'] = "";
        $ret_info['unc_ofg_detail'] = "";
        if ($unc_ofg_info_id >= 1){
            $unc_ofg_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.unc_ofg_info','uoi_id',$unc_ofg_info_id);
            if (!empty($fee_info)){
                $ret_info['unc_ofg_info'] = $unc_ofg_info[0];
                $unc_ofg_detail = \app\index\model\Admin::getuncofgdetailbyid($unc_ofg_info_id);
                if (!empty($unc_ofg_detail)){
                    $ret_info['unc_ofg_detail'] = $unc_ofg_detail;
                }
            }
        }

        $ret_info['ogcugi'] = "";
        $ogcugi = \app\index\model\Admin::getorderogcugibyid($cs_id);
        if (!empty($ogcugi)){
            $ret_info['ogcugi'] = $ogcugi;
        }
        $ret_info['logistic_info'] = "";
        $logistric_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.logistics_info','cs_id',$cs_id);
        if(!empty($logistric_info)){
            $ret_info['logistic_info'] = $logistric_info;
        }
        return $ret_info;
    }

    /*上传咨询表*/
    public function uploadconsultform(){
        try{
            $delFileName = '';
            if (array_key_exists('delFileName',$_GET)){
                $delFileName = $_GET['delFileName'];
            }

            $guid = self::create_guid();
            $file = request()->file('file');
            //$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            $info = $file->move(ROOT_PATH . 'public' . DS . 'cachefile',"$guid",true);
            $name = $info->getFilename();
            $pathname = $info->getPathname();
            if($info){
                $delFileNameMsg = '';
                if (!empty($delFileName)){
                    $filePath = ROOT_PATH . 'public' . DS . 'cachefile'.DS.$delFileName;
                    if (file_exists($filePath)){
                        if (unlink($filePath)){
                            $delFileNameMsg = '文件删除成功！';
                        }else{
                            $delFileNameMsg = '文件删除失败！';
                        }
                    }
                }
                $res=[
                    'code'=>'1',
                    'msg'=>'上传成功',
                    'fileName'=>$name,
                    'pathName'=>$pathname,
                    'delFileNameMsg'=>$delFileNameMsg
                ];
                return json($res);
            }else{
                $res=[
                    'code'=>'0',
                    'msg'=>'上传失败',
                ];
                return json($res);
            }
        }
        catch (Exception $e){
            $msg = $e->getMessage();
            $res=[
                'code'=>'0',
                'msg'=>$msg,
            ];
            return json($res);
        }
    }

    function test(){
        $cachefilePath = '/uploads'.DS.'1.png';
        return $cachefilePath;
    }

    /* 创建GUID */
    function create_guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = '';//chr(45);// "-"
        $uuid = //chr(123).// "{"
            substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
            //.chr(125);// "}"
        return $uuid;
    }
}
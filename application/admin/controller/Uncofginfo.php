<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/05/08
 * Time: 16:11
 */

namespace app\admin\controller;
use think\Controller;
use think\Input;

class Uncofginfo extends Controller
{
    public function uncofginfo(){
        $user_session = session("user_session");
        $login_user_id = $user_session['user_id'];
        $login_user_name = $user_session['fullname'];
        $date = date("Y-m-d");
        $this->assign('date',$date);
        //$this->assign('user_id',$login_user_id);
        //$this->assign('user_name',$login_user_name);
        return $this->fetch();
    }
    public function checkid(){
        $uoi_manual_ofg_id = $_POST['id'];
        $uoi_id = $_POST['uoi_id'];
        $unc_ofg_info = \app\index\model\Admin::getclassinfobyproperty('dsp_logistic.unc_ofg_info','uoi_manual_ofg_id',$uoi_manual_ofg_id);
        if (empty($unc_ofg_info)){
            return true;
        }else{
            if ($uoi_id == ""){
                return false;
            }
            $length = count($unc_ofg_info);
            for ($i = 0; $i < $length; $i++){
                if ($uoi_id != $unc_ofg_info[$i]['uoi_id']){
                    return false;
                }
            }
            return true;
        }
    }
}
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
        $date = date("Ymd");
        $this->assign('date',$date);
        $this->assign('user_id',$login_user_id);
        $this->assign('user_name',$login_user_name);
        return $this->fetch();
    }
}
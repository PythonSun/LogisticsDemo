<?php

namespace app\UserManage\controller;
use think\Controller;
use think\Request;

class Resetpersonalinfo extends Controller
{
    public function resetpersonalinfo(){
        return $this->fetch();
    }

    public function updatepersonalinfo(){
    	if(isset($_POST['param'])){
    		$param = $_POST['param'];
    		$ret = \app\index\model\Admin::updatepersonalinfo($param);
    		return $ret;
    	}else{
    		return '';
    	}
    }
}
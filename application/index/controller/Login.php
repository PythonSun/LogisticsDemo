<?php
namespace app\index\controller;
use think\Controller;

class Login extends Controller
{
	/*登录页渲染方法*/
    public function login(){
    	return $this->fetch();
    }

    /*登录验证*/
    public function  loginconfirm(){
    	$username = $_POST['username'];
    	$password = $_POST['password'];

    	$ret = \app\index\model\Admin::login($username,$password);
    	return $ret;
    }
}

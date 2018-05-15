<?php
namespace app\admin\controller;
use think\Controller;

class Inputlogisticsorder extends Controller
{
	/*输入物流单号渲染方法*/
    public function inputlogisticsorder(){
    	return $this->fetch();
    }

    /*发送短信*/
    public function sendmessage(){
    	$params = array();
		$AppKey= "57019656a4ddd2eeae8209e92c133c3e";
		$AppSecret = "2dc9af4fb762";
		$templateid = 3064651;

		$params[] = '孙乐苏';    /*收件人*/
		$params[] = '广东';      /*货场*/
		$params[] = '201800001'; /*物流单号*/
		$params[] = '1000';      /*件数*/
		$telephone = '13527792506'; /*电话号码，支持群发*/
    	$p = new \Serverapi($AppKey,$AppSecret,'curl');
    	$sendstate = $p->sendSMSTemplate($templateid,array($telephone),json_encode($params));
    	return $sendstate;           /*返回发送结果*/
    }
}
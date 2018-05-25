<?php
namespace app\admin\controller;
use think\Controller;
class Inputlogisticsorder extends Controller {
	/*输入物流单号渲染方法*/
	public function inputlogisticsorder() {
		return $this -> fetch();
	}

	/*保存信息*/
	public function savemessage() {
		if (array_key_exists('param', $_POST)) {
			$logistic_info = $_POST['param'];
			$id = \app\index\model\Admin::getmaxtableidretid('logistics_info', 'logistics_id');
			$item['logistics_id'] = $id + 1;
			$item['cs_id'] = $logistic_info['cs_id'];
			$item['goods_yard_name'] = $logistic_info['goods_yard_name'];
			$item['transfer_order_num'] = $logistic_info['transfer_order_num'];
			$item['delivery_date'] = $logistic_info['delivery_date'];
			$item['count'] = $logistic_info['count'];
			$res = \app\index\model\Admin::insertlogisticinfo($item);
			print_r($res);
		}
	}

	/*获取数据库信息*/
	public function loadingdata() {
		$type = 0x01;
		$page = $_GET['page'];
		$limit = $_GET['limit'];
		$tablelist = \app\index\model\Admin::querylogisticsinfo($type, $page, $limit);
		return $tablelist;
	}
	
	/*删除数据*/
	public function deletedata() {
		$logistics_id = $_GET['logistics_id'];
		$dellogisticsrowdata = \app\index\model\Admin::deleterowtableid('logistics_info', 'logistics_id', $logistics_id);
		return $dellogisticsrowdata;
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
    	return $sendstate;           
    }


}

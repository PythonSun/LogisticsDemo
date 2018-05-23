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
			$item['logistics_id'] = $id+1;
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
		$tablelist = \app\index\model\Admin::querylogisticsinfo($type,$page,$limit);
		return $tablelist;
	}

}

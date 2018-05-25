<?php
namespace app\admin\controller;
use think\Controller;
header("Content-Type:text/html; charset=utf-8");
class editlogistics extends Controller {
	public function editlogistics() {
		$logistics_id = $_GET['logistics_id'];
		$cs_id = $_GET['cs_id'];
		$goods_yard_name = $_GET['goods_yard_name'];
		$transfer_order_num = $_GET['transfer_order_num'];
		$delivery_date = $_GET['delivery_date'];
		$count = $_GET['count'];
		$time_stamp = $_GET['time_stamp'];
		
		$this -> assign('logistics_id', $logistics_id);
		$this -> assign('cs_id', $cs_id);
		$this -> assign('goods_yard_name', $goods_yard_name);
		$this -> assign('transfer_order_num', $transfer_order_num);
		$this -> assign('delivery_date', $delivery_date);
		$this -> assign('count', $count);
		$this -> assign('time_stamp', $time_stamp);
		
		return $this -> fetch();
	}
	public function editsave() {
		$parse['logistics_id'] = $_GET['logistics_id'];
		$parse['cs_id'] = $_GET['cs_id'];
		$parse['goods_yard_name'] = $_GET['goods_yard_name'];
		$parse['transfer_order_num'] = $_GET['transfer_order_num'];
		$parse['delivery_date'] = $_GET['delivery_date'];
		$parse['count'] = $_GET['count'];
		$parse['user_id'] = '1';
        $parse['time_stamp'] = $_GET['time_stamp'];
		$ret = \app\index\model\Admin::updatelogisticsinfo($parse);
		return $ret;
	}
	
	
}
?>
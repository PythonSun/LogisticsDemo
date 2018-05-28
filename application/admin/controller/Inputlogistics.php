<?php
namespace app\admin\controller;
use think\Controller, \think\View;
header("Content-Type:text/html; charset=utf-8");
class inputlogistics extends Controller {
	public function inputlogistics() {
		$cs_id = $_GET['cs_id'];
		$goods_yard_name = $_GET['goods_yard_name'];
		$transfer_order_num = $_GET['transfer_order_num'];
		$delivery_date = $_GET['delivery_date'];
		$count = $_GET['count'];
		$this -> assign('disabled', 'disabled');
		$this -> assign('layuidisabled', 'layui-disabled');
		$this -> assign('cs_id', $cs_id);
		$this -> assign('goods_yard_name', $goods_yard_name);
		$this -> assign('transfer_order_num', $transfer_order_num);
		$this -> assign('delivery_date', $delivery_date);
		$this -> assign('count', $count);
		return $this -> fetch();
	}

}
?>
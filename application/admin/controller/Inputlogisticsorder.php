<?php
namespace app\admin\controller;
use think\Controller;
use think\Exception;

class Inputlogisticsorder extends Controller {
	/*输入物流单号渲染方法*/
	public function inputlogisticsorder() {
        //phpinfo();
		return $this -> fetch();
	}

	/*触发流水号事件*/
	public function serialnumber() {
		$cs_id = $_POST['cs_id'];
		$result = \app\index\model\Admin::getreceiverbycsid($cs_id);
		return $result;
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
			return $res;
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
	public function sendmessage() {
		$AppKey = "57019656a4ddd2eeae8209e92c133c3e";
		$AppSecret = "2dc9af4fb762";
		$templateid = 3064651;
		$cs_id = $_POST['cs_id'];
		$goods_yard_name = $_POST['goods_yard_name'];
        $transfer_order_num = $_POST['transfer_order_num'];
        $count = $_POST['count'];
        $logistics_id = $_POST['logistics_id'];
		$result = \app\index\model\Admin::getreceiverbycsid($cs_id);
		if (!empty($result)){
            $params = array();
            /*收件人*/
            $params[] = $result['receiver_name'];
            /*货场*/
            $params[] = $goods_yard_name;
            /*物流单号*/
            $params[] = $transfer_order_num;
            /*件数*/
            $params[] = $count;
            /*电话号码，支持群发*/
            $telephone = $result['receiver_phone'];
            $p = new \Serverapi($AppKey, $AppSecret, 'curl');
            $sendstate = $p->sendSMSTemplate($templateid, array($telephone), json_encode($params));
            if (!empty($sendstate) && $sendstate['code'] == 200){
                $logistric_info = \app\index\model\Admin::getclassinfobyproperty('logistics_info','logistics_id',$logistics_id);
                if (!empty($logistric_info)){
                    $updataInfo = $logistric_info[0];
                    $updataInfo['user_id'] = -1;
                    $updataInfo['time_stamp'] = date("Y-m-d H:i:s");
                    \app\index\model\Admin::updatelogisticinfo($updataInfo);
                    return true;
                }
            }
            return $sendstate;
        }else{
		    return false;
        }
	}

}

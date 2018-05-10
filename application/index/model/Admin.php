<?php
	namespace app\index\model;
	use think\Db;
	use PHPExcel_IOFactory;
	use PHPExcel;
    use PHPExcel_RichText;
    use think\Session;

	class Admin extends \think\Model
	{
		/*登录验证*/

		/*退出登录*/
		public static function logout(){
			session("user_session", NULL);        					/*user_session置空，表示注销当前用户*/
			return true;
		}

        /*获取session*/
        public static function getsessioninfo(){
            return Session::get('user_session');
        }


		/*获取select选项值*/
		public static function getclassinfo($tablename,$tableID){
			$sql = "select * from ".$tablename;
			$sql.= " order by ".$tableID." asc";
			$tableobj = Db::query($sql);
			if(!empty($tableobj)){
				return $tableobj;
			}
		}
        /*根据某个属性获取数据*/
        public static function getclassinfobyproperty($tablename,$property,$value){
            $sql = "select * from ".$tablename;
            $sql.= " where '$property' = '$value'";
            $tableobj = Db::query($sql);
            if(!empty($tableobj)){
                return $tableobj;
            }
        }

		/*查询订货确认单*/
		public static function querygoodsorderinfo(...$args){
			
			return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
		}

		/*查询维修，代用等订单 销售部查询时调用 */
		/*参数:organizename(总部门) departmentname(子部门) areamanager(经理名) type  page  limit queryinfo*/
		public static function querycsinfobysales(...$args){
			$totalargs = count($args);
			$organizename = $args[0];
            $departmentname = $args[1];
            $areamanager = $args[2];
			$type = $args[3];
			$pagenum = intval($args[4]?$args[4]:1);
			$length = intval($args[5]);

			$sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            //   $sqltwo .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type' ";
			if($organizename != "")
            {
                $sqlone .= "and build_organize_name='$organizename' ";
            }
            if($departmentname != "")
            {
                $sqlone .= "and build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                $sqlone .= "and build_user_name='$areamanager' ";
            }

            if($totalargs == 7){
                if($args[6]['areamanager'] != "" && $areamanager == ""){
                    $areamanger1 = $args[6]['areamanager'];
                    $sqlone.= " and build_user_name ='$areamanger1'";
                }
                if($args[6]['departmentname'] != "" && $departmentname == ""){
                    $departmentname1 = $args[6]['departmentname'];
                    $sqlone.= " and build_department_name ='$departmentname1'";
                }
                if($args[6]['organizename'] != "" && $organizename == ""){
                    $organizename1 = $args[6]['organizename'];
                    $sqlone.= " and build_organize_name ='$organizename1'";
                }
                $startdate = $args[6]['startdate'];
                $enddate = $args[6]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqlone.= " and write_date >='$startdate' and write_date <='$enddate'";
                }
                if($args[6]['order_id'] != "")
                {
                    $cs_id = $args[6]['order_id'];
                    $sqlone.= " and dsp_logistic.cs_info.cs_id ='$cs_id'";
                }
                if($args[6]['orderstate'] != "")
                {
                    $cs_info_state = $args[6]['orderstate'];
                    $sqlone.= " and cs_info_state ='$cs_info_state'";
                }
                if($type == 2||$type == 5) //借样和配件没有返货信息
                {
                    if($args[6]['receiver_name'] != "")
                    {
                        $delivery_info_receiver_name = $args[6]['receiver_name'];
                        $sqlone.= " and delivery_info_receiver_name ='$delivery_info_receiver_name'";
                    }
                }
                else
                {
                    if($args[6]['receiver_name'] != "")
                    {
                        $return_info_receiver_name = $args[6]['receiver_name'];
                        $sqlone.= " and return_info_receiver_name ='$return_info_receiver_name'";
                    }
                }
            }
			$countobj = Db::query($sqlone);
			$count = $countobj[0]['count(*)'];
			if($count == 0){
				return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>[]));
			}
			$pagetot = ceil($count/$length);
			if($pagenum >= $pagetot){
				$pagenum = $pagetot;
			}

			$offset = ($pagenum - 1)*$length;
            $sqltwo ="select  dsp_logistic.cs_belong.* ,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.transfer_fee_mode,dsp_logistic.logistics_info.transfer_order_num,";
            $sqltwo .= "dsp_logistic.delivery_info.delivery_info_receiver_name,dsp_logistic.return_info.return_info_receiver_name from dsp_logistic.cs_info ";
            $sqltwo .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqltwo .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqltwo .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
         //   $sqltwo .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqltwo .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' ";
            if($organizename != "")
            {
                $sqltwo .= "and build_organize_name='$organizename' ";
            }
            if($departmentname != "")
            {
                $sqltwo .= "and build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                $sqltwo .= "and build_user_name='$areamanager' ";
            }
			if($totalargs == 7){
				if($args[6]['areamanager'] != "" && $areamanager == ""){
					$areamanger1 = $args[6]['areamanager'];
					$sqltwo.= " and build_user_name ='$areamanger1'";
				}
                if($args[6]['departmentname'] != "" && $departmentname == ""){
                    $departmentname1 = $args[6]['departmentname'];
                    $sqltwo.= " and build_department_name ='$departmentname1'";
                }
                if($args[6]['organizename'] != "" && $organizename == ""){
                    $organizename1 = $args[6]['organizename'];
                    $sqltwo.= " and build_organize_name ='$organizename1'";
                }
                $startdate = $args[6]['startdate'];
                $enddate = $args[6]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqltwo.= " and write_date >='$startdate' and write_date <='$enddate'";
                }
                if($args[6]['order_id'] != "")
                {
                    $cs_id = $args[6]['order_id'];
                    $sqltwo.= " and dsp_logistic.cs_info.cs_id ='$cs_id'";
                }
                if($args[6]['orderstate'] != "")
                {
                    $cs_info_state = $args[6]['orderstate'];
                    $sqltwo.= " and cs_info_state ='$cs_info_state'";
                }
                if($type == 2||$type == 5) //借样和配件没有返货信息
                {
                    if($args[6]['receiver_name'] != "")
                    {
                        $delivery_info_receiver_name = $args[6]['receiver_name'];
                        $sqltwo.= " and delivery_info_receiver_name ='$delivery_info_receiver_name'";
                    }
                }
                else
                {
                    if($args[6]['receiver_name'] != "")
                    {
                        $return_info_receiver_name = $args[6]['receiver_name'];
                        $sqltwo.= " and return_info_receiver_name ='$return_info_receiver_name'";
                    }
                }
			}
			$sqltwo .= "order By dsp_logistic.cs_info.write_date DESC limit {$offset},{$length} ;";
			$tableobj = Db::query($sqltwo);
			if(!empty($tableobj)){
                for ($i = 0;$i < $count;$i++)
                {
                    if($type == 2||$type == 5) //借样和配件没有返货信息
                    {
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];
                    }
                    else
                    {
                        //$tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                    }
                    $state = $tableobj[$i]["cs_info_state"];
                    $mode =  $tableobj[$i]["transfer_fee_mode"];
                    if($state == 0)
                        $tableobj[$i]["cs_info_state"] = "空";
                    elseif ($state == 1)
                    {
                        $tableobj[$i]["cs_info_state"] = "处理中";
                    }
                    elseif ($state == 2)
                    {
                        $tableobj[$i]["cs_info_state"] = "已完成";
                    }
                    elseif ($state == 3)
                    {
                        $tableobj[$i]["cs_info_state"] = "取消";
                    }
                    elseif ($state == 4)
                    {
                        $tableobj[$i]["cs_info_state"] = "备货";
                    }

                    if ($mode == 1)
                    $tableobj[$i]["transfer_fee_mode"] = "到付";
                    elseif ($mode == 2)
                    $tableobj[$i]["transfer_fee_mode"] = "现金";
                    elseif ($mode == 3)
                    $tableobj[$i]["transfer_fee_mode"] = "现付";
                    elseif ($mode == 4)
                    $tableobj[$i]["transfer_fee_mode"] = "公司付";


                }
				return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
			}
		}

        /*查询维修，代用等订单,物流部和财务的人查询时调用*/
        /*参数: type  page  limit queryinfo*/
        public static function querycsInfomation(...$args){
            $totalargs = count($args);

            $type = $args[0];
            $pagenum = intval($args[1]?$args[1]:1);
            $length = intval($args[2]);

            $sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            //   $sqltwo .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type' ";

            if($totalargs == 4){
                if($args[3]['areamanager'] != "" ){
                    $areamanger1 = $args[3]['areamanager'];
                    $sqlone.= " and build_user_name ='$areamanger1'";
                }
                if($args[3]['departname'] ){
                    $departmentname1 = $args[3]['departname'];
                    $sqlone.= " and (build_department_name ='$departmentname1' or build_organize_name ='$departmentname1') ";
                }
                $startdate = $args[3]['startdate'];
                $enddate = $args[3]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqlone.= " and write_date >='$startdate' and write_date <='$enddate' ";
                }
                if($args[3]['order_id'] != "")
                {
                    $cs_id = $args[3]['order_id'];
                    $sqlone.= " and dsp_logistic.cs_info.cs_id ='$cs_id' ";
                }
                if($args[3]['orderstate'] != "")
                {
                    $cs_info_state = $args[3]['orderstate'];
                    $sqlone.= " and cs_info_state ='$cs_info_state' ";
                }
                if($type == 2||$type == 5) //借样和配件没有返货信息
                {
                    if($args[3]['receiver_name'] != "")
                    {
                        $delivery_info_receiver_name = $args[3]['receiver_name'];
                        $sqlone.= " and delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                    }
                }
                else
                {
                    if($args[3]['receiver_name'] != "")
                    {
                        $return_info_receiver_name = $args[3]['receiver_name'];
                        $sqlone.= " and return_info_receiver_name ='$return_info_receiver_name' ";
                    }
                }
            }
            $countobj = Db::query($sqlone);
            $count = $countobj[0]['count(*)'];
            if($count == 0){
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>[]));
            }
            $pagetot = ceil($count/$length);
            if($pagenum >= $pagetot){
                $pagenum = $pagetot;
            }

            $offset = ($pagenum - 1)*$length;
            $sqltwo ="select  dsp_logistic.cs_belong.* ,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.transfer_fee_mode,dsp_logistic.logistics_info.transfer_order_num,";
            $sqltwo .= "dsp_logistic.delivery_info.delivery_info_receiver_name,dsp_logistic.return_info.return_info_receiver_name from dsp_logistic.cs_info ";
            $sqltwo .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqltwo .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqltwo .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            //   $sqltwo .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqltwo .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' ";
            if($totalargs == 4){
                if($args[3]['areamanager'] != ""){
                    $areamanger1 = $args[3]['areamanager'];
                    $sqltwo.= " and build_user_name ='$areamanger1' ";
                }
                if($args[3]['departname'] != "" ){
                    $departmentname1 = $args[3]['departname'];
                    $sqltwo.= " and (build_department_name ='$departmentname1' or build_organize_name ='$departmentname1') ";
                }

                $startdate = $args[3]['startdate'];
                $enddate = $args[3]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqltwo.= " and write_date >='$startdate' and write_date <='$enddate' ";
                }
                if($args[3]['order_id'] != "")
                {
                    $cs_id = $args[3]['order_id'];
                    $sqltwo.= " and dsp_logistic.cs_info.cs_id ='$cs_id' ";
                }
                if($args[3]['orderstate'] != "")
                {
                    $cs_info_state = $args[3]['orderstate'];
                    $sqltwo.= " and cs_info_state ='$cs_info_state' ";
                }
                if($type == 2||$type == 5) //借样和配件没有返货信息
                {
                    if($args[3]['receiver_name'] != "")
                    {
                        $delivery_info_receiver_name = $args[3]['receiver_name'];
                        $sqltwo.= " and delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                    }
                }
                else
                {
                    if($args[3]['receiver_name'] != "")
                    {
                        $return_info_receiver_name = $args[3]['receiver_name'];
                        $sqltwo.= " and return_info_receiver_name ='$return_info_receiver_name' ";
                    }
                }
            }
            $sqltwo .= "order By dsp_logistic.cs_info.write_date DESC limit {$offset},{$length} ;";
            $tableobj = Db::query($sqltwo);
            if(!empty($tableobj)){
                for ($i = 0;$i < $count;$i++)
                {
                    if($type == 2||$type == 5) //借样和配件没有返货信息
                    {
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];
                    }
                    else
                    {
                        //$tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                    }
                    $state = $tableobj[$i]["cs_info_state"];
                    $mode =  $tableobj[$i]["transfer_fee_mode"];
                    if($state == 0)
                        $tableobj[$i]["cs_info_state"] = "空";
                    elseif ($state == 1)
                    {
                        $tableobj[$i]["cs_info_state"] = "处理中";
                    }
                    elseif ($state == 2)
                    {
                        $tableobj[$i]["cs_info_state"] = "已完成";
                    }
                    elseif ($state == 3)
                    {
                        $tableobj[$i]["cs_info_state"] = "取消";
                    }
                    elseif ($state == 4)
                    {
                        $tableobj[$i]["cs_info_state"] = "备货";
                    }

                    if ($mode == 1)
                        $tableobj[$i]["transfer_fee_mode"] = "到付";
                    elseif ($mode == 2)
                        $tableobj[$i]["transfer_fee_mode"] = "现金";
                    elseif ($mode == 3)
                        $tableobj[$i]["transfer_fee_mode"] = "现付";
                    elseif ($mode == 4)
                        $tableobj[$i]["transfer_fee_mode"] = "公司付";
                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
        }

        /*根据条件查询审批确认单，五个参数最少四个:user_id  type  page  limit queryinfo*/
        public static function queryApproveConfirmOrder(...$args){
            $totalargs = count($args);
            $examine_user_id = $args[0];
            $type = $args[1];
            $pagenum = intval($args[2]?$args[2]:1);
            $length = intval($args[3]);

            $sqlone = "select count(*) from dsp_logistic.cs_examine ";
            $sqlone .= "left join dsp_logistic.cs_info on dsp_logistic.cs_info.cs_id = dsp_logistic.cs_examine.cs_id ";
         //   $sqlone .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
         //   $sqlone .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone.=" where examine_user_id = '$examine_user_id' and cs_info_type = '$type'";
            if($totalargs == 5){
                $startdate = $args[4]['startdate'];
                $enddate = $args[4]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqlone.= " and write_date >='$startdate' and write_date <='$enddate' ";
                }
                if($args[4]['order_id'] != "")
                {
                    $cs_id = $args[4]['order_id'];
                    $sqlone.= " and dsp_logistic.cs_info.cs_id ='$cs_id' ";
                }
                if($args[4]['orderstate'] != "")
                {
                    $cs_info_state = $args[4]['orderstate'];
                    $sqlone.= " and cs_info_state ='$cs_info_state' ";
                }
                if($type == 2||$type == 5) //借样和配件没有返货信息
                {
                    if($args[4]['receiver_name'] != "")
                    {
                        $delivery_info_receiver_name = $args[4]['receiver_name'];
                        $sqlone.= " and delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                    }
                }
                else
                {
                    if($args[4]['receiver_name'] != "")
                    {
                        $return_info_receiver_name = $args[4]['receiver_name'];
                        $sqlone.= " and return_info_receiver_name ='$return_info_receiver_name' ";
                    }
                }
                if($args[4]['departname'] != "")
                {
                    $departmentname1 = $args[4]['departname'];
                    $sqlone.= " and (build_department_name ='$departmentname1' or build_organize_name ='$departmentname1') ";

                }

                if($args[4]['areamanager'] != "")
                {
                    $user_name = $args[4]['areamanager'];
                    $sqlone.= " and build_user_name ='$user_name' ";

                }
            }
            $countobj = Db::query($sqlone);
            $count = $countobj[0]['count(*)'];
            if($count == 0){
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>[]));
            }


            $pagetot = ceil($count/$length);
            if($pagenum >= $pagetot){
                $pagenum = $pagetot;
            }

            $offset = ($pagenum - 1)*$length;
            $sqltwo = "select  dsp_logistic.cs_belong.* ,dsp_logistic.cs_info.*,";
            $sqltwo .= "dsp_logistic.delivery_info.delivery_info_receiver_name,dsp_logistic.return_info.return_info_receiver_name from dsp_logistic.cs_examine ";
            $sqltwo .= "left join dsp_logistic.cs_info on dsp_logistic.cs_info.cs_id = dsp_logistic.cs_examine.cs_id ";
          //  $sqltwo .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqltwo .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqltwo .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
         //   $sqltwo .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo.=" where examine_user_id = '$examine_user_id' and cs_info_type = '$type'";
            if($totalargs == 5){
                $startdate = $args[4]['startdate'];
                $enddate = $args[4]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqltwo.= " and write_date >='$startdate' and write_date <='$enddate' ";
                }
                if($args[4]['order_id'] != "")
                {
                    $cs_id = $args[4]['order_id'];
                    $sqltwo.= " and dsp_logistic.cs_info.cs_id ='$cs_id' ";
                }
                if($args[4]['orderstate'] != "")
                {
                    $cs_info_state = $args[4]['orderstate'];
                    $sqltwo.= " and cs_info_state ='$cs_info_state' ";
                }
                if($type == 2||$type == 5) //借样和配件没有返货信息
                {
                    if($args[4]['receiver_name'] != "")
                    {
                        $delivery_info_receiver_name = $args[4]['receiver_name'];
                        $sqltwo.= " and delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                    }
                }
                else
                {
                    if($args[4]['receiver_name'] != "")
                    {
                        $return_info_receiver_name = $args[4]['receiver_name'];
                        $sqltwo.= " and return_info_receiver_name ='$return_info_receiver_name' ";
                    }
                }
                if($args[4]['departname'] != "")
                {
                    $departmentname1 = $args[4]['departname'];
                    $sqltwo.= " and (build_department_name ='$departmentname1' or build_organize_name ='$departmentname1') ";

                }

                if($args[4]['areamanager'] != "")
                {
                    $user_name = $args[4]['areamanager'];
                    $sqltwo.= " and build_user_name ='$user_name' ";

                }
            }
            $sqltwo .= " order By dsp_logistic.cs_examine.cs_examine_id DESC limit {$offset},{$length} ;";
            $tableobj = Db::query($sqltwo);
            if(!empty($tableobj)){
                $count = count($tableobj);
                for ($i = 0;$i < $count;$i++)
                {
                    if($type == 2||$type == 5) //借样和配件没有返货信息
                    {
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];
                    }
                    else
                    {
                        //$tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                    }
                    $tableobj[$i]["serial_number"] = $i+1;
                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
        }

		/*测试新建表格对象，并且填写数据*/
		public static function testexcel(){
			$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
			$PHPSheet = $PHPExcel->getActiveSheet(); 
			$PHPSheet->setTitle('demo'); 
			$PHPSheet->setCellValue('A1','姓名')->setCellValue('B1','分数');
			$PHPSheet->setCellValue('A2','张三')->setCellValue('B2','50');
			$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
		  	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        	header('Content-Disposition: attachment;filename="01simple.xlsx"');
        	header('Cache-Control: max-age=0');
        	$PHPWriter->save("php://output");
		}

		/*根据模板导出表格测试*/
		public static function testtemplateexport(){
			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			$objPHPExcel = $objReader->load("F:/website/Apache24/htdocs/LogisticsOrder/public/templates/26template.xlsx");
			$objPHPExcel->getActiveSheet()->setTitle('sheetone');
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('C3', '研发一部');
			$objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setName('Candara');
			$objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setSize(16);
			$objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setBold(true);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007'); 
		  	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        	header('Content-Disposition: attachment;filename="01simple.xlsx"');
        	header('Cache-Control: max-age=0');
        	$objWriter->save("php://output");
		}

        /*根据条件查询用户信息*/
        public static function queryuserinfo($pagenum,$length)
        {
            $sql = "select count(*) from dsp_logistic.user ;";
            $countobj = Db::query($sql);
            $count = $countobj[0]['count(*)'];
            if($count == 0){
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>[]));
            }
            $pagetot = ceil($count/$length);
            if($pagenum >= $pagetot){
                $pagenum = $pagetot;
            }

            $offset = ($pagenum - 1)*$length;
            $sql = "SELECT dsp_logistic.user.*,dsp_logistic.organize.*,dsp_logistic.role.* ,dsp_logistic.job.*  from dsp_logistic.user ";
            $sql .= "left join dsp_logistic.organize on dsp_logistic.organize.organize_id = dsp_logistic.user.organize_id ";
            $sql .= "left join dsp_logistic.role on dsp_logistic.role.role_id = dsp_logistic.user.role_id ";
            $sql .= "left join dsp_logistic.job on dsp_logistic.job.job_id = dsp_logistic.user.job_id ";
            $sql .= " order By dsp_logistic.user.organize_id DESC limit {$offset},{$length} ;";
            $tableobj = Db::query($sql);
            if(!empty($tableobj)){
                $datacount = count($tableobj);
                for ($i = 0;$i<$datacount;$i++)
                {
                    $parentid = $tableobj[$i]["parent_id"];
                    if($parentid === 0) //只有总部门，子部门为空
                    {
                        //$name =
                        $tableobj[$i]["companyname"]=$tableobj[$i]["organize_name"];
                        $tableobj[$i]["organize_name"] = "";
                        $tableobj[$i]["companyid"] = $tableobj[$i]["organize_id"];
                    }
                    else {
                        $sql ="select * from dsp_logistic.organize where `organize_id` = '{$parentid}'";

                        $conpanytalbe = Db::query($sql);
                        if(!empty($conpanytalbe))
                        {
                            $tableobj[$i]["companyid"] = $conpanytalbe[0]["organize_id"];
                            $tableobj[$i]["companyname"] = $conpanytalbe[0]["organize_name"];
                        }
                        else{
                            $tableobj[$i]["companyname"]="";
                            $tableobj[$i]["companyid"] = "";
                        }
                    }
                    $tableobj[$i]["serialnumber"]=$i+1;


                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>[]));
        }

        /*根据查询角色信息*/
        public static function queryroleinfo(...$param)
        {
            $sql = "select * from  dsp_logistic.role ";
            $count = count($param);
            if($count == 1)
            {
                $sql.= "where role_id = '{$param[0]}'";
            }
            $tablerole = Db::query($sql);
            if(!empty($tablerole))
                return $tablerole;
            return null;
        }
        /*根据查询职位信息*/
        public static function queryjobinfo()
        {
            $sql = "select * from  dsp_logistic.job ";
            $tablejob = Db::query($sql);
            if(!empty($tablejob))
                return $tablejob;
            return null;
        }
        /*根据查询部门信息*/
        public static function querydepartmentinfo(...$param)
        {
            $paramcount = count($param);
            $sql = "select * from  dsp_logistic.organize";
            if($paramcount === 1)
            {
                $param1 = $param[0];
                $sql.= " where dsp_logistic.organize.parent_id = {$param1} ";
            }
            elseif ($paramcount === 2) //两个参数 parent_id ,organize_id
            {
                $param1 = $param[0];
                $param2 = $param[1];
                $sql.= " where dsp_logistic.organize.parent_id = {$param1} or dsp_logistic.organize.organize_id = {$param2}";
            }
            $tableorganize = Db::query($sql);
            if(!empty($tableorganize))
                return $tableorganize;
            return null;
        }
        /*根据部门id那名字*/
        public static function querydepartmentname($param)
        {
            $sql = "select * from  dsp_logistic.organize where organize_id = '$param'";
            $tableorganize = Db::query($sql);
            if(!empty($tableorganize))
                return $tableorganize[0]["organize_name"];
            return "";
        }

        /*根据增加和更新组织架构*/
        public static function updatedepartment($department){
        	$sqlone = "delete FROM dsp_logistic.organize where organize_id > 0";
        	$result = Db::execute($sqlone);

        	for($item=0 ; $item < count($department); $item++){
        		$organize_id = intval($department[$item]['organize_id']);
        		$parent_id = intval($department[$item]['parent_id']);
        		$organize_name = $department[$item]['organize_name'];
        		$sqltwo="INSERT INTO dsp_logistic.organize (organize_id,parent_id,organize_name) VALUES ('$organize_id','$parent_id','$organize_name') ON DUPLICATE KEY UPDATE parent_id='$parent_id',organize_name = '$organize_name'";
        		$result = Db::execute($sqltwo);
        	}
        	return true;
        }

        public static function getmaxorganizeID(){
        	$sql = "SELECT max(organize_id) FROM dsp_logistic.organize";
            $organizeID = Db::query($sql);
            return $organizeID;
        }

        /*根据增加和更新用户*/
        public static function updateuser($user)
        {
            $fullname = $user["fullname"];
            $password = $user["password"];
            $phone = $user["phone"];
            $organize_id = $user["department_id"];
            if(empty($organize_id)) //如果子部门不选，则保存总部门
            {
                $organize_id = $user["company_id"];
            }

            $job_id = $user["job_id"];
            $role_id = $user["role_id"];

            $user_id = $user["user_id"];
            if($user_id != "")
            {
                $sql = " UPDATE dsp_logistic.user ";
                $sql.="  SET  fullname = '{$fullname}', password = '{$password}', phone = '{$phone}', organize_id = '{$organize_id}', job_id = '{$job_id}', role_id = '{$role_id}' ";
                $sql.=" where user_id = '{$user_id}'";
                $result = Db::execute($sql);
                return $result;
            }
            else
            {
                $sql = "INSERT INTO `dsp_logistic`.`user` (`fullname`, `password`, `phone`, `organize_id`, `job_id`, `role_id`)";
                $sql.="  VALUES ('{$fullname}', '{$password}', '{$phone}', '{$organize_id}', '{$job_id}', '{$role_id}');";
                $result = Db::execute($sql);
                return $result;
            }

        }
        /*获取删除用户*/
        public  static function deleteuser($userid)
        {
            $sql = "Delete FROM dsp_logistic.user where user_id = '{$userid}'";
            $retsql = Db::query($sql);
            return $retsql;

        }

        /*获取用户信息根据id*/
        public  static function getloginuserinfo($userid)
        {
            $sql = "SELECT * FROM dsp_logistic.user WHERE user_id = '{$userid}' LIMIT 1";
            $retsql = Db::query($sql);
            return $retsql;
        }

        /*查询单号是否存在 hjh*/
        public function queryhasconfirmorder($orderis)
        {
            $sql = "SELECT * FROM dsp_logistic.cs_info WHERE id = {$orderis}";
            $retsql = Db::query($sql);
            return $retsql;
        }

        /*新增更换确认单 hjh*/
        public static function updateroleinfo($role)
        {

            $sql = "UPDATE dsp_logistic.role ";
            $sql.="  SET  role_name = '{$role["role_name"]}', order_goods_permission = '{$role["order_goods_permission"]}', replace_permission = '{$role["replace_permission"]}', borrow_sample_permission = '{$role["borrow_sample_permission"]}', return_goods_permission = '{$role["return_goods_permission"]}',";
            $sql.= "fixing_permission =  '{$role["fixing_permission"]}',maintain_permission = '{$role["maintain_permission"]}',substitute_permission = '{$role["substitute_permission"]}',user_manage_permission = '{$role["user_manage_permission_manage"]}' ,logistic_input_permission = '{$role["logistic_input_permission"]}'";
            $sql.=" where role_id = '{$role["role_id"]}'";
            $result = Db::execute($sql);
            return "$result";
        }

        /*获取用户信息*/
        public static  function getuserinfobydepid($depid)
        {
            $sql = "SELECT * FROM dsp_logistic.user WHERE organize_id = {$depid}";
            $retsql = Db::query($sql);
            return $retsql;
        }

        /*模糊搜索型号*/
        public  static function serachmodelinfo($serachText, $product_type_id, $brand)
        {
            $sql = "SELECT * FROM dsp_logistic.product_info WHERE model LIKE '%{$serachText}%' AND product_type_id = '{$product_type_id}' AND brand_id = '{$brand}'";
            $retsql = Db::query($sql);
            return $retsql;
        }

        /*获取表中最大的id值*/
        public static function getmaxtableid($tablename,$tableID){
            $sql = "SELECT max({$tableID}) FROM dsp_logistic.{$tablename}";
            $organizeID = Db::query($sql);
            return $organizeID;
        }

        /*获取表中最大的id值*/
        public static function getmaxtableidretid($tablename,$tableID){
            $sql = "SELECT max({$tableID}) FROM dsp_logistic.{$tablename}";
            $organizeID = Db::query($sql);
            if (!empty($organizeID)){
                $id = $organizeID[0]["max({$tableID})"];
                if (empty($id)){
                    return 0;
                }
                return $id;
            }else{
                //return -1;
            }
        }

        /*删除表的行*/
        public static function deleterowtableid($tablename,$tableID,$value){
            $sql = "DELETE FROM dsp_logistic.{$tablename} WHERE {$tableID} = '{$value}'";
            $organizeID = Db::query($sql);
            return $organizeID;
        }

        /*新增订单    暂时使用（更改确认单）*/
        public  static function addconfirmorder($info)
        {
            $cs_id = $info['cs_id'];
            $custom_info_id = $info['custom_info_id'];
            $delivery_info_id = $info['delivery_info_id'];
            $return_info_id = $info['return_info_id'];
            $payment_info_id = $info['payment_info_id'];
            $cur_process_user_id = $info['cur_process_user_id'];
            $pre_process_user_id = $info['pre_process_user_id'];
            $cs_info_type = $info['cs_info_type'];
            $can_edit = $info['can_edit'];
            $write_date = $info['write_date'];
            $cs_info_state = $info['cs_info_state'];
            $complete_date = $info['complete_date'];
            $product_number = $info['product_number'];
            $sql_value ="'{$cs_id}','{$custom_info_id}','{$delivery_info_id}','{$return_info_id}','{$payment_info_id}','{$cur_process_user_id}','{$pre_process_user_id}','{$cs_info_type}','{$can_edit}','{$write_date}','{$cs_info_state}','{$complete_date}','{$product_number}'";
            $sql = "INSERT INTO dsp_logistic.cs_info (cs_id,custom_info_id,delivery_info_id,return_info_id,payment_info_id,cur_process_user_id,pre_process_user_id
,cs_info_type,can_edit,write_date,cs_info_state,complete_date,product_number) VALUES ({$sql_value}) ";
            $sql.= "ON DUPLICATE KEY UPDATE custom_info_id = '{$custom_info_id}',delivery_info_id = '{$delivery_info_id}',return_info_id = '{$return_info_id}',payment_info_id = '{$payment_info_id}',";
            $sql.= "cur_process_user_id= '{$cur_process_user_id}',pre_process_user_id= '{$pre_process_user_id}',cs_info_type = '{$cs_info_type}',";
            $sql.= "can_edit= '{$can_edit}',write_date= '{$write_date}',cs_info_state= '{$cs_info_state}',complete_date= '{$complete_date}',product_number= '{$complete_date}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*新增 custom_info */
        public  static function addcustominfo($info)
        {
            $custom_info_id = $info['custom_info_id'];
            $company_name = $info['company_name'];
            $company_phone = $info['company_phone'];
            $company_address = $info['company_address'];
            $legal_representative = $info['legal_representative'];
            $legal_phone = $info['legal_phone'];
            $company_contact = $info['company_contact'];
            $company_contact_phone = $info['company_contact_phone'];
            $sql_value ="'{$custom_info_id}','{$company_name}','{$company_phone}','{$company_address}','{$legal_representative}','{$legal_phone}','{$company_contact}','{$company_contact_phone}'";
            $sql = "INSERT INTO dsp_logistic.custom_info (custom_info_id,company_name,company_phone,company_address,legal_representative,legal_phone,company_contact,company_contact_phone) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE company_name = '{$company_name}',company_phone = '{$company_phone}',company_address = '{$company_address}',";
            $sql.= "legal_representative= '{$legal_representative}',legal_phone= '{$legal_phone}',company_contact = '{$company_contact}',";
            $sql.= "company_contact_phone= '{$company_contact_phone}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*新增 delivery_info */
        public static function adddeliveryinfo($info){
            $delivery_info_id = $info['delivery_info_id'];
            $delivery_info_receiver_name = $info['delivery_info_receiver_name'];
            $receiver_phone = $info['receiver_phone'];
            $goods_yard_name = $info['goods_yard_name'];
            $goods_yard_phone = $info['goods_yard_phone'];
            $receiver_address = $info['receiver_address'];
            $is_insure = $info['is_insure'];
            $is_sign = $info['is_sign'];
            $insure_amout = $info['insure_amout'];
            $has_contract = $info['has_contract'];
            $transfer_fee_mode = $info['transfer_fee_mode'];
            $sql_value ="'$delivery_info_id','{$delivery_info_receiver_name}','{$receiver_phone}','{$goods_yard_name}','{$goods_yard_phone}','{$receiver_address}','{$is_insure}','{$insure_amout}','{$is_sign}','{$has_contract}','{$transfer_fee_mode}'";
            $sql = "INSERT INTO dsp_logistic.delivery_info (delivery_info_id,delivery_info_receiver_name,receiver_phone,goods_yard_name,goods_yard_phone,receiver_address,is_insure,insure_amout,is_sign,has_contract,transfer_fee_mode) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE delivery_info_receiver_name = '$delivery_info_receiver_name',receiver_phone = '$receiver_phone',goods_yard_name = '{$goods_yard_name}',";
            $sql.= "goods_yard_phone= '{$goods_yard_phone}',receiver_address= '{$receiver_address}',is_insure = '{$is_insure}',";
            $sql.= "is_sign= '{$is_sign}',insure_amout= '{$insure_amout}',has_contract= '{$has_contract}',transfer_fee_mode= '{$transfer_fee_mode}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*新增 return_info */
        public  static function addreturninfo($info)
        {
            $return_info_id = $info['return_info_id'];
            $return_info_receiver_name = $info['return_info_receiver_name'];
            $return_info_receiver_phone = $info['return_info_receiver_phone'];
            $return_info_goods_yard_name = $info['return_info_goods_yard_name'];
            $return_info_goods_yard_phone = $info['return_info_goods_yard_phone'];
            $return_order_num = $info['return_order_num'];
            $return_info_receiver_address = $info['return_info_receiver_address'];
            $sql_value ="'$return_info_id','{$return_info_receiver_name}','{$return_info_receiver_phone}','{$return_info_goods_yard_name}','{$return_info_goods_yard_phone}','{$return_order_num}','{$return_info_receiver_address}'";
            $sql = "INSERT INTO dsp_logistic.return_info (return_info_id,return_info_receiver_name,return_info_receiver_phone,return_info_goods_yard_name,return_info_goods_yard_phone,return_order_num,return_info_receiver_address) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE return_info_receiver_name = '$return_info_receiver_name',return_info_receiver_phone = '$return_info_receiver_phone',return_info_goods_yard_name = '{$return_info_goods_yard_name}',";
            $sql.= "return_info_goods_yard_phone= '{$return_info_goods_yard_phone}',return_order_num= '{$return_order_num}',return_info_receiver_address = '{$return_info_receiver_address}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }

        /*新增 payment_info*/
        public static function addpaymentinfo($info){
            $payment_info_id = $info['payment_info_id'];
            $is_pad = $info['is_pad'];
            $paid_date = $info['paid_date'];
            $customization_fee = $info['customization_fee'];
            $paid_bank = $info['paid_bank'];
            $payment_info_comment = $info['comment'];
//            $time_stamp = $info['time_stamp'];
//            $user_id = $info['user_id'];
            $sql_value ="'$payment_info_id','{$is_pad}','{$paid_date}','{$customization_fee}','{$paid_bank}','{$payment_info_comment}'";
            $sql = "INSERT INTO dsp_logistic.payment_info (payment_info_id,is_pad,paid_date,customization_fee,paid_bank,comment,time_stamp,user_id) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE is_pad = '$is_pad',paid_date = '$paid_date',customization_fee = '{$customization_fee}',";
            $sql.= "paid_bank= '$paid_bank',payment_info_comment= '$payment_info_comment'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*cs_belong*/
        public static function updatecsbelong($info){
            $cs_belong_id = $info['cs_belong_id'];
            $cs_id = $info['cs_id'];
            $build_organize_id = $info['build_organize_id'];
            $build_user_id = $info['build_user_id'];
            $cs_belong_create_time = $info['cs_belong_create_time'];
            $build_user_name = $info['build_user_name'];
            $build_organize_name = $info['build_organize_name'];
            $build_department_id = $info['build_department_id'];
            $build_department_name = $info['build_department_name'];
            $sql_value ="'$cs_belong_id','{$cs_id}','{$build_organize_id}','{$build_user_id}','{$cs_belong_create_time}','{$build_user_name}','{$build_organize_name}','{$build_department_id}','{$build_department_name}'";
            $sql = "INSERT INTO dsp_logistic.cs_belong (cs_belong_id,cs_id,build_organize_id,build_user_id,cs_belong_create_time,build_user_name,build_organize_name,build_department_id,build_department_name) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE cs_id = '$cs_id',build_organize_id = '$build_organize_id',build_user_id = '$build_user_id',";
            $sql.= "cs_belong_create_time= '$cs_belong_create_time',build_user_name= '$build_user_name',build_organize_name= '$build_organize_name',";
            $sql.= "build_department_id='{$build_department_id}',build_department_name='{$build_department_name}';";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*新增 确认单清单经理部分 order_goods_manager*/
        public static function addordergoodsmanager($info){
            //id
            $order_goods_manager_id = $info['order_goods_manager_id'];
            $cs_id = $info['cs_id'];
            $product_info_id = $info['product_info_id'];
            $unit_price = $info['unit_price'];
            $unit = $info['unit'];
            $order_goods_manager_count = $info['order_goods_manager_count'];
            $specification = $info['specification'];
            $order_goods_manager_explain = $info['order_goods_manager_explain'];
            $type = $info['type'];
            $comment = $info['comment'];
            $bar_code = $info['bar_code'];
            $back_date = $info['back_date'];
            $replace_reason = $info['replace_reason'];
            $purchase_date = $info['purchase_date'];
            $deal_date = $info['deal_date'];
            $fault_condition = $info['fault_condition'];
            $sql_value ="'$order_goods_manager_id','{$cs_id}','{$product_info_id}','{$unit_price}','{$order_goods_manager_count}','{$specification}','{$order_goods_manager_explain}','{$type}'";
            $sql_value .= ",'{$comment}','{$bar_code}','{$back_date}','{$replace_reason}','{$purchase_date}','{$deal_date}','{$fault_condition}','$unit'";
            $sql = "INSERT INTO dsp_logistic.order_goods_manager (order_goods_manager_id,cs_id,product_info_id,unit_price,order_goods_manager_count,specification,order_goods_manager_explain,type";
            $sql .= ",comment,bar_code,back_date,replace_reason,purchase_date,deal_date,fault_condition,unit) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE cs_id = '$cs_id',product_info_id = '$product_info_id',unit_price = '$unit_price',";
            $sql.= "order_goods_manager_count= '$order_goods_manager_count',specification= '$specification',order_goods_manager_explain= '$order_goods_manager_explain',";
            $sql.= "type= '$type',comment= '$comment',bar_code= '$bar_code',";
            $sql.= "back_date= '$back_date',replace_reason= '$replace_reason',purchase_date= '$purchase_date',";
            $sql.= "deal_date= '$deal_date',fault_condition= '$fault_condition' ,unit = '$unit';";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*新增 确认单清单物流部分 order_goods_logistics*/
        public static function addordergoodslogistics($info){
            $ogl_id = $info['ogl_id'];
            $order_goods_manager_id = $info['order_goods_manager_id'];
            $ogl_product_state = $info['ogl_product_state'];
            $ogl_unc_product_id = $info['ogl_unc_product_id'];
            $ogl_comment = $info['ogl_comment'];
            $user_id = $info['user_id'];
            $ogl_time_stamp = $info['ogl_time_stamp'];
            $ogl_explain = $info['ogl_explain'];
            $sql_value ="'$ogl_id','{$order_goods_manager_id}','{$ogl_product_state}','{$ogl_unc_product_id}','{$ogl_comment}','{$user_id}','{$ogl_time_stamp}','{$ogl_explain}'";
            $sql = "INSERT INTO dsp_logistic.order_goods_logistics (ogl_id,order_goods_manager_id,ogl_product_state,ogl_unc_product_id,ogl_comment,user_id,ogl_time_stamp,ogl_explain) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE $order_goods_manager_id = '$order_goods_manager_id',ogl_product_state = '$ogl_product_state',ogl_unc_product_id = '$ogl_unc_product_id',";
            $sql.= "ogl_comment= '$ogl_comment',user_id= '$user_id',ogl_time_stamp= '$ogl_time_stamp',";
            $sql.= "ogl_explain= '$ogl_explain'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*新增 确认单审批 cs_examine*/
        public static function addcsexamine($info){
            $cs_examine_id = $info['cs_examine_id'];
            $cs_id = $info['cs_id'];
            $submit_user_id = $info['submit_user_id'];
            $examine_user_id = $info['examine_user_id'];
            $cs_examine_date = $info['cs_examine_date'];
            $cs_examine_content = $info['cs_examine_content'];
            $cs_examine_result = $info['cs_examine_result'];
            $cs_examine_time_stamp = $info['cs_examine_time_stamp'];
            $cs_examine_comment = $info['cs_examine_comment'];
            $cs_examine_name = $info['cs_examine_name'];
            $cs_examine_state = $info['cs_examine_state'];
            $sql_value ="'$cs_examine_id','{$cs_id}','{$submit_user_id}','{$examine_user_id}','{$cs_examine_date}','{$cs_examine_content}','{$cs_examine_result}','{$cs_examine_time_stamp}','{$cs_examine_comment}','{$cs_examine_name}','{$cs_examine_state}'";
            $sql = "INSERT INTO dsp_logistic.cs_examine (cs_examine_id,cs_id,submit_user_id,examine_user_id,cs_examine_date,cs_examine_content,cs_examine_result,cs_examine_time_stamp,cs_examine_comment,cs_examine_name,cs_examine_state) VALUES ({$sql_value})";
            $sql.= "ON DUPLICATE KEY UPDATE cs_id = '$cs_id',submit_user_id = '$submit_user_id',examine_user_id = '$examine_user_id',";
            $sql.= "cs_examine_date= '$cs_examine_date',cs_examine_content= '$cs_examine_content',cs_examine_result= '$cs_examine_result',";
            $sql.= "cs_examine_time_stamp= '$cs_examine_time_stamp',cs_examine_comment= '$cs_examine_comment',cs_examine_name= '$cs_examine_name',";
            $sql.= "cs_examine_state= '$cs_examine_state'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }

        /*查询订单未审核的条数,未完待续*/
        public static function queryexamineordernums($cs_info_type,$cs_info_state){
            $sql = "select count(*) from dsp_logistic.cs_info where cs_info_type='$cs_info_type' and cs_info_state='$cs_info_state'";
            $countobj = Db::query($sql);
            $count = $countobj[0]['count(*)'];
            return $count;
        }

        /*查找 所属领导信息（$role_name：总经理/财务部 /其它看数据库）*/
        public static function getdepleaderbyuserid($userid,$role_name){

            $sqlrole = "SELECT * FROM dsp_logistic.role WHERE role_name = '{$role_name}' LIMIT 1";
            $retrole = Db::query($sqlrole);
            $retuserinfo = self::getloginuserinfo($userid);
            if (empty($retuserinfo)){
                return ;
            }
            if (empty($retrole)){
                return ;
            }
            $org_id = $retuserinfo[0]['organize_id'];
            $role_id = $retrole[0]['role_id'];
            if ($role_name == '总经理'){
                 $sqlorg =  "SELECT * FROM dsp_logistic.organize WHERE organize_id = '{$org_id}' LIMIT 1";
                 $retorg = Db::query($sqlorg);
                 if(empty($retorg)){
                     //return '改经理公司不存在';
                     return null;
                 }
                $org_pid = $retorg[0]['parent_id'];
                $sqlleader = "SELECT * FROM dsp_logistic.user WHERE role_id = '{$role_id}' AND organize_id = '{$org_pid}' LIMIT 1";
                return Db::query($sqlleader);
            }else if($role_name != '财务部'){
                $sqlleader = "SELECT * FROM dsp_logistic.user WHERE role_id = '{$role_id}' AND organize_id = '{$org_id}' LIMIT 1";
                //return $sqlleader;
                return Db::query($sqlleader);
            }else {
                $sqlleader = "SELECT * FROM dsp_logistic.user WHERE role_id = '{$role_id}' LIMIT 1";
                //return $sqlleader;
                return Db::query($sqlleader);
            }
        }
        public static function getcsinfomaxid($tableName,$tableID,$type = ""){
            $dateymd = date('Ymd');
            if (!empty($type) && $type != ""){
                $dateymd = $type.$dateymd;
            }
            $sql ="select * from dsp_logistic.{$tableName} where {$tableID} like '%{$dateymd}%'";
            $retdb = Db::query($sql);
            if(empty($retdb)){
                return $dateymd.'0001';
            }else{
                $num = count($retdb);
                $maxid = 0001;
                for($i = 0; $i < $num; $i++){
                    $cs_id = $retdb[$i]['cs_id'];
                    $cur_id = str_replace($dateymd,'',$cs_id);
                    if ($cur_id > $maxid){
                        $maxid = $cur_id;
                    }
                }

                $maxid = $maxid + 0001;
                $strmaxid = $newStr= sprintf('%04s', $maxid);;
                return $dateymd.$strmaxid;
            }
        }

        public static function getcuruserquerypower($user)
        {
            if(empty($user))
                return;
            $role = \app\index\model\Admin::queryroleinfo($user["role_id"]);
            if(empty($role))
                return ;
            $rolename = $role[0]["role_name"];
            $userquerypower = array();
            $userquerypower["isSales"] = false;
            if( $rolename == "管理人员" || $rolename == "部长/主管"||$rolename == "物流部人员"||$rolename == "财务部")
            {
                $userquerypower["isSales"] = true;
            }
            else
            {
                if($rolename == "总经理")
                {
                    $userquerypower["organizename"] = \app\index\model\Admin::querydepartmentname($user["organize_id"]);
                    $userquerypower["departmentname"] ="";
                    $userquerypower["areamanager"] ="";
                }
                if($rolename == "总监")
                {
                    $userquerypower["departmentname"] = \app\index\model\Admin::querydepartmentname($user["organize_id"]);
                    $userquerypower["areamanager"] ="";
                    $organize = \app\index\model\Admin::getdepleaderbyuserid($user["user_id"],"总经理");
                    $userquerypower["organizename"] = \app\index\model\Admin::querydepartmentname($organize[0]["organize_id"]);
                }
                if($rolename == "经理")
                {
                    $userquerypower["areamanager"] = $user["fullname"];
                    $depart = \app\index\model\Admin::getdepleaderbyuserid($user["user_id"],"总监");
                    $userquerypower["departmentname"] = \app\index\model\Admin::querydepartmentname($depart[0]["organize_id"]);
                    $organize = \app\index\model\Admin::getdepleaderbyuserid($depart[0]["user_id"],"总经理");
                    $userquerypower["organizename"] = \app\index\model\Admin::querydepartmentname($organize[0]["organize_id"]);
                }
            }
            session("user_querypower", $userquerypower);
        }

        public static function login($username,$password){
            $where['fullname'] = $username;
            $where['password'] = $password;

            $user = Db::name('dsp_logistic.user')->where($where)->find();   /*用户信息检测*/
            if($user){
                unset($user["password"]);      	   					       /*销毁password*/
                session_start();
                session("user_session", $user);/*创建session,里面只包含用户名，password已经销毁*/

                \app\index\model\Admin::getcuruserquerypower($user);
                return true;
            }else{
                return false;
            }
        }

        /*查询需要导出的更换确认单  未完待续*/
        public static function queryexportreplaceconfirmorder($param,$type){
            $sqlone ="select dsp_logistic.cs_belong.*,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.* from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type' ";
            if((property_exists($param,'startdate'))&&(property_exists($param,'enddate'))){
               $startdate = $param->startdate;
               $enddate = $param->enddate;
               $sqlone.= " and write_date >='$startdate' and write_date <='$enddate' ";
            }

            if(property_exists($param,'departname')){
                $departname = $param->departname;
                $sqlone.= " and (build_department_name ='$departname' or build_organize_name ='$departname') ";
            }

            if(property_exists($param,'areamanager')){
                $areamanager = $param->areamanager;
                $sqlone.= " and build_user_name ='$areamanager' ";
            }

            if(property_exists($param,'orderstate')){
                $orderstate = $param->orderstate;
                $sqlone.= " and cs_info_state ='$orderstate' ";
            }

            if(property_exists($param,'order_id')){
                $order_id = $param->order_id;
                $sqlone.= " and dsp_logistic.cs_info.cs_id ='$order_id' ";
            }

            if(property_exists($param,'receiver_name')){
                $receiver_name = $param->receiver_name;
                $sqlone.= " and return_info_receiver_name ='$receiver_name' ";
            }

            if(property_exists($param,'yard')){
                $yard = $param->yard;
                //$sqlone.= " and goods_yard_name ='$yard' ";
            }

            if(property_exists($param,'couriernumber')){
                $couriernumber = $param->couriernumber;
                //$sqlone.= " and transfer_order_num ='$couriernumber' ";
            }

            $tableobj = Db::query($sqlone);

            /*查询产品详细信息*/
            $sqltwo = "select dsp_logistic.order_goods_manager.*,dsp_logistic.cs_info.*,dsp_logistic.product_info.*,dsp_logistic.product_brand.brand_name,dsp_logistic.product_type.product_type_name,dsp_logistic.unc_product.unc_product_name,dsp_logistic.order_goods_logistics.ogl_unc_product_id from dsp_logistic.cs_info ";
            $sqltwo .= "left join dsp_logistic.order_goods_manager on dsp_logistic.order_goods_manager.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_manager.product_info_id ";
            $sqltwo .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
            $sqltwo .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
            $sqltwo .= "left join dsp_logistic.order_goods_logistics on dsp_logistic.order_goods_logistics.order_goods_manager_id = dsp_logistic.order_goods_manager.order_goods_manager_id ";
            $sqltwo .= "left join dsp_logistic.unc_product on dsp_logistic.unc_product.unc_product_id = dsp_logistic.order_goods_logistics.ogl_unc_product_id ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' and dsp_logistic.product_info.product_info_id !='-1' ";

            /*运费付费模式*/
            if(property_exists($param,'freightmode')){
                $freightmode = intval($param->freightmode);
                $sqltwo .= "and dsp_logistic.delivery_info.transfer_fee_mode = '$freightmode' ";
            }

            /*产品分类*/
            if(property_exists($param,'productclass')){
                $productclass = intval($param->productclass);
                $sqltwo .= "and dsp_logistic.product_type.product_type_id = '$productclass' ";
            }

            /*品牌*/
            if(property_exists($param,'brand')){
                $brand = intval($param->brand);
                $sqltwo .= "and dsp_logistic.product_brand.brand_id = '$brand' ";
            }

            /*产品型号*/
            if(property_exists($param,'producttype')){
                $producttype = $param->producttype;
                $sqltwo .= "and dsp_logistic.product_info.model = '$producttype' ";
            }

            /*生产地*/
            if(property_exists($param,'productarea')){
                $productarea = intval($param->productarea);
                $sqltwo .= "and dsp_logistic.product_place.place_id = '$productarea' ";
            }

            /*非常规产品*/
            if(property_exists($param,'customproduct')){
                $customproduct = intval($param->customproduct);
                $sqltwo .= "and dsp_logistic.unc_product.unc_product_id = '$customproduct' ";
            }

            $listobj = Db::query($sqltwo);

            for($item=0; $item<count($tableobj);$item++){
                $productlist = array();
                for($listitem=0;$listitem<count($listobj);$listitem++){
                    if($tableobj[$item]['cs_id'] == $listobj[$listitem]['cs_id']){
                        $productlist[] = $listobj[$listitem];
                    }
                }
                $tableobj[$item]['productlist'] = $productlist;
            }

            return $tableobj;
        }

        /*根据流水号和类型查询需打印的更换(0x01)/代用(0x06)/维修(0x04)/退货(0x03)/借样(0x02)确认单*/
        public static function queryprintcsinfoorder($cs_id,$type){
            $sqlone ="select dsp_logistic.cs_belong.*,dsp_logistic.cs_info.*,";
            if($type != 0x03){
                $sqlone .= "dsp_logistic.delivery_info.*,";
            }
            $sqlone .= "dsp_logistic.custom_info.*,";
            if($type != 0x02){
                $sqlone .= "dsp_logistic.return_info.* ";
            }
            $sqlone .= "from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            if($type != 0x03){
                $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            }
            if($type != 0x02){
                $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            }
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type' and dsp_logistic.cs_info.cs_id='$cs_id'";
            $tableobj = Db::query($sqlone);

            /*查询产品详细信息*/
            $sqltwo = "select dsp_logistic.order_goods_manager.*,dsp_logistic.cs_info.*,dsp_logistic.product_info.*,dsp_logistic.product_brand.brand_name,dsp_logistic.product_type.product_type_name from dsp_logistic.cs_info ";
            $sqltwo .= "left join dsp_logistic.order_goods_manager on dsp_logistic.order_goods_manager.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_manager.product_info_id ";
            $sqltwo .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
            $sqltwo .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' and dsp_logistic.cs_info.cs_id='$cs_id' and dsp_logistic.product_info.product_info_id !='-1' ";
            $listobj = Db::query($sqltwo);

            for($item=0; $item<count($tableobj);$item++){
                $productlist = array();
                for($listitem=0;$listitem<count($listobj);$listitem++){
                    if($tableobj[$item]['cs_id'] == $listobj[$listitem]['cs_id']){
                        $productlist[] = $listobj[$listitem];
                    }
                }
                $tableobj[$item]['productlist'] = $productlist;
            }

            return $tableobj;
        }

        /*根据流水号查询需打印的非定型产品订货单*/
        public static function queryprintuncofginfoorder($cs_id){
            $sqlone ="select dsp_logistic.order_goods_cs_info.*,dsp_logistic.unc_ofg_info.* from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
            $sqlone .= "where dsp_logistic.order_goods_cs_info.cs_id='$cs_id' ";
            $tableobj = Db::query($sqlone);

            $sqltwo = "select dsp_logistic.order_goods_cs_info.unc_ofg_info_id,dsp_logistic.unc_ofg_info.uoi_id,dsp_logistic.unc_ofg_detail.*,dsp_logistic.product_info.* ";
            $sqltwo .= "from dsp_logistic.order_goods_cs_info ";
            $sqltwo .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
            $sqltwo .= "left join dsp_logistic.unc_ofg_detail on dsp_logistic.unc_ofg_detail.unc_ofg_info_id = dsp_logistic.unc_ofg_info.uoi_id ";
            $sqltwo .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.unc_ofg_detail.product_info_id ";
            $sqltwo .= "where dsp_logistic.order_goods_cs_info.cs_id='$cs_id' ";
            $listobj = Db::query($sqltwo);

            for($item=0; $item<count($tableobj);$item++){
                $productlist = array();
                for($listitem=0;$listitem<count($listobj);$listitem++){
                    if($tableobj[$item]['unc_ofg_info_id'] == $listobj[$listitem]['unc_ofg_info_id']){
                        $productlist[] = $listobj[$listitem];
                    }
                }
                $tableobj[$item]['productlist'] = $productlist;
            }

            return $tableobj;
        }

        /*打印更换,代用，借样，维修，退货确认单*/
        public static function printreplaceconfirmorder($file_name,$file_extend,$template_name,$ret){
            $root_url = $_SERVER['DOCUMENT_ROOT'];
            $file_name = iconv("utf-8","gb2312",$file_name);
            $template_name = iconv("utf-8","gb2312",$template_name);

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($root_url."/templates/".$template_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('sheet0');
            $objPHPExcel->getActiveSheet()->setCellValue('C3', $ret['build_department_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G3', $ret['build_user_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C4', $ret['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('K4', $ret['company_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $ret['company_address']);
            $objPHPExcel->getActiveSheet()->setCellValue('C6', $ret['legal_representative']);
            $objPHPExcel->getActiveSheet()->setCellValue('I6', $ret['legal_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('C7', $ret['company_contact']);
            $objPHPExcel->getActiveSheet()->setCellValue('I7', $ret['company_contact_phone']);

            $objPHPExcel->getActiveSheet()->setCellValue('C9', $ret['delivery_info_receiver_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('I9', $ret['is_insure']);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $ret['receiver_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('I10', $ret['insure_amout']);
            $objPHPExcel->getActiveSheet()->setCellValue('C11', $ret['goods_yard_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('I11', $ret['is_sign']);
            $objPHPExcel->getActiveSheet()->setCellValue('C12', $ret['receiver_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('I12', $ret['has_contract']);
            $objPHPExcel->getActiveSheet()->setCellValue('C13', $ret['receiver_address']);
            $objPHPExcel->getActiveSheet()->setCellValue('C14', $ret['order_delivery_require']);

            $objPHPExcel->getActiveSheet()->setCellValue('C17', $ret['return_info_goods_yard_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C18', $ret['return_info_goods_yard_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('C19', $ret['return_info_receiver_address']);
            $objPHPExcel->getActiveSheet()->setCellValue('C20', $ret['return_order_num']);

            $productlist = $ret['productlist'];
            for($item=22;$item<(count($productlist)+22);$item++){
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$item, $productlist[$item-22]['product_type_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$item, $productlist[$item-22]['brand_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$item, $productlist[$item-22]['product_info_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$item, $productlist[$item-22]['model']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$item, $productlist[$item-22]['specification']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$item, $productlist[$item-22]['uint']);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$item, $productlist[$item-22]['product_number']);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$item, $productlist[$item-22]['bar_code']);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-22]['back_date']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$item, $productlist[$item-22]['replace_reason']);
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$file_name.'.'.$file_extend.'"');
            header('Cache-Control: max-age=0');
            ob_clean();  //关键
            flush();     //关键
            if($file_extend == 'xlsx'){
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
            }else if($file_extend == 'xls'){
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            }

            $objWriter->save("php://output");  /*输出至浏览器*/
            exit;        //关键
        }

        /*打印非定型产品订货单*/
        public static function printuncordergoods($file_name,$file_extend,$template_name,$ret){
            $root_url = $_SERVER['DOCUMENT_ROOT'];
            $file_name = iconv("utf-8","gb2312",$file_name);
            $template_name = iconv("utf-8","gb2312",$template_name);

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($root_url."/templates/".$template_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('sheet0');
            $objPHPExcel->getActiveSheet()->setCellValue('C3', $ret[0]['uoi_manual_ofg_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('H3', $ret[0]['uoi_custom_name']);
            //$objPHPExcel->getActiveSheet()->setCellValue('L3', $ret['uoi_custom_name']);
            //$objPHPExcel->getActiveSheet()->setCellValue('C4', $ret['uoi_manual_ofg_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('H4', $ret[0]['uoi_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('L4', $ret[0]['uoi_to_place']);

            $productlist = $ret[0]['productlist'];
            for($item=7;$item<(count($productlist)+7);$item++){
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$item, $productlist[$item-7]['model']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$item, $productlist[$item-7]['uod_count']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$item, $productlist[$item-7]['uod_unit']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$item, $productlist[$item-7]['uod_requirement']);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-7]['uod_delivery_date']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$item, $productlist[$item-7]['uod_comment']);
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$file_name.'.'.$file_extend.'"');
            header('Cache-Control: max-age=0');
            ob_clean();  //关键
            flush();     //关键
            if($file_extend == 'xlsx'){
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
            }else if($file_extend == 'xls'){
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            }

            $objWriter->save("php://output");  /*输出至浏览器*/
            exit;        //关键
        }


        /*获取非常规产品 unc_product*/
        public static function getuncproduct(){
            $sql = "SELECT * FROM dsp_logistic.unc_product";
            return Db::query($sql);
        }
        /*订货确认单 order_goods_cs_info*/
        public static function updateordergoodscsinfo($info){
            $cs_id = $info['cs_id'];
            $ofg_info_id = $info['ofg_info_id'];
            $fee_info_id = $info['fee_info_id'];
            $delivery_date_reply = $info['delivery_date_reply'];
            $unc_ofg_info_id = $info['unc_ofg_info_id'];
            $consult_sheet_file = $info['consult_sheet_file'];
            $delivered_total = $info['delivered_total'];
            $delivered_pa = $info['delivered_pa'];
            $delivered_conference = $info['delivered_conference'];
            $delivered_customization = $info['delivered_customization'];

            $delivered_record = $info['delivered_record'];
            $delivered_metro = $info['delivered_metro'];
            $delivered_aux = $info['delivered_aux'];
            $delivered_gift = $info['delivered_gift'];
            $delivered_album = $info['delivered_album'];
            $product_number = $info['product_number'];
            $order_date = $info['order_date'];
            $sql_value ="'{$cs_id}','{$ofg_info_id}','{$fee_info_id}','{$delivery_date_reply}','{$unc_ofg_info_id}','{$consult_sheet_file}','{$delivered_total}',";
            $sql_value .= "'{$delivered_pa}','{$delivered_conference}','{$delivered_customization}','{$delivered_record}','{$delivered_metro}','{$delivered_aux}',";
            $sql_value .= "'{$delivered_gift}','{$delivered_album}','{$product_number}','{$order_date}'";
            $sql = "INSERT INTO dsp_logistic.order_goods_cs_info (cs_id,ofg_info_id,fee_info_id,delivery_date_reply,unc_ofg_info_id,consult_sheet_file,";
            $sql .= "delivered_total,delivered_pa,delivered_conference,delivered_customization,delivered_record,delivered_metro,delivered_aux,";
            $sql .= "delivered_gift,delivered_album,product_number,order_date) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE cs_id = '{$cs_id}',ofg_info_id = '{$ofg_info_id}',fee_info_id = '{$fee_info_id}',delivery_date_reply = '{$delivery_date_reply}'";
            $sql .= ",unc_ofg_info_id = '{$unc_ofg_info_id}',consult_sheet_file = '{$consult_sheet_file}',delivered_total = '{$delivered_total}'";
            $sql .",delivered_pa = '{$delivered_pa}',delivered_conference = '{$delivered_conference}',delivered_customization = '{$delivered_customization}'";
            $sql .= ",delivered_record = '{$delivered_record}',delivered_metro = '{$delivered_metro}',delivered_aux = '{$delivered_aux}'";
            $sql .= ",delivered_gift = '{$delivered_gift}',delivered_album = '{$delivered_album}',product_number = '{$product_number}',order_date = '{$order_date}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*订单信息 ofg_info*/
        public static function updateofginfo($info){
            $ofg_info_id = $info['ofg_info_id'];
            $receiver_name = $info['receiver_name'];
            $receiver_phone = $info['receiver_phone'];
            $receiver_address = $info['receiver_address'];
            $user_id = $info['user_id'];
            $sql_value ="'{$ofg_info_id}','{$receiver_name}','{$receiver_phone}','{$receiver_address}','{$user_id}'";
            $sql = "INSERT INTO dsp_logistic.ofg_info (ofg_info_id,receiver_name,receiver_phone,receiver_address,user_id) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE receiver_name = '{$receiver_name}',receiver_phone = '{$receiver_phone}',receiver_address = '{$receiver_address}'";
            $sql .= ",user_id = '{$user_id}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }

        /*费用情况 fee_info*/
        public static function updatefeeinfo($info){
            $fee_info_id = $info['fee_info_id'];
            $customization_fee = $info['customization_fee'];
            $transfer_fee = $info['transfer_fee'];
            $transfer_mode = $info['transfer_mode'];
            $comment = $info['comment'];

            $sql_value ="'{$fee_info_id}','{$customization_fee}','{$transfer_fee}','{$transfer_mode}','{$comment}'";
            $sql = "INSERT INTO dsp_logistic.fee_info (fee_info_id,customization_fee,transfer_fee,transfer_mode,comment) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE customization_fee = '{$customization_fee}',transfer_fee = '{$transfer_fee}',transfer_mode = '{$transfer_mode}'";
            $sql .= ",comment = '{$comment}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*新增 订货确认单 清单 order_goods_cs_undeliver_goods_info*/
        public static function updateogcugi($info){
            $ogcugi_id = $info['ogcugi_id'];
            $cs_id = $info['cs_id'];
            $product_info_id = $info['product_info_id'];
            $ogcugi_count = $info['ogcugi_count'];
            $ogcugi_product_state = $info['ogcugi_product_state'];
            $ogcugi_comment = $info['ogcugi_comment'];
            $ogcugi_unit = $info['ogcugi_unit'];
            $sql_value ="'{$ogcugi_id}','{$cs_id}','{$product_info_id}','{$ogcugi_count}','{$ogcugi_product_state}','{$ogcugi_comment}','{$ogcugi_unit}'";
            $sql = "INSERT INTO dsp_logistic.order_goods_cs_undeliver_goods_info (ogcugi_id,cs_id,product_info_id,ogcugi_count,ogcugi_product_state,ogcugi_comment,ogcugi_unit) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE cs_id = '{$cs_id}',product_info_id = '{$product_info_id}',ogcugi_count = '{$ogcugi_count}'";
            $sql .= ",ogcugi_product_state = '{$ogcugi_product_state}',ogcugi_comment = '{$ogcugi_comment}',ogcugi_unit = '{$ogcugi_unit}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }

        public static function getproductplace($place_id){
            $sqlleader = "SELECT * FROM dsp_logistic.product_place WHERE place_id = '{$place_id}'LIMIT 1";
            return Db::query($sqlleader);
        }
        /*物流信息 logistics_info*/
        public static function updatelogisticsinfo($info){
            $logistics_id = $info['logistics_id'];
            $cs_id = $info['cs_id'];
            $goods_yard_name = $info['goods_yard_name'];
            $transfer_order_num = $info['transfer_order_num'];
            $delivery_date = $info['delivery_date'];
            $count = $info['count'];
            $user_id = $info['user_id'];
            $time_stamp = $info['time_stamp'];
            $sql_value ="'{$logistics_id}','{$cs_id}','{$goods_yard_name}','{$transfer_order_num}','{$delivery_date}','{$count}','{$user_id}','{$time_stamp}'";
            $sql = "INSERT INTO dsp_logistic.logistics_info (logistics_id,cs_id,goods_yard_name,transfer_order_num,delivery_date,count,user_id,time_stamp) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE cs_id = '{$cs_id}',goods_yard_name = '{$goods_yard_name}',transfer_order_num = '{$transfer_order_num}',delivery_date = '{$delivery_date}'";
            $sql .= ",count = '{$count}',user_id = '{$user_id}',time_stamp = '{$time_stamp}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*根据cs_id 获取走流程确认单的所有信息*/
        public static function getallcsinfobycsid($cs_id)
        {

            $sqlone ="select dsp_logistic.cs_belong.*,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.*,";
            $sqlone .= "dsp_logistic.custom_info.*,dsp_logistic.return_info.*,dsp_logistic.logistics_info.* from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_id='$cs_id' ";
            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;
            //获取审批表
            $allcsinfo = $tableobj[0];
            $cs_examine_info = Array();
            $examine_ids = Array();
            $allcsinfo[''].explode(',',$examine_ids);
            if(count($examine_ids)>0)
            {
                foreach ($examine_ids as $id )
                {
                    $exmine = \app\index\model\Admin::getclassinfobyproperty('cs_examine','cs_examine_id',$examine_ids[$id]);
                    if(!empty($exmine))
                        $cs_examine_info[]=$exmine[0];
                }
            }
            $allcsinfo['cs_examine_info'] = $cs_examine_info;


        }

    }
?>
<?php
	namespace app\index\model;
	use think\Db;
	use PHPExcel_IOFactory;
	use PHPExcel;
    use PHPExcel_RichText;
    use think\Exception;
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
            $sql.= " where $property = '$value'";
            $tableobj = Db::query($sql);
            if(!empty($tableobj)){
                return $tableobj;
            }
        }

		/*查询订货确认单*/
		public static function querygoodsorderinfo(...$args){
            $totalargs = count($args);
            $organizename = $args[0];
            $departmentname = $args[1];
            $areamanager = $args[2];
            //$type = $args[3];
            $pagenum = intval($args[4]?$args[4]:1);
            $length = intval($args[5]);

            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
            $sqlone .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
            $sqlone .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
            //   $sqltwo .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";

            $sqloneCondition = "where dsp_logistic.order_goods_cs_info.cs_id > 0 ";
            if($organizename != "")
            {
                $sqloneCondition .= "and build_organize_name='$organizename' ";
            }
            if($departmentname != "")
            {
                $sqloneCondition .= "and build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                $sqloneCondition .= "and build_user_name='$areamanager' ";
            }

            if($totalargs == 7){
                if($args[6]['areamanager'] != "" && $areamanager == ""){
                    $areamanger1 = $args[6]['areamanager'];
                    $sqloneCondition.= " and build_user_name = '$areamanger1'";
                }
                if($args[6]['departmentname'] != "" && $departmentname == ""){
                    $departmentname1 = $args[6]['departmentname'];
                    $sqloneCondition.= " and  build_department_name LIKE '%$departmentname1%'";
                }
                if($args[6]['organizename'] != "" && $organizename == ""){
                    $organizename1 = $args[6]['organizename'];
                    $sqloneCondition.= " and build_organize_name ='$organizename1'";
                }
                $startdate = $args[6]['startdate'];
                $enddate = $args[6]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqloneCondition.= " and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate'";
                }else if($startdate != "" && $enddate == "" ){
                    $sqloneCondition.= " and cs_belong_create_time ='$startdate'";
                }else if($startdate == "" && $enddate != "" ){
                    $sqloneCondition.= " and cs_belong_create_time ='$enddate'";
                }
                if($args[6]['order_id'] != "")
                {
                    $cs_id = $args[6]['order_id'];
                    $sqloneCondition.= " and dsp_logistic.order_goods_cs_info.cs_id ='$cs_id'";
                }
                if($args[6]['orderstate'] != "")
                {
                    $cs_info_state = $args[6]['orderstate'];
                    $sqloneCondition.= " and cs_info_state ='$cs_info_state'";
                }
                if ($args[6]['freightmode'] != ""){
                    $transfer_mode = $args[6]['freightmode'];
                    $sqloneCondition.= " and dsp_logistic.fee_info.transfer_mode ='$transfer_mode'";
                }
                if ($args[6]['receiver_name'] != ""){
                    $receiver_name = $args[6]['receiver_name'];
                    $sqloneCondition.= " and receiver_name = '$receiver_name'";
                }
                if ($args[6]['couriernumber'] != "" || $args[6]['yard'] != ""){
                    //$sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                    if ($args[6]['couriernumber'] != ""){
                        $transfer_order_num = $args[6]['couriernumber'];
                        $sqloneCondition.= " and transfer_order_num = '$transfer_order_num'";
                    }
                    if ($args[6]['yard'] != ""){
                        $goods_yard_name = $args[6]['yard'];
                        $sqloneCondition.= " and goods_yard_name = '$goods_yard_name'";
                    }
                }


                /*if($type == 2||$type == 5) //借样和配件没有返货信息
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
                }*/
            }
            //return $sqlone;
            $sqlone = $sqlone.$sqloneCondition;
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
            $sqltwo_h ="select  dsp_logistic.cs_belong.* ,dsp_logistic.order_goods_cs_info.*,dsp_logistic.fee_info.transfer_mode,dsp_logistic.ofg_info.receiver_name";
            $sqltwo_h .= ",dsp_logistic.logistics_info.delivery_date from dsp_logistic.order_goods_cs_info ";
            $sqltwo = "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
            $sqltwo .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
            $sqltwo .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
            $sqltwo .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqltwoCondition = "where dsp_logistic.order_goods_cs_info.cs_id like '%%' ";
            if($organizename != "")
            {
                $sqltwoCondition .= "and build_organize_name='$organizename' ";
            }
            if($departmentname != "")
            {
                $sqltwoCondition .= "and build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                $sqltwoCondition .= "and build_user_name='$areamanager' ";
            }
            if($totalargs == 7){
                if($args[6]['areamanager'] != "" && $areamanager == ""){
                    $areamanger1 = $args[6]['areamanager'];
                    $sqltwoCondition .= " and build_user_name = '$areamanger1'";
                }
                if($args[6]['departmentname'] != "" && $departmentname == ""){
                    $departmentname1 = $args[6]['departmentname'];
                    $sqltwoCondition .= " and build_department_name = '$departmentname1'";
                }
                if($args[6]['organizename'] != "" && $organizename == ""){
                    $organizename1 = $args[6]['organizename'];
                    $sqltwoCondition .= " and build_organize_name ='$organizename1'";
                }
                $startdate = $args[6]['startdate'];
                $enddate = $args[6]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqltwoCondition .= " and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate'";
                }else if($startdate != "" && $enddate == "" ){
                    $sqltwoCondition .= " and cs_belong_create_time ='$startdate'";
                }else if($startdate == "" && $enddate != "" ){
                    $sqltwoCondition .= " and cs_belong_create_time ='$enddate'";
                }
                if($args[6]['order_id'] != "")
                {
                    $cs_id = $args[6]['order_id'];
                    $sqltwoCondition .= " and dsp_logistic.order_goods_cs_info.cs_id ='$cs_id'";
                }
                if($args[6]['orderstate'] != "")
                {
                    $cs_info_state = $args[6]['orderstate'];
                    $sqltwoCondition .= " and cs_info_state ='$cs_info_state'";
                }
                if ($args[6]['freightmode'] != ""){
                    $transfer_mode = $args[6]['freightmode'];
                    $sqltwoCondition .=" and dsp_logistic.fee_info.transfer_mode ='$transfer_mode'";
                }
                if ($args[6]['receiver_name'] != ""){
                    $receiver_name = $args[6]['receiver_name'];
                    $sqltwoCondition .= " and receiver_name = '$receiver_name'";
                }
                if ($args[6]['couriernumber'] != "" || $args[6]['yard'] != ""){
                    //$sqltwo_h .= ",dsp_logistic.logistics_info.delivery_date from dsp_logistic.order_goods_cs_info ";
                    //$sqltwo .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                    if ($args[6]['couriernumber'] != ""){
                        $transfer_order_num = $args[6]['couriernumber'];
                        $sqltwoCondition .= " and transfer_order_num = '$transfer_order_num'";
                    }
                    if ($args[6]['yard'] != ""){
                        $goods_yard_name = $args[6]['yard'];
                        $sqltwoCondition .= " and goods_yard_name = '$goods_yard_name'";
                    }
                }else{
                    //$sqltwo_h .= " from dsp_logistic.order_goods_cs_info ";
                }

            }
            $sqltwoCondition .= "order By dsp_logistic.order_goods_cs_info.cs_id DESC limit {$offset},{$length} ;";
            $sql = $sqltwo_h.$sqltwo.$sqltwoCondition;
            $tableobj = Db::query($sql);
            if(!empty($tableobj)){
                $tableobjcount = count($tableobj);
                for ($i = 0;$i < $tableobjcount;$i++)
                {
                    /*if($type == 2||$type == 5) //借样和配件没有返货信息
                    {
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];
                    }
                    else
                    {
                        //$tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                        $tableobj[$i]["receiver_name"] = $tableobj[$i]["return_info_receiver_name"];
                    }*/

                    $tableobj[$i]["receiver_name"] = $tableobj[$i]["receiver_name"];//+++++
                    //$tableobj[$i]['write_date'] = $tableobj[$i]["order_date"];
                    $state = $tableobj[$i]["cs_info_state"];

                    if($state == 0)
                        $tableobj[$i]["cs_info_state"] = "";
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
                    elseif ($state == 6)
                    {
                        $tableobj[$i]["cs_info_state"] = "缺货";
                    }
                   // $tableobj[$i]["cs_info_state"] = "1";

                    $tableobj[$i]["transfer_fee_mode"]= self::parsefreightmode($tableobj[$i]["transfer_mode"]) ;


                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
			//return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
		}
        /*查询订货确认单 --- 物流，财务全部查询  2018-9-3 */
        public static function querygoodsorderinfo_ex(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $totalargs = count($args);

            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            if($totalargs == 3)
            {
                $cs_info_state = $args[2];
                $sqlone.=" where cs_info_state ='$cs_info_state'";
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

            $sql_all = "select  dsp_logistic.order_goods_cs_info.* from dsp_logistic.order_goods_cs_info ";
            if($totalargs == 3)
            {
                $cs_info_state = $args[2];
                $sql_all.=" where cs_info_state ='$cs_info_state' ";
            }
            $sql_all.="order By dsp_logistic.order_goods_cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select  dsp_logistic.cs_belong.* ,dsp_logistic.fee_info.transfer_mode,dsp_logistic.ofg_info.receiver_name";
                $sqlsub .= ",dsp_logistic.logistics_info.delivery_date from dsp_logistic.order_goods_cs_info ";
                $sqlsub .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
                $sqlsub .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id  ";
                $sqlsub .= "where dsp_logistic.order_goods_cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['receiver_name'] = $obj[0]['receiver_name'];
                $allOrder[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $allOrder[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $allOrder[$i]['build_user_name'] = $obj[0]['build_user_name'];
                $allOrder[$i]['cs_belong_id'] = $obj[0]['cs_belong_id'];

                $state = $allOrder[$i]["cs_info_state"];

                if($state == 0)
                    $allOrder[$i]["cs_info_state"] = "";
                elseif ($state == 1)
                {
                    $allOrder[$i]["cs_info_state"] = "处理中";
                }
                elseif ($state == 2)
                {
                    $allOrder[$i]["cs_info_state"] = "已完成";
                }
                elseif ($state == 3)
                {
                    $allOrder[$i]["cs_info_state"] = "取消";
                }
                elseif ($state == 4)
                {
                    $allOrder[$i]["cs_info_state"] = "备货";
                }
                elseif ($state == 6)
                {
                    $allOrder[$i]["cs_info_state"] = "缺货";
                }

                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[0]["transfer_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据销售部全部查询  2018-9-3 */
        public static function querygoodsorderinfo_ex1(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $organizename = $args[2];
            $departmentname = $args[3];
            $areamanager = $args[4];

            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id  where ";
            $isFirst = true;
            if($organizename != "")
            {

                $sqlone .= "build_organize_name='$organizename' ";
                $isFirst = false;
            }
            if($departmentname != "")
            {
                if($isFirst)
                {
                    $sqlone .= " build_department_name='$departmentname' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                if($isFirst)
                {
                    $sqlone .= " build_user_name='$areamanager' ";
                }
                else
                    $sqlone .= "and  build_user_name='$areamanager'";

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

            $sql_all = "select  dsp_logistic.order_goods_cs_info.* ,dsp_logistic.cs_belong.* from dsp_logistic.order_goods_cs_info ";
            $sql_all .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id  where ";
            $isFirst = true;
            if($organizename != "")
            {
                $sql_all .= "build_organize_name='$organizename' ";
                $isFirst = false;
            }
            if($departmentname != "")
            {
                if($isFirst)
                {
                    $sql_all .= " build_department_name='$departmentname' ";
                    $isFirst = false;
                }
                else
                    $sql_all .= "and build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                if($isFirst)
                {
                    $sql_all .= " build_user_name='$areamanager' ";
                }
                else
                    $sql_all .= "and  build_user_name='$areamanager'";

            }
            $sql_all.="order By dsp_logistic.order_goods_cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.fee_info.transfer_mode,dsp_logistic.ofg_info.receiver_name";
                $sqlsub .= ",dsp_logistic.logistics_info.delivery_date from dsp_logistic.order_goods_cs_info ";
                $sqlsub .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
                $sqlsub .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.order_goods_cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['receiver_name'] = $obj[$index]['receiver_name'];


                $state = $allOrder[$i]["cs_info_state"];

                if($state == 0)
                    $allOrder[$i]["cs_info_state"] = "";
                elseif ($state == 1)
                {
                    $allOrder[$i]["cs_info_state"] = "处理中";
                }
                elseif ($state == 2)
                {
                    $allOrder[$i]["cs_info_state"] = "已完成";
                }
                elseif ($state == 3)
                {
                    $allOrder[$i]["cs_info_state"] = "取消";
                }
                elseif ($state == 4)
                {
                    $allOrder[$i]["cs_info_state"] = "备货";
                }
                elseif ($state == 6)
                {
                    $allOrder[$i]["cs_info_state"] = "缺货";
                }

                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[$index]["transfer_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据日期，部门，经理有一不为空，除订单状态外其他为空查询  2018-9-3 */
        public static function querygoodsorderinfo_ex2(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $organizename = $args[2];
            $departmentname = $args[3];
            $areamanager = $args[4];
            $queryInfo = $args[5];
            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $condition= \app\index\model\Admin::getgoodorderqueryconditon($organizename,$departmentname,$areamanager,$queryInfo);
            $sqlone .=$condition;
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

            $sql_all = "select  dsp_logistic.order_goods_cs_info.* ,dsp_logistic.cs_belong.cs_belong_id,dsp_logistic.cs_belong.cs_belong_create_time,";
            $sql_all .= "dsp_logistic.cs_belong.build_department_name,dsp_logistic.cs_belong.build_user_name from dsp_logistic.order_goods_cs_info ";
            $sql_all .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sql_all.=$condition;
            $sql_all.=" order By dsp_logistic.order_goods_cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.fee_info.transfer_mode,dsp_logistic.ofg_info.receiver_name";
                $sqlsub .= ",dsp_logistic.logistics_info.delivery_date from dsp_logistic.order_goods_cs_info ";
                $sqlsub .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
                $sqlsub .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.order_goods_cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['receiver_name'] = $obj[0]['receiver_name'];
                $state = $allOrder[$i]["cs_info_state"];

                if($state == 0)
                    $allOrder[$i]["cs_info_state"] = "";
                elseif ($state == 1)
                {
                    $allOrder[$i]["cs_info_state"] = "处理中";
                }
                elseif ($state == 2)
                {
                    $allOrder[$i]["cs_info_state"] = "已完成";
                }
                elseif ($state == 3)
                {
                    $allOrder[$i]["cs_info_state"] = "取消";
                }
                elseif ($state == 4)
                {
                    $allOrder[$i]["cs_info_state"] = "备货";
                }
                elseif ($state == 6)
                {
                    $allOrder[$i]["cs_info_state"] = "缺货";
                }

                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[0]["transfer_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据收货人不为空，除订单状态外其他为空查询  2018-9-3 */
        public static function querygoodsorderinfo_ex3(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $organizename = $args[2];
            $departmentname = $args[3];
            $areamanager = $args[4];
            $queryInfo = $args[5];

            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id";
            $condition= \app\index\model\Admin::getgoodorderqueryconditon($organizename,$departmentname,$areamanager,$queryInfo);
            $sqlone .=$condition;
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

            $sql_all = "select  dsp_logistic.order_goods_cs_info.* ,dsp_logistic.ofg_info.receiver_name from dsp_logistic.order_goods_cs_info ";
            $sql_all .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id";
            $sql_all.=$condition;
            $sql_all.=" order By dsp_logistic.order_goods_cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.fee_info.transfer_mode,dsp_logistic.cs_belong.cs_belong_id,dsp_logistic.cs_belong.cs_belong_create_time,";
                $sqlsub .= "dsp_logistic.cs_belong.build_department_name,dsp_logistic.cs_belong.build_user_name";
                $sqlsub .= ",dsp_logistic.logistics_info.delivery_date from dsp_logistic.order_goods_cs_info ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.order_goods_cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $allOrder[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $allOrder[$i]['build_user_name'] = $obj[0]['build_user_name'];
                $allOrder[$i]['cs_belong_id'] = $obj[0]['cs_belong_id'];


                $state = $allOrder[$i]["cs_info_state"];

                if($state == 0)
                    $allOrder[$i]["cs_info_state"] = "";
                elseif ($state == 1)
                {
                    $allOrder[$i]["cs_info_state"] = "处理中";
                }
                elseif ($state == 2)
                {
                    $allOrder[$i]["cs_info_state"] = "已完成";
                }
                elseif ($state == 3)
                {
                    $allOrder[$i]["cs_info_state"] = "取消";
                }
                elseif ($state == 4)
                {
                    $allOrder[$i]["cs_info_state"] = "备货";
                }
                elseif ($state == 6)
                {
                    $allOrder[$i]["cs_info_state"] = "缺货";
                }

                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[0]["transfer_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据货场，运单 有一不为空 ，除订单状态外其他为空查询  2018-9-3 */
        public static function querygoodsorderinfo_ex4(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $organizename = $args[2];
            $departmentname = $args[3];
            $areamanager = $args[4];
            $queryInfo = $args[5];

            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $condition= \app\index\model\Admin::getgoodorderqueryconditon($organizename,$departmentname,$areamanager,$queryInfo);
            $sqlone .=$condition;
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

            $sql_all = "select  dsp_logistic.order_goods_cs_info.* ,dsp_logistic.logistics_info.delivery_date from dsp_logistic.order_goods_cs_info ";
            $sql_all .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sql_all .=$condition;
            $sql_all.="order By dsp_logistic.order_goods_cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.fee_info.transfer_mode,dsp_logistic.ofg_info.receiver_name";
                $sqlsub .=",dsp_logistic.cs_belong.cs_belong_id,dsp_logistic.cs_belong.cs_belong_create_time,";
                $sqlsub .= "dsp_logistic.cs_belong.build_department_name,dsp_logistic.cs_belong.build_user_name";
                $sqlsub .= " from dsp_logistic.order_goods_cs_info ";
                $sqlsub .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
                $sqlsub .= "left join dsp_logistic.unc_ofg_info on dsp_logistic.unc_ofg_info.uoi_id = dsp_logistic.order_goods_cs_info.unc_ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.order_goods_cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $allOrder[$i]['receiver_name'] = $obj[0]['receiver_name'];
                $allOrder[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $allOrder[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $allOrder[$i]['build_user_name'] = $obj[0]['build_user_name'];
                $allOrder[$i]['cs_belong_id'] = $obj[0]['cs_belong_id'];

                $state = $allOrder[$i]["cs_info_state"];
                if($state == 0)
                    $allOrder[$i]["cs_info_state"] = "";
                elseif ($state == 1)
                {
                    $allOrder[$i]["cs_info_state"] = "处理中";
                }
                elseif ($state == 2)
                {
                    $allOrder[$i]["cs_info_state"] = "已完成";
                }
                elseif ($state == 3)
                {
                    $allOrder[$i]["cs_info_state"] = "取消";
                }
                elseif ($state == 4)
                {
                    $allOrder[$i]["cs_info_state"] = "备货";
                }
                elseif ($state == 6)
                {
                    $allOrder[$i]["cs_info_state"] = "缺货";
                }

                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[0]["transfer_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据运费方式不为空 ，除订单状态外其他为空 查询  2018-9-3 */
        public static function querygoodsorderinfo_ex5(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $organizename = $args[2];
            $departmentname = $args[3];
            $areamanager = $args[4];
            $queryInfo = $args[5];

            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
            $condition= \app\index\model\Admin::getgoodorderqueryconditon($organizename,$departmentname,$areamanager,$queryInfo);
            $sqlone .=$condition;
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

            $sql_all = "select  dsp_logistic.order_goods_cs_info.* ,dsp_logistic.fee_info.transfer_mode from dsp_logistic.order_goods_cs_info ";
            $sql_all .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
            $sql_all.= $condition;
            $sql_all.=" order By dsp_logistic.order_goods_cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.logistics_info.delivery_date,dsp_logistic.ofg_info.receiver_name";
                $sqlsub .=",dsp_logistic.cs_belong.cs_belong_id,dsp_logistic.cs_belong.cs_belong_create_time,";
                $sqlsub .= "dsp_logistic.cs_belong.build_department_name,dsp_logistic.cs_belong.build_user_name";
                $sqlsub .= " from dsp_logistic.order_goods_cs_info ";
                $sqlsub .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.order_goods_cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['receiver_name'] = $obj[0]['receiver_name'];
                $allOrder[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $allOrder[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $allOrder[$i]['build_user_name'] = $obj[0]['build_user_name'];
                $allOrder[$i]['cs_belong_id'] = $obj[0]['cs_belong_id'];


                $state = $allOrder[$i]["cs_info_state"];

                if($state == 0)
                    $allOrder[$i]["cs_info_state"] = "";
                elseif ($state == 1)
                {
                    $allOrder[$i]["cs_info_state"] = "处理中";
                }
                elseif ($state == 2)
                {
                    $allOrder[$i]["cs_info_state"] = "已完成";
                }
                elseif ($state == 3)
                {
                    $allOrder[$i]["cs_info_state"] = "取消";
                }
                elseif ($state == 4)
                {
                    $allOrder[$i]["cs_info_state"] = "备货";
                }
                elseif ($state == 6)
                {
                    $allOrder[$i]["cs_info_state"] = "缺货";
                }

                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($allOrder[$i]["transfer_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        public static function getgoodorderqueryconditon($organizename,$departmentname,$areamanager ,$queryInfo )
        {
            $sqlone = "  where  ";
            $isFirst = true;
            if($organizename != "")
            {
                $sqlone .= "build_organize_name='$organizename' ";
                $isFirst = false;
            }
            if($departmentname != "")
            {
                if($isFirst)
                {
                    $sqlone .= "build_department_name='$departmentname' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                if($isFirst)
                {
                    $sqlone .= " build_user_name='$areamanager' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and  build_user_name='$areamanager'";
            }


            if ($queryInfo['areamanager']!= "" && $areamanager == "")
             {
                 $areamanger1 = $queryInfo['areamanager'];
                 if($isFirst)
                 {
                     $sqlone .= " build_user_name = '$areamanger1' ";
                     $isFirst = false;
                 }
                 else
                     $sqlone .= "and  build_user_name = '$areamanger1' ";
             }
            if ($queryInfo['departmentname']!= "" && $departmentname == "")
            {
                $departmentname1 = $queryInfo['departmentname'];
                if($isFirst)
                {
                    $sqlone .= " build_department_name = '$departmentname1' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and  build_department_name = '$departmentname1' ";
            }
            if ($queryInfo['organizename']!= "" && $organizename == "")
            {
                $organizename1 = $queryInfo['organizename'];
                if($isFirst)
                {
                    $sqlone .= " build_organize_name ='$organizename1' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and  build_organize_name ='$organizename1' ";
            }


            if($queryInfo['startdate']!= "" || $queryInfo['enddate'] != "")
            {
                if($queryInfo['startdate'] == "")
                    $queryInfo['startdate'] = $queryInfo['enddate'];
                if($queryInfo['enddate'] == "")
                    $queryInfo['enddate'] = $queryInfo['startdate'];
                $startdate = $queryInfo['startdate'];
                $enddate = $queryInfo['enddate'];
                if($isFirst)
                {
                    $sqlone .= " cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
            }
            if ($queryInfo['order_id']!= "")
            {
                $cs_id = $queryInfo['order_id'];
                if($isFirst)
                {
                    $sqlone .= " dsp_logistic.order_goods_cs_info.cs_id ='$cs_id' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and  dsp_logistic.order_goods_cs_info.cs_id ='$cs_id' ";
            }
            if ($queryInfo['orderstate']!= "")
            {
                $cs_info_state = $queryInfo['orderstate'];
                if($isFirst)
                {
                    $sqlone .= " cs_info_state ='$cs_info_state' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= " and cs_info_state ='$cs_info_state' ";
            }
            if ($queryInfo['freightmode']!= "")
            {
                $transfer_mode = $queryInfo['freightmode'];
                if($isFirst)
                {
                    $sqlone .= " dsp_logistic.fee_info.transfer_mode ='$transfer_mode' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and dsp_logistic.fee_info.transfer_mode ='$transfer_mode' ";
            }
            if ($queryInfo['receiver_name']!= "")
            {
                $receiver_name = $queryInfo['receiver_name'];
                if($isFirst)
                {
                    $sqlone .= " receiver_name = '$receiver_name' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and  receiver_name = '$receiver_name' ";
            }
            if ($queryInfo['couriernumber']!= "")
            {
                $transfer_order_num = $queryInfo['couriernumber'];
                if($isFirst)
                {
                    $sqlone .= " transfer_order_num = '$transfer_order_num' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and  transfer_order_num = '$transfer_order_num' ";
            }
            if ($queryInfo['yard']!= "")
            {
                $goods_yard_name = $queryInfo['yard'];
                if($isFirst)
                {
                    $sqlone .= " goods_yard_name = '$goods_yard_name' ";
                    $isFirst = false;
                }
                else
                    $sqlone .= "and  goods_yard_name = '$goods_yard_name' ";
            }

            if($isFirst)
                return "";
            return $sqlone;
        }

		/*查询维修，代用等订单 销售部查询时调用 */
		/*参数:organizename(总部门) departmentname(子部门) areamanager(经理名) rolename(角色名),type  page  limit queryinfo*/
		public static function querycsinfobysales(...$args){
			$totalargs = count($args);
			$organizename = $args[0];
            $departmentname = $args[1];
            $areamanager = $args[2];
            $rolename = $args[3];
			$type = $args[4];
			$pagenum = intval($args[5]?$args[5]:1);
			$length = intval($args[6]);

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
                $sqlone .= "and dsp_logistic.cs_belong.build_organize_name='$organizename' ";
            }
            if($departmentname != "")
            {
                $sqlone .= "and dsp_logistic.cs_belong.build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                $sqlone .= "and dsp_logistic.cs_belong.build_user_name='$areamanager' ";
            }
            else if($rolename != '部门总监'&&  $rolename != '部门助理'){
                //不是经理、部门总监、部门助理查时，不能查看未提交的单
                $sqlone .= "and dsp_logistic.cs_info.cs_info_state != 0 ";
            }

            if($totalargs == 8){
                if($args[7]['areamanager'] != "" && $areamanager == ""){
                    $areamanger1 = $args[7]['areamanager'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1'";
                }
                if($args[7]['departmentname'] != "" && $departmentname == ""){
                    $departmentname1 = $args[7]['departmentname'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_department_name ='$departmentname1'";
                }
                if($args[7]['organizename'] != "" && $organizename == ""){
                    $organizename1 = $args[7]['organizename'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_organize_name ='$organizename1'";
                }
                $startdate = $args[7]['startdate'];
                $enddate = $args[7]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqlone.= " and dsp_logistic.cs_belong.cs_belong_create_time >='$startdate' and dsp_logistic.cs_belong.cs_belong_create_time <='$enddate'";
                }
                else if($startdate != "")
                {
                    $sqlone.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                else if($enddate != "")
                {
                    $sqlone.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                if($args[7]['order_id'] != "")
                {
                    $cs_id = $args[7]['order_id'];
                    $sqlone.= " and dsp_logistic.cs_info.cs_id ='$cs_id'";
                }
                if($args[7]['orderstate'] != "")
                {
                    $cs_info_state = $args[7]['orderstate'];
                    $sqlone.= " and dsp_logistic.cs_info.cs_info_state ='$cs_info_state'";
                }
//                if($type == 2||$type == 5) //借样和配件没有返货信息
//                {
                    if($args[7]['receiver_name'] != "")
                    {
                        $delivery_info_receiver_name = $args[7]['receiver_name'];
                        $sqlone.= " and dsp_logistic.delivery_info.delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                    }

                    if($args[7]['yard'] != "")
                    {
                        $yard = $args[7]['yard'];
                        $sqlone.= " and dsp_logistic.delivery_info.delivery_info_goods_yard_name ='$yard' ";
                    }
//                }
//                else
//                {
//                    if($args[7]['receiver_name'] != "")
//                    {
//                        $return_info_receiver_name = $args[7]['receiver_name'];
//                        $sqlone.= " and return_info_receiver_name ='$return_info_receiver_name' ";
//                    }
//                    if($args[7]['yard'] != "")
//                    {
//                        $yard = $args[7]['yard'];
//                        $sqlone.= " and return_info_goods_yard_name ='$yard' ";
//                    }
//                }

                if($args[7]['couriernumber'] != "")
                {
                    $num = $args[7]['couriernumber'];
                    $sqlone.= " and dsp_logistic.logistics_info.transfer_order_num ='$num' ";
                }
                if(array_key_exists('freightmode',$args[7]))
                {
                    if($args[7]['freightmode'] != "")
                    {
                        $freightmode = $args[7]['freightmode'];
                        $sqlone.= " and dsp_logistic.delivery_info.transfer_fee_mode ='$freightmode' ";
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
            $sqltwo ="select  dsp_logistic.cs_belong.* ,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.transfer_fee_mode,dsp_logistic.logistics_info.transfer_order_num,dsp_logistic.logistics_info.delivery_date,";
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
                $sqltwo .= "and dsp_logistic.cs_belong.build_organize_name='$organizename' ";
            }
            if($departmentname != "")
            {
                $sqltwo .= "and dsp_logistic.cs_belong.build_department_name='$departmentname' ";
            }
            if($areamanager != "")
            {
                $sqltwo .= "and dsp_logistic.cs_belong.build_user_name='$areamanager' ";
            }
            else if($rolename != '部门总监'&&  $rolename != '部门助理'){
                //不是经理、部门总监、部门助理查时，不能查看未提交的单
                $sqltwo .= "and dsp_logistic.cs_info.cs_info_state != 0 ";
            }

			if($totalargs == 8){
				if($args[7]['areamanager'] != "" && $areamanager == ""){
					$areamanger1 = $args[7]['areamanager'];
					$sqltwo.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1'";
				}
                if($args[7]['departmentname'] != "" && $departmentname == ""){
                    $departmentname1 = $args[7]['departmentname'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_department_name ='$departmentname1'";
                }
                if($args[7]['organizename'] != "" && $organizename == ""){
                    $organizename1 = $args[7]['organizename'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_organize_name ='$organizename1'";
                }
                $startdate = $args[7]['startdate'];
                $enddate = $args[7]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqltwo.= " and dsp_logistic.cs_belong.cs_belong_create_time >='$startdate' and dsp_logistic.cs_belong.cs_belong_create_time <='$enddate'";
                }
                else if($startdate != "")
                {
                    $sqltwo.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                else if($enddate != "")
                {
                    $sqltwo.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                if($args[7]['order_id'] != "")
                {
                    $cs_id = $args[7]['order_id'];
                    $sqltwo.= " and dsp_logistic.cs_info.cs_id ='$cs_id'";
                }
                if($args[7]['orderstate'] != "")
                {
                    $cs_info_state = $args[7]['orderstate'];
                    $sqltwo.= " and dsp_logistic.cs_info.cs_info_state ='$cs_info_state'";
                }

                if($args[7]['receiver_name'] != "")
                {
                    $delivery_info_receiver_name = $args[7]['receiver_name'];
                    $sqltwo.= " and dsp_logistic.delivery_info.delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                }

                if($args[7]['yard'] != "")
                {
                    $yard = $args[7]['yard'];
                    $sqltwo.= " and dsp_logistic.delivery_info.delivery_info_goods_yard_name ='$yard' ";
                }

                if($args[7]['couriernumber'] != "")
                {
                    $num = $args[7]['couriernumber'];
                    $sqltwo.= " and dsp_logistic.logistics_info.transfer_order_num ='$num' ";
                }

                if(array_key_exists('freightmode',$args[7]))
                {
                    if($args[7]['freightmode'] != "")
                    {
                        $freightmode = $args[7]['freightmode'];
                        $sqltwo.= " and dsp_logistic.delivery_info.transfer_fee_mode ='$freightmode' ";
                    }
                }
			}
			$sqltwo .= "order By dsp_logistic.cs_info.write_date DESC limit {$offset},{$length} ;";
			$tableobj = Db::query($sqltwo);
			if(!empty($tableobj)){

                for ($i = 0;$i < count($tableobj);$i++)
                {

                    $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];

                    $tableobj[$i]["cs_info_state"] = self::parseorderstate($tableobj[$i]["cs_info_state"]);
                    $tableobj[$i]["transfer_fee_mode"] = self::parsefreightmode( $tableobj[$i]["transfer_fee_mode"]);

                    if($tableobj[$i]["complete_date"] == "2000-01-01 00:00:00")
                    {
                        $tableobj[$i]["complete_date"] ="";
                    }

                }
				return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
			}
		}
		/*chenshanqiang向物流表单插入数据*/
		public static function insertlogisticinfo($info){
		    try
            {
                $logistics_id = $info['logistics_id'];
                $cs_id = $info['cs_id'];
                $goods_yard_name = $info['goods_yard_name'];
                $transfer_order_num = $info['transfer_order_num'];
                $delivery_date = $info['delivery_date'];
                $count = $info['count'];
                $time_stamp = '2000-01-01 00:00:00';//date("Y-m-d");
                $sql_value ="'$logistics_id','$cs_id','$goods_yard_name','$transfer_order_num','$delivery_date','$count','$time_stamp'";
                $sql = "INSERT INTO dsp_logistic.logistics_info (logistics_id,cs_id,goods_yard_name,transfer_order_num,delivery_date,count,time_stamp) VALUES ({$sql_value})";
                $sqlret = Db::execute($sql);
                return $sqlret;
            }
            catch (Exception $ex)
            {
                return false;
            }

        }
        /*chenshanqiang查询物流表数据*/
		public static function querylogisticsinfo(...$args){
			$type = $args[0];
            $pagenum = intval($args[1]?$args[1]:1);
            $length = intval($args[2]);
			$sqlone = "select count(*) from dsp_logistic.logistics_info";
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
			/*$tableobj = Db::query($sqlone);*/
			$sqlone = "select * from dsp_logistic.logistics_info order By dsp_logistic.logistics_info.logistics_id DESC limit {$offset},{$length}";
			$tableobj = Db::query($sqlone);
			return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
		}
		 /*chenshanqiang更改物流表数据*/
		 public static function updatelogisticsinfofirst($args){
			$sql = "up count(*) from dsp_logistic.cs_info ";
			return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
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
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type' ";

            //不能查看未提交的单
            $sqlone .= "and dsp_logistic.cs_info.cs_info_state != 0 ";

            if($totalargs == 4){
                if($args[3]['areamanager'] != "" ){
                    $areamanger1 = $args[3]['areamanager'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1'";
                }
                if($args[3]['departname'] ){
                    $departmentname1 = $args[3]['departname'];
                    $sqlone.= " and (dsp_logistic.cs_belong.build_department_name ='$departmentname1' or dsp_logistic.cs_belong.build_organize_name ='$departmentname1') ";
                }
                $startdate = $args[3]['startdate'];
                $enddate = $args[3]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqlone.= " and dsp_logistic.cs_belong.cs_belong_create_time >='$startdate' and dsp_logistic.cs_belong.cs_belong_create_time <='$enddate' ";
                }
                else if($startdate != "")
                {
                    $sqlone.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                else if($enddate != "")
                {
                    $sqlone.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                if($args[3]['order_id'] != "")
                {
                    $cs_id = $args[3]['order_id'];
                    $sqlone.= " and dsp_logistic.cs_info.cs_id ='$cs_id' ";
                }
                if($args[3]['orderstate'] != "")
                {
                    $cs_info_state = $args[3]['orderstate'];
                    $sqlone.= " and dsp_logistic.cs_info.cs_info_state ='$cs_info_state' ";
                }

                if($args[3]['receiver_name'] != "")
                {
                    $delivery_info_receiver_name = $args[3]['receiver_name'];
                    $sqlone.= " and dsp_logistic.delivery_info.delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                }

                if($args[3]['yard'] != "")
                {
                    $yard = $args[3]['yard'];
                    $sqlone.= " and dsp_logistic.delivery_info.delivery_info_goods_yard_name ='$yard' ";
                }


                if($args[3]['couriernumber'] != "")
                {
                    $num = $args[3]['couriernumber'];
                    $sqlone.= " and dsp_logistic.logistics_info.transfer_order_num ='$num' ";
                }

                if(array_key_exists('freightmode',$args[3]))
                {
                    if($args[3]['freightmode'] != "")
                    {
                        $freightmode = $args[3]['freightmode'];
                        $sqlone.= " and dsp_logistic.delivery_info.transfer_fee_mode ='$freightmode' ";
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
            $sqltwo ="select  dsp_logistic.cs_belong.* ,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.transfer_fee_mode,";


            $sqltwo .="dsp_logistic.logistics_info.transfer_order_num,dsp_logistic.logistics_info.delivery_date,";

            $sqltwo .= "dsp_logistic.delivery_info.delivery_info_receiver_name,dsp_logistic.return_info.return_info_receiver_name from dsp_logistic.cs_info ";
            $sqltwo .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqltwo .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqltwo .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            $sqltwo .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' ";
            //不能查看未提交的单
            $sqltwo .= "and dsp_logistic.cs_info.cs_info_state != 0 ";
            if($totalargs == 4){
                if($args[3]['areamanager'] != ""){
                    $areamanger1 = $args[3]['areamanager'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1' ";
                }
                if($args[3]['departname'] != "" ){
                    $departmentname1 = $args[3]['departname'];
                    $sqltwo.= " and (dsp_logistic.cs_belong.build_department_name ='$departmentname1' or dsp_logistic.cs_belong.build_organize_name ='$departmentname1') ";
                }

                $startdate = $args[3]['startdate'];
                $enddate = $args[3]['enddate'];
                if($startdate != "" && $enddate != "" ){
                    $sqltwo.= " and dsp_logistic.cs_belong.cs_belong_create_time >='$startdate' and dsp_logistic.cs_belong.cs_belong_create_time <='$enddate' ";
                }
                else if($startdate != "")
                {
                    $sqltwo.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                else if($enddate != "")
                {
                    $sqltwo.= " and dsp_logistic.cs_belong.cs_belong_create_time ='$startdate'";
                }
                if($args[3]['order_id'] != "")
                {
                    $cs_id = $args[3]['order_id'];
                    $sqltwo.= " and dsp_logistic.cs_info.cs_id ='$cs_id' ";
                }
                if($args[3]['orderstate'] != "")
                {
                    $cs_info_state = $args[3]['orderstate'];
                    $sqltwo.= " and dsp_logistic.cs_info.cs_info_state ='$cs_info_state' ";
                }

                if($args[3]['receiver_name'] != "")
                {
                    $delivery_info_receiver_name = $args[3]['receiver_name'];
                    $sqltwo.= " and dsp_logistic.delivery_info.delivery_info_receiver_name ='$delivery_info_receiver_name' ";
                }

                if($args[3]['yard'] != "")
                {
                    $yard = $args[3]['yard'];
                    $sqltwo.= " and dsp_logistic.delivery_info.delivery_info_goods_yard_name ='$yard' ";
                }


                if($args[3]['couriernumber'] != "")
                {
                    $num = $args[3]['couriernumber'];
                    $sqltwo.= " and dsp_logistic.logistics_info.transfer_order_num ='$num' ";
                }

                if(array_key_exists('freightmode',$args[3]))
                {
                    if($args[3]['freightmode'] != "")
                    {
                        $freightmode = $args[3]['freightmode'];
                        $sqltwo.= " and dsp_logistic.delivery_info.transfer_fee_mode ='$freightmode' ";
                    }
                }
            }
            $sqltwo .= "order By dsp_logistic.cs_info.write_date DESC limit {$offset},{$length}";
            $tableobj = Db::query($sqltwo);
            if(!empty($tableobj)){
                for ($i = 0;$i < count($tableobj);$i++)
                {
                    $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];

                    $tableobj[$i]["cs_info_state"] = self::parseorderstate($tableobj[$i]["cs_info_state"]);
                    $tableobj[$i]["transfer_fee_mode"]= self::parsefreightmode($tableobj[$i]["transfer_fee_mode"]);
                    if($tableobj[$i]["complete_date"] == "2000-01-01 00:00:00")
                    {
                        $tableobj[$i]["complete_date"] ="";
                    }
                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
        }

        /*查询维修，代用等订单 --- 全部查询  2018-9-4 */
        public static function querycsInfomation_ex1(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $type = $args[2];
            $totalargs = count($args);

            $sqlone = "select count(*) from dsp_logistic.cs_info where dsp_logistic.cs_info.cs_info_type='$type' ";
            if($totalargs == 4)
            {
                $cs_info_state = $args[3];
                $sqlone .= "and dsp_logistic.cs_info.cs_info_state = '$cs_info_state' ";
            }
            else
                $sqlone .= "and dsp_logistic.cs_info.cs_info_state != 0 ";
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

            $sql_all = "select  dsp_logistic.cs_info.*  from dsp_logistic.cs_info where dsp_logistic.cs_info.cs_info_type='$type'  ";
            if($totalargs == 4)
            {
                $cs_info_state = $args[3];
                $sql_all .= "and dsp_logistic.cs_info.cs_info_state = '$cs_info_state' ";
            }
            else
                $sql_all .= "and dsp_logistic.cs_info.cs_info_state != 0 ";
            $sql_all.="order By dsp_logistic.cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.cs_belong.cs_belong_create_time,dsp_logistic.cs_belong.build_department_name,";
                $sqlsub .="dsp_logistic.cs_belong.build_user_name,dsp_logistic.delivery_info.transfer_fee_mode,";
                $sqlsub .="dsp_logistic.logistics_info.transfer_order_num,dsp_logistic.logistics_info.delivery_date,";
                $sqlsub .= "dsp_logistic.delivery_info.delivery_info_receiver_name from dsp_logistic.cs_info ";
                $sqlsub .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['receiver_name'] = $obj[0]['delivery_info_receiver_name'];
                $allOrder[$i]['transfer_order_num'] = $obj[0]['transfer_order_num'];
                $allOrder[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $allOrder[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $allOrder[$i]['build_user_name'] = $obj[0]['build_user_name'];

                $allOrder[$i]["cs_info_state"] = self::parseorderstate($allOrder[$i]["cs_info_state"]);
                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[0]["transfer_fee_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据日期，部门，经理有一不为空，除订单状态外其他为空查询  2018-9-3 */
        public static function querycsInfomation_ex2(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $type = $args[2];
            $organizename = $args[3];
            $departmentname = $args[4];
            $areamanager = $args[5];
            $rolename = $args[6];
            $queryInfo = $args[7];

            $sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone.="where dsp_logistic.cs_info.cs_info_type='$type' ";
            $condition= self::getcsInfomationqueryconditon($organizename,$departmentname,$areamanager,$rolename,$queryInfo);
            $sqlone .=$condition;
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

            $sql_all = "select  dsp_logistic.cs_info.* ,dsp_logistic.cs_belong.cs_belong_create_time,dsp_logistic.cs_belong.build_department_name,";
            $sql_all .="dsp_logistic.cs_belong.build_user_name from dsp_logistic.cs_info  ";
            $sql_all .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sql_all.="where dsp_logistic.cs_info.cs_info_type='$type' ";
            $condition= self::getcsInfomationqueryconditon($organizename,$departmentname,$areamanager,$rolename,$queryInfo);
            $sql_all .=$condition;
            $sql_all.="order By dsp_logistic.cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.delivery_info.transfer_fee_mode,";
                $sqlsub .="dsp_logistic.logistics_info.transfer_order_num,dsp_logistic.logistics_info.delivery_date,";
                $sqlsub .= "dsp_logistic.delivery_info.delivery_info_receiver_name from dsp_logistic.cs_info ";
                $sqlsub .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['receiver_name'] = $obj[0]['delivery_info_receiver_name'];
                $allOrder[$i]['transfer_order_num'] = $obj[0]['transfer_order_num'];



                $allOrder[$i]["cs_info_state"] = self::parseorderstate($allOrder[$i]["cs_info_state"]);
                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[0]["transfer_fee_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据收货人,运费方式有一不为空，除订单状态外其他为空查询  2018-9-3 */
        public static function querycsInfomation_ex3(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $type = $args[2];
            $organizename = $args[3];
            $departmentname = $args[4];
            $areamanager = $args[5];
            $rolename = $args[6];
            $queryInfo = $args[7];

            $sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone.="where dsp_logistic.cs_info.cs_info_type='$type' ";
            $condition= self::getcsInfomationqueryconditon($organizename,$departmentname,$areamanager,$rolename,$queryInfo);
            $sqlone .=$condition;
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

            $sql_all = "select  dsp_logistic.cs_info.* ,dsp_logistic.delivery_info.transfer_fee_mode,";
            $sql_all .="dsp_logistic.delivery_info.delivery_info_receiver_name from dsp_logistic.cs_info  ";
            $sql_all .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sql_all.="where dsp_logistic.cs_info.cs_info_type='$type' ";
            $condition= self::getcsInfomationqueryconditon($organizename,$departmentname,$areamanager,$rolename,$queryInfo);
            $sql_all .=$condition;
            $sql_all.="order By dsp_logistic.cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.cs_belong.cs_belong_create_time,dsp_logistic.cs_belong.build_department_name,";
                $sqlsub .="dsp_logistic.cs_belong.build_user_name,";
                $sqlsub .="dsp_logistic.logistics_info.transfer_order_num,dsp_logistic.logistics_info.delivery_date ";
                $sqlsub .= "from dsp_logistic.cs_info ";
                $sqlsub .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $index = count($obj)-1;
                $allOrder[$i]['delivery_date'] = $obj[$index]['delivery_date'];
                $allOrder[$i]['transfer_order_num'] = $obj[0]['transfer_order_num'];
                $allOrder[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $allOrder[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $allOrder[$i]['build_user_name'] = $obj[0]['build_user_name'];
                $allOrder[$i]['receiver_name'] =  $allOrder[$i]['delivery_info_receiver_name'];

                $allOrder[$i]["cs_info_state"] = self::parseorderstate($allOrder[$i]["cs_info_state"]);
                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($allOrder[$i]["transfer_fee_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        /*查询订货确认单 --- 根据货场，运单 有一不为空 ，除订单状态外其他为空查询  2018-9-3 */
        public static function querycsInfomation_ex4(...$args){
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);
            $type = $args[2];
            $organizename = $args[3];
            $departmentname = $args[4];
            $areamanager = $args[5];
            $rolename = $args[6];
            $queryInfo = $args[7];

            $sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone.="where dsp_logistic.cs_info.cs_info_type='$type' ";
            $condition= self::getcsInfomationqueryconditon($organizename,$departmentname,$areamanager,$rolename,$queryInfo);
            $sqlone .=$condition;
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

            $sql_all = "select  dsp_logistic.cs_info.* ,dsp_logistic.logistics_info.delivery_date,";
            $sql_all .="dsp_logistic.logistics_info.transfer_order_num from dsp_logistic.cs_info  ";
            $sql_all .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
            $sql_all.="where dsp_logistic.cs_info.cs_info_type='$type' ";
            $condition= self::getcsInfomationqueryconditon($organizename,$departmentname,$areamanager,$rolename,$queryInfo);
            $sql_all .=$condition;
            $sql_all.="order By dsp_logistic.cs_info.cs_id DESC limit {$offset},{$length} ";
            $allOrder = Db::query($sql_all);
            if(empty($allOrder))
            {
                return (array('code'=>0,'msg'=>'','count'=>0,'data'=>[]));
            }
            for ($i = 0;$i < count($allOrder);$i++)
            {
                $sqlsub ="select dsp_logistic.cs_belong.cs_belong_create_time,dsp_logistic.cs_belong.build_department_name,";
                $sqlsub .="dsp_logistic.cs_belong.build_user_name,dsp_logistic.delivery_info.transfer_fee_mode,";
                $sqlsub .= "dsp_logistic.delivery_info.delivery_info_receiver_name from dsp_logistic.cs_info ";
                $sqlsub .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
                $sqlsub .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlsub .= "where dsp_logistic.cs_info.cs_id = '{$allOrder[$i]['cs_id']}' ";
                $obj = Db::query($sqlsub);
                if(empty($obj))
                    continue;
                $allOrder[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $allOrder[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $allOrder[$i]['build_user_name'] = $obj[0]['build_user_name'];
                $allOrder[$i]['receiver_name'] =  $obj[0]['delivery_info_receiver_name'];

                $allOrder[$i]["cs_info_state"] = self::parseorderstate($allOrder[$i]["cs_info_state"]);
                $allOrder[$i]["transfer_fee_mode"]= self::parsefreightmode($obj[0]["transfer_fee_mode"]) ;

            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$allOrder));
        }

        public static function getcsInfomationqueryconditon($organizename,$departmentname,$areamanager ,$rolename,$queryInfo )
        {
            $sqlone = " ";
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
                $sqlone .= "and  build_user_name='$areamanager'";
                if($queryInfo['orderstate'] != "")
                {
                    $cs_info_state = $queryInfo['orderstate'];
                    $sqlone .= " and cs_info_state ='$cs_info_state' ";
                }
            }
            elseif ($rolename != '部门总监'&&  $rolename != '部门助理' && $queryInfo['orderstate'] == "")
            {
                $sqlone .= " and cs_info_state !=0 ";
            }
            elseif($queryInfo['orderstate'] != "")
            {
                $cs_info_state = $queryInfo['orderstate'];
                $sqlone .= " and cs_info_state ='$cs_info_state' ";
            }


            if ($queryInfo['areamanager']!= "" && $areamanager == "")
            {
                $areamanger1 = $queryInfo['areamanager'];
                $sqlone .= "and  build_user_name = '$areamanger1' ";
            }
            if ($queryInfo['departmentname']!= "" && $departmentname == "")
            {
                $departmentname1 = $queryInfo['departmentname'];
                $sqlone .= "and  build_department_name = '$departmentname1' ";
            }
            if ($queryInfo['organizename']!= "" && $organizename == "")
            {
                $organizename1 = $queryInfo['organizename'];
                $sqlone .= "and  build_organize_name ='$organizename1' ";
            }

            if($queryInfo['startdate']!= "" || $queryInfo['enddate'] != "")
            {
                if($queryInfo['startdate'] == "")
                    $queryInfo['startdate'] = $queryInfo['enddate'];
                if($queryInfo['enddate'] == "")
                    $queryInfo['enddate'] = $queryInfo['startdate'];
                $startdate = $queryInfo['startdate'];
                $enddate = $queryInfo['enddate'];
                $sqlone .= "and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
            }
            if ($queryInfo['order_id']!= "")
            {
                $cs_id = $queryInfo['order_id'];
                $sqlone .= "and  dsp_logistic.order_goods_cs_info.cs_id ='$cs_id' ";
            }
            if ($queryInfo['freightmode']!= "")
            {
                $transfer_mode = $queryInfo['freightmode'];
                $sqlone .= "and dsp_logistic.delivery_info.transfer_fee_mode ='$transfer_mode' ";
            }
            if ($queryInfo['receiver_name']!= "")
            {
                $receiver_name = $queryInfo['receiver_name'];
                $sqlone .= "and  delivery_info_receiver_name = '$receiver_name' ";
            }
            if ($queryInfo['couriernumber']!= "")
            {
                $transfer_order_num = $queryInfo['couriernumber'];
                $sqlone .= "and  transfer_order_num = '$transfer_order_num' ";
            }
            if ($queryInfo['yard']!= "")
            {
                $goods_yard_name = $queryInfo['yard'];
                    $sqlone .= "and  goods_yard_name = '$goods_yard_name' ";
            }
            return $sqlone;
        }

        public static function queryConfirmOrder($queryuserinfo,$page,$limit,$type,$queryInfo)
        {
            $organizename = "";
            if (array_key_exists('organizename',$queryuserinfo)){
                $organizename = $queryuserinfo["organizename"];
            }
            $departmentname = "";
            if (array_key_exists('departmentname',$queryuserinfo)){
                $departmentname = $queryuserinfo["departmentname"];
            }
            $areamanager = "";
            if (array_key_exists('areamanager',$queryuserinfo)){
                $areamanager = $queryuserinfo["areamanager"];
            }
            $rolename = $queryuserinfo['role_name'];

            if(isset($queryInfo)){
                $queryInfo["organizename"] = "";
                $queryInfo["departmentname"] = $queryInfo["departname"];
                $mode = self::getquerymode($queryInfo);
                if($mode == 0)
                {
                    if($queryuserinfo["isSales"])
                        $tablelist = self::querycsInfomation_ex1($page,$limit,$type);
                    else
                        $tablelist = self::querycsInfomation_ex2($page,$limit,$type,$organizename,$departmentname,$areamanager,$rolename,$queryInfo);
                }
                elseif($mode == 1)
                {

                    $tablelist = self::querycsInfomation_ex2($page,$limit,$type,$organizename,$departmentname,$areamanager,$rolename,$queryInfo);
                }
                elseif($mode == 2 && $queryuserinfo['isSales'])
                {
                    $tablelist = self::querycsInfomation_ex3($page,$limit,$type,$organizename,$departmentname,$areamanager,$rolename,$queryInfo);
                }
                elseif($mode == 3 && $queryuserinfo['isSales'])
                {
                    $tablelist = self::querycsInfomation_ex4($page,$limit,$type,$organizename,$departmentname,$areamanager,$rolename,$queryInfo);
                }
                elseif($mode == 4)
                {
                    if($queryuserinfo['isSales'])
                        $tablelist = self::querycsInfomation_ex1($page,$limit,$type,$queryInfo['orderstate']);
                    else
                        $tablelist = self::querycsInfomation_ex2($page,$limit,$type,$organizename,$departmentname,$areamanager,$rolename,$queryInfo);
                }
                else
                {

                    if($queryuserinfo["isSales"])
                        $tablelist = self::querycsInfomation($type,$page,$limit,$queryInfo);
                    else
                        $tablelist = self::querycsinfobysales($organizename,$departmentname,$areamanager,$rolename,$type,$page,$limit,$queryInfo);
                }
            }else{
                //没有查询条件
                if($queryuserinfo["isSales"])
                    $tablelist = self::querycsInfomation_ex1($page,$limit,$type);
                else
                {
                    $queryInfo = array();
                    $queryInfo['startdate'] = "";
                    $queryInfo['enddate'] = "";
                    $queryInfo['areamanager'] = "";
                    $queryInfo['orderstate'] = "";
                    $queryInfo['order_id'] = "";
                    $queryInfo['receiver_name'] = "";
                    $queryInfo['yard'] = "";
                    $queryInfo['couriernumber'] = "";
                    $queryInfo['freightmode'] = "";
                    $queryInfo["organizename"] = "";
                    $queryInfo["departmentname"] = "";
                    $tablelist = self::querycsInfomation_ex2($page,$limit,$type,$organizename,$departmentname,$areamanager,$rolename,$queryInfo);
                }

            }
            return $tablelist;
        }

        //根据条件返回查询方式
        public static function getquerymode($queryInfo)
        {
            if($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
                $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
                $queryInfo['orderstate'] == "" && $queryInfo['order_id'] == ""&&
                $queryInfo['receiver_name'] == "" && $queryInfo['yard'] == ""&&
                $queryInfo['couriernumber'] == "" && $queryInfo['freightmode'] == ""
            ) //都为空
                return 0;
            elseif (($queryInfo['startdate'] != "" || $queryInfo['enddate'] != ""||
                    $queryInfo['departmentname'] != "" || $queryInfo['areamanager'] != ""
                )&& $queryInfo['order_id'] == ""&&
                $queryInfo['receiver_name'] == "" && $queryInfo['yard'] == ""&&
                $queryInfo['couriernumber'] == "" && $queryInfo['freightmode'] == ""
            ) //日期，部门，经理有一不为空，除订单状态外其他为空
                return 1;
            elseif ($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
                $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
                $queryInfo['order_id'] == ""&&
                ($queryInfo['receiver_name'] != "" || $queryInfo['freightmode'] != "")&&
                $queryInfo['couriernumber'] == "" && $queryInfo['yard'] == ""
            )//收货人不为空，除订单状态外其他为空
                return 2;
            elseif ($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
                $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
                $queryInfo['order_id'] == ""&& $queryInfo['receiver_name'] == "" &&
                ($queryInfo['yard'] != "" || $queryInfo['couriernumber'] != "" )&&
                $queryInfo['freightmode'] == "") //货场，运单 有一不为空 ，除订单状态外其他为空
                return 3;
            elseif($queryInfo['startdate'] == "" && $queryInfo['enddate'] == ""&&
                $queryInfo['departmentname'] == "" && $queryInfo['areamanager'] == ""&&
                $queryInfo['orderstate'] != "" && $queryInfo['order_id'] == ""&&
                $queryInfo['receiver_name'] == "" && $queryInfo['yard'] == ""&&
                $queryInfo['couriernumber'] == "" && $queryInfo['freightmode'] == ""
            ) //订单状态不为空 ,其他为空
                return 4;

            return -1;
        }

        /*物流人员根据条件查询审批确认单，五个参数最少san个: type  page  limit queryinfo*/
        public static function logisticQueryApproveConfirmOrder(...$args){
            $totalargs = count($args);


            $type = $args[0];
            $pagenum = intval($args[1]?$args[1]:1);
            $length = intval($args[2]);

            if($totalargs == 3)
            {
                return self::logisticQueryApproveConfirmOrder_ex($type,$pagenum,$length);
            }

            $sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type'and cs_info_state = 1 and dsp_logistic.cs_info.complete_date <= '2000-01-01 00:00:00'";

            if($totalargs == 4){
                $sqlone.= self::approveConfirmQueryCondition($args[3]);
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
            $sqltwo ="select  dsp_logistic.cs_belong.* ,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.transfer_fee_mode,";
            $sqltwo .= "dsp_logistic.delivery_info.delivery_info_receiver_name from dsp_logistic.cs_info ";
            $sqltwo .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' and cs_info_state = 1 and dsp_logistic.cs_info.complete_date <= '2000-01-01 00:00:00'";
            if($totalargs == 4){
                $sqltwo.= self::approveConfirmQueryCondition($args[3]);
            }
            $sqltwo .= " order By dsp_logistic.cs_info.cs_id DESC limit {$offset},{$length} ;";
            $tableobj = Db::query($sqltwo);
            if(!empty($tableobj)){
                for ($i = 0;$i < count($tableobj);$i++)
                {
                    $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];

                    $tableobj[$i]["cs_info_state"] = self::parseorderstate($tableobj[$i]["cs_info_state"]);

                    $tableobj[$i]["serial_number"] = $i+1;
                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
        }

        public static function logisticQueryApproveConfirmOrder_ex(...$args){
            $type = $args[0];
            $pagenum = intval($args[1]?$args[1]:1);
            $length = intval($args[2]);

            $sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type'and cs_info_state = 1 and dsp_logistic.cs_info.complete_date <= '2000-01-01 00:00:00'";
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
            $sqltwo ="select dsp_logistic.cs_info.* from dsp_logistic.cs_info ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' and cs_info_state = 1 and dsp_logistic.cs_info.complete_date <= '2000-01-01 00:00:00'";
            $sqltwo .= " order By dsp_logistic.cs_info.cs_id DESC limit {$offset},{$length} ;";
            $tableobj = Db::query($sqltwo);
            if(empty($tableobj))
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>[]));

            for ($i = 0;$i < count($tableobj);$i++)
            {
                $sqlthree ="select  dsp_logistic.cs_belong.* ,dsp_logistic.delivery_info.transfer_fee_mode,";
                $sqlthree .= "dsp_logistic.delivery_info.delivery_info_receiver_name from dsp_logistic.cs_info ";
                $sqlthree .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
                $sqlthree .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
                $sqlthree .= "where dsp_logistic.cs_info.cs_id='{$tableobj[$i]['cs_id']}' ";
                $obj = Db::query($sqlthree);
                if(empty($obj))
                    continue;
                $tableobj[$i]["receiver_name"] = $obj[0]["delivery_info_receiver_name"];
                $tableobj[$i]['cs_belong_create_time'] = $obj[0]['cs_belong_create_time'];
                $tableobj[$i]['build_department_name'] = $obj[0]['build_department_name'];
                $tableobj[$i]['build_user_name'] = $obj[0]['build_user_name'];

                $tableobj[$i]["cs_info_state"] = self::parseorderstate($tableobj[$i]["cs_info_state"]);

                $tableobj[$i]["serial_number"] = $i+1;
            }
            return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
        }

        /*非物流人员根据条件查询审批确认单，五个参数最少四个:user_id  type  page  limit queryinfo*/
        public static function queryApproveConfirmOrder(...$args){
            $totalargs = count($args);
            $examine_user_id = $args[0];
            $type = $args[1];
            $pagenum = intval($args[2]?$args[2]:1);
            $length = intval($args[3]);

            $sqlone = "select count(*) from dsp_logistic.cs_examine ";
            $sqlone .= "left join dsp_logistic.cs_info on dsp_logistic.cs_info.cs_id = dsp_logistic.cs_examine.cs_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone.=" where examine_user_id = '$examine_user_id' and cs_info_type = '$type' and cs_info_state = '1' and cs_examine_state = 1";
            if($totalargs == 5){
                $sqlone.= self::approveConfirmQueryCondition($args[4]);
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
            $sqltwo .= "dsp_logistic.delivery_info.delivery_info_receiver_name from dsp_logistic.cs_examine ";
            $sqltwo .= "left join dsp_logistic.cs_info on dsp_logistic.cs_info.cs_id = dsp_logistic.cs_examine.cs_id ";
            $sqltwo .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo.=" where examine_user_id = '$examine_user_id' and cs_info_type = '$type' and cs_info_state = '1' and cs_examine_state = 1 ";
            if($totalargs == 5){
                $sqltwo.= self::approveConfirmQueryCondition($args[4]);
            }
            $sqltwo .= " order By dsp_logistic.cs_info.cs_id DESC limit {$offset},{$length} ;";
            $tableobj = Db::query($sqltwo);
            if(!empty($tableobj)){
                $countTable = count($tableobj);
                for ($i = 0;$i < $countTable;$i++)
                {
                    $tableobj[$i]["receiver_name"] = $tableobj[$i]["delivery_info_receiver_name"];

                    $tableobj[$i]["serial_number"] = $i+1;

                    $tableobj[$i]["cs_info_state"] = self::parseorderstate($tableobj[$i]["cs_info_state"]);
                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
        }

        public static function approveConfirmQueryCondition($queryInfo)
        {
            $sqlone = " ";
            $startdate = $queryInfo['startdate'];
            $enddate = $queryInfo['enddate'];
            if($startdate != "" && $enddate != "" ){
                $sqlone.= " and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
            }
            else if($startdate != "")
            {
                $sqlone.= " and cs_belong_create_time ='$startdate'";
            }
            else if($enddate != "")
            {
                $sqlone.= " and cs_belong_create_time ='$startdate'";
            }
            if($queryInfo['order_id'] != "")
            {
                $cs_id = $queryInfo['order_id'];
                $sqlone.= " and dsp_logistic.cs_info.cs_id ='$cs_id' ";
            }
            if($queryInfo['departname'] != "")
            {
                $departmentname1 = $queryInfo['departname'];
                $sqlone.= " and (build_department_name ='$departmentname1' or build_organize_name ='$departmentname1') ";

            }
            if($queryInfo['areamanager'] != "")
            {
                $user_name = $queryInfo['areamanager'];
                $sqlone.= " and build_user_name ='$user_name' ";

            }
            if ($queryInfo['receiver_name']!= "")
            {
                $receiver_name = $queryInfo['receiver_name'];
                $sqlone .= "and  delivery_info_receiver_name = '$receiver_name' ";
            }
            return $sqlone;
        }

        /*根据条件查询用户信息*/
        public static function queryuserinfo(...$args)
        {
            $pagenum = $args[0];
            $length = $args[1];
            $sql = "select count(*) from dsp_logistic.user ";
            if(count($args) == 3){
                $user_id = $args[2];
                $sql.= " where dsp_logistic.user.user_id='$user_id'";
            }
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
            if(count($args) == 3){
                $user_id = $args[2];
                $sql .= " where dsp_logistic.user.user_id='$user_id'";
            }
            $sql .= " order By dsp_logistic.user.organize_id DESC limit {$offset},{$length} ";
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
            $username= $user["username"];
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
                $sql.="  SET  username = '{$username}', fullname = '{$fullname}', password = '{$password}', phone = '{$phone}', organize_id = '{$organize_id}', job_id = '{$job_id}', role_id = '{$role_id}' ";
                $sql.=" where user_id = '{$user_id}'";
                $result = Db::execute($sql);
                return $result;
            }
            else
            {
                $sql = "INSERT INTO `dsp_logistic`.`user` (`username`,`fullname`, `password`, `phone`, `organize_id`, `job_id`, `role_id`)";
                $sql.="  VALUES ('$username','{$fullname}', '{$password}', '{$phone}', '{$organize_id}', '{$job_id}', '{$role_id}');";
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
            $sql.= "fixing_permission =  '{$role["fixing_permission"]}',maintain_permission = '{$role["maintain_permission"]}',substitute_permission = '{$role["substitute_permission"]}',user_manage_permission = '{$role["user_manage_permission_manage"]}' ,logistic_input_permission = '{$role["logistic_input_permission"]}',big_data_permission_manage = '{$role["big_data_permission_manage"]}'";
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

        /*模糊搜索型号 弃用*/
        public  static function serachmodelinfo($serachText)
        {
            $sql = "SELECT * FROM dsp_logistic.product_info WHERE model LIKE '%{$serachText}%' limit 10";
            $retsql = Db::query($sql);
            return $retsql;
        }

        /*精准搜索型号  弃用*/
        public  static function coldserachmodelinfo($serachText)
        {
            $sql = "SELECT * FROM dsp_logistic.product_info WHERE model = '{$serachText}' ";
            $retsql = Db::query($sql);
            return $retsql;
        }

        /*精准搜索型号 忽略大小写*/
        public  static function coldserachmodel($serachText)
        {
            $sql = "SELECT * FROM dsp_logistic.product_info WHERE model = UPPER('{$serachText}')";
            $retsql = Db::query($sql);
            return $retsql;
        }

        public static function addproductinfo($info){
            $product_info_id = $info['product_info_id'];
            $model = $info['model'];
            $product_info_name = $info['product_info_name'];
            $product_type_id = $info['product_type_id'];
            $brand_id = $info['brand_id'];
            $place_id = $info['place_id'];

            $sql_value ="'{$product_info_id}','{$model}','{$product_info_name}','{$product_type_id}','{$brand_id}','{$place_id}'";
            $sql = "INSERT INTO dsp_logistic.product_info (product_info_id,model,product_info_name,product_type_id,brand_id,place_id) VALUES ({$sql_value}) ";
            $sql.= "ON DUPLICATE KEY UPDATE model = '{$model}',product_info_name = '{$product_info_name}',product_type_id = '{$product_type_id}'";
            $sql.= ",brand_id = '{$brand_id}',place_id= '{$place_id}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
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
                if( count($organizeID) == 0 ){
                    return 0;
                }
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

        //2020-03-23 增
        public  static function addConfirmOrder($info)
        {
            Db::startTrans();
            try{
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
                $cs_examine_ids = $info['cs_examine_ids'];
                $unc_ofg_info_id = $info['unc_ofg_info_id'];
                $delivery_date_reply = $info['delivery_date_reply'];
                $cs_info_delivery_date = $info['cs_info_delivery_date'];
                $cs_info_warehouse_date = $info['cs_info_warehouse_date'];
                //INSERT INTO `dsp_logistic`.`cs_info` (`cs_id`) VALUES ('2018111500003');

                $sql_value ="'{$cs_id}','{$custom_info_id}','{$delivery_info_id}','{$return_info_id}','{$payment_info_id}','{$cur_process_user_id}','{$pre_process_user_id}','{$cs_info_type}','{$can_edit}','{$write_date}','{$cs_info_state}','{$complete_date}','{$product_number}','$cs_examine_ids','{$unc_ofg_info_id}','$delivery_date_reply','$cs_info_delivery_date','$cs_info_warehouse_date'";
                $sql = "INSERT INTO dsp_logistic.cs_info (cs_id,custom_info_id,delivery_info_id,return_info_id,payment_info_id,cur_process_user_id,pre_process_user_id";
                $sql.= ",cs_info_type,can_edit,write_date,cs_info_state,complete_date,product_number,cs_examine_ids,unc_ofg_info_id,delivery_date_reply,cs_info_delivery_date,cs_info_warehouse_date) VALUES ({$sql_value}) ";
                Db::execute($sql);
                Db::commit();
                return $cs_id;
            }
            catch (Exception $ex)
            {
                Db::rollback();
                return false;
            }
        }

        /*update    暂时使用（更改确认单）*/
        public  static function updateconfirmorder($info)
        {
            try{
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
                $cs_examine_ids = $info['cs_examine_ids'];
                $unc_ofg_info_id = $info['unc_ofg_info_id'];
                $delivery_date_reply = $info['delivery_date_reply'];
                $cs_info_delivery_date = $info['cs_info_delivery_date'];
                $cs_info_warehouse_date = $info['cs_info_warehouse_date'];
                $sql_value ="'{$cs_id}','{$custom_info_id}','{$delivery_info_id}','{$return_info_id}','{$payment_info_id}','{$cur_process_user_id}','{$pre_process_user_id}','{$cs_info_type}','{$can_edit}','{$write_date}','{$cs_info_state}','{$complete_date}','{$product_number}','$cs_examine_ids','{$unc_ofg_info_id}','$delivery_date_reply','$cs_info_delivery_date','$cs_info_warehouse_date'";
                $sql = "INSERT INTO dsp_logistic.cs_info (cs_id,custom_info_id,delivery_info_id,return_info_id,payment_info_id,cur_process_user_id,pre_process_user_id";
                $sql.= ",cs_info_type,can_edit,write_date,cs_info_state,complete_date,product_number,cs_examine_ids,unc_ofg_info_id,delivery_date_reply,cs_info_delivery_date,cs_info_warehouse_date) VALUES ({$sql_value}) ";
                $sql.= "ON DUPLICATE KEY UPDATE custom_info_id = '{$custom_info_id}',delivery_info_id = '{$delivery_info_id}',return_info_id = '{$return_info_id}',payment_info_id = '{$payment_info_id}',";
                $sql.= "cur_process_user_id= '{$cur_process_user_id}',pre_process_user_id= '{$pre_process_user_id}',cs_info_type = '{$cs_info_type}',";
                $sql.= "can_edit= '{$can_edit}',write_date= '{$write_date}',cs_info_state= '{$cs_info_state}',complete_date= '{$complete_date}',product_number= '$product_number',";
                $sql.= "cs_examine_ids= '$cs_examine_ids',unc_ofg_info_id='{$unc_ofg_info_id}',delivery_date_reply ='$delivery_date_reply', ";
                $sql.= "cs_info_delivery_date= '$cs_info_delivery_date',cs_info_warehouse_date= '$cs_info_warehouse_date'";
                $sqlret = Db::execute($sql);
                return $sqlret;
            }
            catch (Exception $ex)
            {
                return false;
            }

        }
        /*update custom_info */
        public  static function updatecustominfo($info)
        {
            try
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
            catch (Exception $ex)
            {
                return false;
            }

        }
        /*update delivery_info */
        public static function updatedeliveryinfo($info){
            try
            {
                $delivery_info_id = $info['delivery_info_id'];
                $delivery_info_receiver_name = $info['delivery_info_receiver_name'];
                $delivery_info_receiver_phone = $info['delivery_info_receiver_phone'];
                $delivery_info_goods_yard_name = $info['delivery_info_goods_yard_name'];
                $delivery_info_goods_yard_phone = $info['delivery_info_goods_yard_phone'];
                $delivery_info_receiver_address = $info['delivery_info_receiver_address'];
                $is_insure = $info['is_insure'];
                $is_sign = $info['is_sign'];
                $insure_amount = $info['insure_amount'];
                $has_contract = $info['has_contract'];
                $transfer_fee_mode = $info['transfer_fee_mode'];
                $order_delivery_require = $info['order_delivery_require'];
                $sql_value ="'$delivery_info_id','{$delivery_info_receiver_name}','{$delivery_info_receiver_phone}','{$delivery_info_goods_yard_name}','{$delivery_info_goods_yard_phone}','{$delivery_info_receiver_address}','{$is_insure}','{$insure_amount}','{$is_sign}','{$has_contract}','{$transfer_fee_mode}','$order_delivery_require'";
                $sql = "INSERT INTO dsp_logistic.delivery_info (delivery_info_id,delivery_info_receiver_name,delivery_info_receiver_phone,delivery_info_goods_yard_name,delivery_info_goods_yard_phone,delivery_info_receiver_address,is_insure,insure_amount,is_sign,has_contract,transfer_fee_mode,order_delivery_require) VALUES ({$sql_value})";
                $sql.= "ON DUPLICATE KEY UPDATE delivery_info_receiver_name = '$delivery_info_receiver_name',delivery_info_receiver_phone = '$delivery_info_receiver_phone',delivery_info_goods_yard_name = '{$delivery_info_goods_yard_name}',";
                $sql.= "delivery_info_goods_yard_phone= '{$delivery_info_goods_yard_phone}',delivery_info_receiver_address= '{$delivery_info_receiver_address}',is_insure = '{$is_insure}',";
                $sql.= "is_sign= '{$is_sign}',insure_amount= '{$insure_amount}',has_contract= '{$has_contract}',transfer_fee_mode= '{$transfer_fee_mode}',order_delivery_require = '$order_delivery_require'";
                $sqlret = Db::execute($sql);
                return $sqlret;
            }
            catch (Exception $ex)
            {
                return false;
            }

        }
        /*update return_info */
        public  static function updatereturninfo($info)
        {
            try{
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
            catch (Exception $ex)
            {
                return false;
            }

        }

        /*update payment_info*/
        public static function updatepaymentinfo($info){
            try
            {
                $payment_info_id = $info['payment_info_id'];
                $is_paid = $info['is_paid'];
                $paid_date = $info['paid_date'];
                $customization_fee = $info['customization_fee'];
                $paid_bank = $info['paid_bank'];
                $payment_info_comment = $info['payment_info_comment'];
//            $time_stamp = $info['time_stamp'];
//            $user_id = $info['user_id'];
                $sql_value ="'$payment_info_id','{$is_paid}','{$paid_date}','{$customization_fee}','{$paid_bank}','{$payment_info_comment}'";
                $sql = "INSERT INTO dsp_logistic.payment_info (payment_info_id,is_paid,paid_date,customization_fee,paid_bank,payment_info_comment) VALUES ({$sql_value})";
                $sql.= "ON DUPLICATE KEY UPDATE is_paid = '$is_paid',paid_date = '$paid_date',customization_fee = '{$customization_fee}',";
                $sql.= "paid_bank= '$paid_bank',payment_info_comment= '$payment_info_comment'";
                $sqlret = Db::execute($sql);
                return $sqlret;
            }
            catch (Exception $ex)
            {
                return false;
            }

        }

        /*2020-04-22 增*/
        public static function addcsbelong($info){
            Db::startTrans();
            try{
                $cs_id = $info['cs_id'];
                $query = "SELECT * FROM dsp_logistic.cs_belong where `cs_id` = '$cs_id';";
                if(!$query){
                    $cs_id = self::getcsinfomaxid("cs_belong","cs_id");
                }
                $cs_belong_id = $info['cs_belong_id'];
                $build_organize_id = $info['build_organize_id'];
                $build_user_id = $info['build_user_id'];
                $cs_belong_create_time = $info['cs_belong_create_time'];
                $build_user_name = $info['build_user_name'];
                $build_organize_name = $info['build_organize_name'];
                $build_department_id = $info['build_department_id'];
                $build_department_name = $info['build_department_name'];
                $build_user_phone = $info['build_user_phone'];
                $sql_value ="'$cs_belong_id','{$cs_id}','{$build_organize_id}','{$build_user_id}','{$cs_belong_create_time}','{$build_user_name}','{$build_organize_name}','{$build_department_id}','{$build_department_name}','$build_user_phone'";
                $sql = "INSERT INTO dsp_logistic.cs_belong (cs_belong_id,cs_id,build_organize_id,build_user_id,cs_belong_create_time,build_user_name,build_organize_name,build_department_id,build_department_name,build_user_phone) VALUES ({$sql_value})";
                Db::execute($sql);
                Db::commit();
                return $cs_id;
            }
            catch (Exception $ex)
            {
                Db::rollback();
                return false;
            }

        }

        /*update*/
        public static function updatecsbelong($info){
            try
            {
                $cs_belong_id = $info['cs_belong_id'];
                $cs_id = $info['cs_id'];
                $build_organize_id = $info['build_organize_id'];
                $build_user_id = $info['build_user_id'];
                $cs_belong_create_time = $info['cs_belong_create_time'];
                $build_user_name = $info['build_user_name'];
                $build_organize_name = $info['build_organize_name'];
                $build_department_id = $info['build_department_id'];
                $build_department_name = $info['build_department_name'];
                $build_user_phone = $info['build_user_phone'];
                $sql_value ="'$cs_belong_id','{$cs_id}','{$build_organize_id}','{$build_user_id}','{$cs_belong_create_time}','{$build_user_name}','{$build_organize_name}','{$build_department_id}','{$build_department_name}','$build_user_phone'";
                $sql = "INSERT INTO dsp_logistic.cs_belong (cs_belong_id,cs_id,build_organize_id,build_user_id,cs_belong_create_time,build_user_name,build_organize_name,build_department_id,build_department_name,build_user_phone) VALUES ({$sql_value})";
                $sql.= "ON DUPLICATE KEY UPDATE cs_id = '$cs_id',build_organize_id = '$build_organize_id',build_user_id = '$build_user_id',";
                $sql.= "cs_belong_create_time= '$cs_belong_create_time',build_user_name= '$build_user_name',build_organize_name= '$build_organize_name',";
                $sql.= "build_department_id='{$build_department_id}',build_department_name='{$build_department_name}',build_user_phone = '$build_user_phone';";
                $sqlret = Db::execute($sql);
                return $sqlret;
            }
            catch (Exception $ex)
            {
                return false;
            }

        }
        /*update 确认单清单经理部分 order_goods_manager*/
        public static function updateordergoodsmanager($info){
            //id
            try
            {
                $order_goods_manager_id = $info['order_goods_manager_id'];
                $cs_id = $info['cs_id'];
                $product_info_id = $info['product_info_id'];
                $unit_price = $info['unit_price']== ''?-1:$info['unit_price'];
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
            catch (Exception $ex)
            {
                return false;
            }

        }
        /*update 确认单清单物流部分 order_goods_logistics*/
        public static function updateordergoodslogistics($info){
            try
            {
                $ogl_id = $info['ogl_id'];
                $order_goods_manager_id = $info['order_goods_manager_id'];
                $ogl_product_state = $info['ogl_product_state'];
                $unc_product_id = $info['unc_product_id'];
                $ogl_comment = $info['ogl_comment'];
                $user_id = $info['user_id'];
                $ogl_time_stamp = $info['ogl_time_stamp'];
                $ogl_explain = $info['ogl_explain'];
                $sql_value ="'$ogl_id','{$order_goods_manager_id}','{$ogl_product_state}','{$unc_product_id}','{$ogl_comment}','{$user_id}','{$ogl_time_stamp}','{$ogl_explain}'";
                $sql = "INSERT INTO dsp_logistic.order_goods_logistics (ogl_id,order_goods_manager_id,ogl_product_state,unc_product_id,ogl_comment,user_id,ogl_time_stamp,ogl_explain) VALUES ({$sql_value})";
                $sql.= "ON DUPLICATE KEY UPDATE order_goods_manager_id = '$order_goods_manager_id',ogl_product_state = '$ogl_product_state',unc_product_id = '$unc_product_id',";
                $sql.= "ogl_comment= '$ogl_comment',user_id= '$user_id',ogl_time_stamp= '$ogl_time_stamp',";
                $sql.= "ogl_explain= '$ogl_explain'";
                $sqlret = Db::execute($sql);
                return $sqlret;
            }
            catch (Exception $ex)
            {
                return false;
            }

        }
        /*update 确认单审批 cs_examine*/
        public static function updatecsexamine($info){
            try
            {
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
            catch (Exception $ex)
            {
                return false;
            }

        }

        /*update  logisticinfo*/
        public static function updatelogisticinfo($info){
            try
            {
                $logistics_id = $info['logistics_id'];
                $cs_id = $info['cs_id'];
                $goods_yard_name = $info['goods_yard_name'];
                $transfer_order_num = $info['transfer_order_num'];
                $delivery_date = $info['delivery_date'];
                $count = $info['count'];
                $user_id = $info['user_id'];
                $time_stamp = $info['time_stamp'];
                $sql_value ="'$logistics_id','$cs_id','$goods_yard_name','$transfer_order_num','$delivery_date','$count','$user_id','$time_stamp'";
                $sql = "INSERT INTO dsp_logistic.logistics_info (logistics_id,cs_id,goods_yard_name,transfer_order_num,delivery_date,count,user_id,time_stamp) VALUES ({$sql_value})";
                $sql.= "ON DUPLICATE KEY UPDATE cs_id = '$cs_id',goods_yard_name = '$goods_yard_name',transfer_order_num = '$transfer_order_num',";
                $sql.= "delivery_date= '$delivery_date',count= '$count',user_id= '$user_id',";
                $sql.= "time_stamp= '$time_stamp'";
                $sqlret = Db::execute($sql);
                return $sqlret;
            }
            catch (Exception $ex)
            {
                return false;
            }

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
            }else if($role_name != '财务人员'){
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
           // $sql ="select * from dsp_logistic.{$tableName} where {$tableID} like '%{$dateymd}%'";
            $sql = "select max(dsp_logistic.$tableName.$tableID) from dsp_logistic.$tableName where $tableID like '%{$dateymd}%'";
            $retdb = Db::query($sql);
//            if(empty($retdb)){
//                return $dateymd.'00001';
//            }else{
//                $num = count($retdb);
//                $maxid = 00001;
//                for($i = 0; $i < $num; $i++){
//                    $cs_id = $retdb[$i]['cs_id'];
//                    $cur_id = str_replace($dateymd,'',$cs_id);
//                    if ($cur_id > $maxid){
//                        $maxid = $cur_id;
//                    }
//                }
//
//                $maxid = $maxid + 00001;
//                $strmaxid = $newStr= sprintf('%05s', $maxid);;
//                return $dateymd.$strmaxid;
//            }

            //2020-04-17 改
//             $num = 0;
//             if(!empty($retdb))
//             {
//                 $cs_id = $retdb[0]["max(dsp_logistic.$tableName.$tableID)"];
//                 $cur_id = str_replace($dateymd,'',$cs_id);
//                 //$cur_id = 0;
//                 if(count($cs_id) > 5)
//                     $cur_id = substr($cs_id,8,5);
//                 $num = $cur_id;
//
//             }
//             $exit = true;
//             while ($exit)
//             {
//                 $num = $num +1;
//                 $newStr= sprintf('%05s', $num);
//                 $cs_id = $dateymd.$newStr;
//                 $sql1 = "SELECT * FROM dsp_logistic.cs_info where $tableID = '$cs_id'";
//                 $sql2 = "SELECT * FROM dsp_logistic.order_goods_cs_info where $tableID = '$cs_id'";
//                 $retdb1 = Db::query($sql1);
//                 $retdb2 = Db::query($sql2);
//                 if(empty($retdb1)&&empty($retdb2))
//                     return $cs_id;
//             }

             if(!empty($retdb)) {
                 $cs_id = $retdb[0]["max(dsp_logistic.$tableName.$tableID)"];
                 if($cs_id == 0){
                     $num = 1;
                     $newStr= sprintf('%05s', $num);
                     return $dateymd.$newStr;
                 }
                 else{
                     return $cs_id+1;
                 }


             }
             else{
                 $num = 1;
                $newStr= sprintf('%05s', $num);
                return $dateymd.$newStr;
             }

        }

        public static function getcuruserquerypower($user)
        {
            if(empty($user))
                return;
            $role = self::queryroleinfo($user["role_id"]);
            if(empty($role))
                return ;
            $rolename = $role[0]["role_name"];
            $userquerypower = array();
            $userquerypower["isSales"] = false;
            $userquerypower["role_name"] = $rolename;
            if( $rolename == "管理人员" || $rolename == "部长/主管"||$rolename == "物流部人员"||$rolename == "财务人员")
            {
                $userquerypower["isSales"] = true;
            }
            else
            {
                if($rolename == "总经理")
                {
                    $userquerypower["organizename"] = self::querydepartmentname($user["organize_id"]);
                    $userquerypower["departmentname"] ="";
                    $userquerypower["areamanager"] ="";
                }
                if($rolename == "部门总监" || $rolename == "部门助理")
                {
                    $userquerypower["departmentname"] = self::querydepartmentname($user["organize_id"]);
                    $userquerypower["areamanager"] ="";
                    $organize = self::getdepleaderbyuserid($user["user_id"],"总经理");
                    $userquerypower["organizename"] = self::querydepartmentname($organize[0]["organize_id"]);
                }
                if($rolename == "区域经理")
                {
                    $userquerypower["areamanager"] = $user["fullname"];
                    $depart = self::getdepleaderbyuserid($user["user_id"],"部门总监");
                    $userquerypower["departmentname"] = self::querydepartmentname($depart[0]["organize_id"]);
                    $organize = self::getdepleaderbyuserid($depart[0]["user_id"],"总经理");
                    $userquerypower["organizename"] = self::querydepartmentname($organize[0]["organize_id"]);
                }
            }
            session("user_querypower", $userquerypower);
        }

        public static function getdepartmentnumber($departid)
        {
            $sql = "select * from  dsp_logistic.organize where organize_id = '$departid'";
            $tableorganize = Db::query($sql);
            if(!empty($tableorganize))
                return $tableorganize[0]['department_number'];
            else
                return 0;
        }

        public static function login($username,$password){
            $where['username'] = $username;
            //$where['password'] = $password;

            $user = Db::name('dsp_logistic.user')->where($where)->find();   /*用户信息检测*/
            if($user){

                if (md5($user["password"]) != $password){
                    return false;
                }
                unset($user["password"]);      	   					       /*销毁password*/
                session_start();
                session("user_session", $user);/*创建session,里面只包含用户名，password已经销毁*/

                self::getcuruserquerypower($user);
                return true;
            }else{
                return false;
            }
        }

        /*查询当前待审核订单个数*/
        public static function querycsinfonums($user_id,$type){
            $sql = "select count(*) from dsp_logistic.cs_examine ";
            $sql .= "left join dsp_logistic.cs_info on dsp_logistic.cs_examine.cs_id = dsp_logistic.cs_info.cs_id";
            $sql .= " where dsp_logistic.cs_examine.examine_user_id = '$user_id' and dsp_logistic.cs_info.cs_info_type = '$type' and cs_examine_state = '1' and dsp_logistic.cs_info.cs_info_state ='1'";
            $nums = Db::query($sql);
            if(empty($nums))
                return 0;
            return $nums[0]['count(*)'];

        }

        /*查询物流待审核*/
        public static function querylogsticcsinfonums($type){
            $sql = "select count(*) from dsp_logistic.cs_info where dsp_logistic.cs_info.cs_info_type = '$type' and cs_info_state = 1 and dsp_logistic.cs_info.complete_date <= '2000-01-01 00:00:00'";
            $nums = Db::query($sql);
            if(empty($nums))
                return 0;
            return $nums[0]['count(*)'];
        }

        /*查询需要导出的更换/代用/维修/退货/配件/借样确认单  未完待续*/
        // public static function queryexportcsinfoconfirmorder($param,$type,$organizename,$departmentname,$areamanager){
        //     $sqlone ="select dsp_logistic.cs_belong.*,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.*,dsp_logistic.return_info.* from dsp_logistic.cs_info ";
        //     $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
        //     $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
        //     $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
        //     $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type' ";

        //     if($organizename != ""){
        //         $sqlone .= "and build_organize_name='$organizename' ";
        //     }

        //     if($departmentname != ""){
        //         $sqlone .= "and build_department_name='$departmentname' ";
        //     }

        //     if($areamanager != ""){
        //         $sqlone .= "and build_user_name='$areamanager' ";
        //     }

        //     if((property_exists($param,'startdate'))&&(property_exists($param,'enddate'))){
        //        $startdate = $param->startdate;
        //        $enddate = $param->enddate;
        //        $sqlone.= " and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
        //     }else if(property_exists($param,'startdate')){
        //         $startdate = $param->startdate;
        //         $sqlone.= " and cs_belong_create_time ='$startdate'";
        //     }else if(property_exists($param,'enddate')){
        //         $enddate = $param->enddate;
        //         $sqlone.= " and cs_belong_create_time ='$enddate'";
        //     }

        //     if(property_exists($param,'departname')){
        //         $departname = $param->departname;
        //         $sqlone.= " and (build_department_name ='$departname' or build_organize_name ='$departname') ";
        //     }

        //     if(property_exists($param,'areamanager')){
        //         $areamanager = $param->areamanager;
        //         $sqlone.= " and build_user_name ='$areamanager' ";
        //     }

        //     if(property_exists($param,'orderstate')){
        //         $orderstate = $param->orderstate;
        //         $sqlone.= " and cs_info_state ='$orderstate' ";
        //     }

        //     if(property_exists($param,'order_id')){
        //         $order_id = $param->order_id;
        //         $sqlone.= " and dsp_logistic.cs_info.cs_id ='$order_id' ";
        //     }

        //     if($type == 2||$type == 5){
        //         if(property_exists($param,'receiver_name')){
        //             $delivery_info_receiver_name = $param->receiver_name;
        //             $sqlone.= " and delivery_info_receiver_name ='$delivery_info_receiver_name' ";
        //         }
        //     }else{
        //         if(property_exists($param,'receiver_name')){
        //             $return_info_receiver_name = $param->receiver_name;
        //             $sqlone.= " and return_info_receiver_name ='$return_info_receiver_name' ";
        //         }
        //     }

        //     /*运费付费模式*/
        //     if(property_exists($param,'freightmode')){
        //         $freightmode = intval($param->freightmode);
        //         $sqlone .= "and dsp_logistic.delivery_info.transfer_fee_mode = '$freightmode' ";
        //     }

        //     $tableobj = Db::query($sqlone);
        //     if(empty($tableobj))
        //         return null;

        //     /*查询确认单明细*/
        //     for ($i=0; $i < count($tableobj); $i++) {
        //         $cs_id = $tableobj[$i]['cs_id'];

        //         $sqltwo = "select dsp_logistic.order_goods_manager.*,dsp_logistic.product_info.*,dsp_logistic.product_brand.brand_name,dsp_logistic.product_type.product_type_name,dsp_logistic.product_place.place_name,dsp_logistic.order_goods_logistics.*,dsp_logistic.unc_product.* from dsp_logistic.order_goods_manager ";
        //         $sqltwo .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_manager.product_info_id ";
        //         $sqltwo .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
        //         $sqltwo .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
        //         $sqltwo .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
        //         $sqltwo .= "left join dsp_logistic.order_goods_logistics on dsp_logistic.order_goods_logistics.order_goods_manager_id = dsp_logistic.order_goods_manager.order_goods_manager_id ";
        //         $sqltwo .= "left join dsp_logistic.unc_product on dsp_logistic.unc_product.unc_product_id = dsp_logistic.order_goods_logistics.unc_product_id ";
        //         $sqltwo .= "where dsp_logistic.order_goods_manager.type='$type' and dsp_logistic.order_goods_manager.cs_id ='$cs_id' ";
        //         if(property_exists($param,'productclass')){
        //             $productclass = intval($param->productclass);
        //             $sqltwo .= "and dsp_logistic.product_type.product_type_id = '$productclass' ";
        //         }

        //         /*品牌*/
        //         if(property_exists($param,'brand')){
        //             $brand = intval($param->brand);
        //             $sqltwo .= "and dsp_logistic.product_brand.brand_id = '$brand' ";
        //         }

        //         /*产品型号*/
        //         if(property_exists($param,'producttype')){
        //             $producttype = $param->producttype;
        //             $sqltwo .= "and dsp_logistic.product_info.model = '$producttype' ";
        //         }

        //         /*生产地*/
        //         if(property_exists($param,'productarea')){
        //             $productarea = intval($param->productarea);
        //             $sqltwo .= "and dsp_logistic.product_place.place_id = '$productarea' ";
        //         }

        //         /*非常规订单*/
        //         if(property_exists($param,'customproduct')){
        //             $customproduct = intval($param->customproduct);
        //             $sqltwo .= "and dsp_logistic.unc_product.unc_product_id = '$customproduct' ";
        //         }

        //         $onelistobj = Db::query($sqltwo);
        //         $tableobj[$i]['ofg_productlist'] = $onelistobj;
        //     }

        //     // /*单独查询发货日期*/
        //     if(count($tableobj) > 0){
        //         for($i=0; $i < count($tableobj); $i++){
        //             $cs_id = $tableobj[$i]['cs_id'];
        //             $sqlfour = "select dsp_logistic.logistics_info.* from dsp_logistic.logistics_info where cs_id='$cs_id'";
        //             $dateobj = Db::query($sqlfour);
        //             $tableobj[$i]['logistic_date'] = $dateobj;
        //         }
        //     }

        //     /*统计缺货的,产品明细，非常规部分*/
        //     for($i=0; $i<count($tableobj);$i++){
        //         /*广播会议数量*/
        //         $broadcast_num = 0 ;
        //         $meeting_num = 0 ;
        //         $subway_num = 0 ;
        //         $auxdi_num = 0 ;
        //         $record_num = 0 ;

        //         /*非常规产品数量*/
        //         $unc_develop_num = 0 ;
        //         $unc_special_num = 0;
        //         $unc_network_num = 0 ;
        //         $unc_interlligence_num = 0;
        //         $unc_address_num = 0;
        //         $unc_register_num = 0;
        //         $unc_networksoft_num = 0;
        //         $unc_interlligencesoft_num = 0;

        //         /*缺货产品列表*/
        //         $lessproductlist = array();

        //         $ofg_productlist = $tableobj[$i]['ofg_productlist'];
        //         for($j=0 ; $j <count($ofg_productlist); $j++ ){
        //             if($ofg_productlist[$j]['product_type_name'] == '公共广播')
        //                 $broadcast_num++;
        //             if($ofg_productlist[$j]['product_type_name'] == '会议系统')
        //                 $meeting_num++;
        //             if($ofg_productlist[$j]['product_type_name'] == '地铁事业')
        //                 $subway_num++;
        //             if($ofg_productlist[$j]['product_type_name'] == '澳斯迪')
        //                 $auxdi_num++;
        //             if($ofg_productlist[$j]['product_type_name'] == '录播')
        //                 $record_num++;

        //             if($ofg_productlist[$j]['unc_product_name'] == '研发')
        //                 $unc_develop_num++;
        //             if($ofg_productlist[$j]['unc_product_name'] == '特殊')
        //                 $unc_special_num++;
        //             if($ofg_productlist[$j]['unc_product_name'] == '网络化')
        //                 $unc_network_num++;
        //             if($ofg_productlist[$j]['unc_product_name'] == '智能化')
        //                 $unc_interlligence_num++;
        //             if($ofg_productlist[$j]['unc_product_name'] == '可寻址')
        //                 $unc_address_num++;
        //             if($ofg_productlist[$j]['unc_product_name'] == '注册码')
        //                 $unc_register_num++;
        //             if($ofg_productlist[$j]['unc_product_name'] == '网络化软件')
        //                 $unc_networksoft_num++;
        //             if($ofg_productlist[$j]['unc_product_name'] == '智能化软件')
        //                 $unc_interlligencesoft_num++;

        //             /*缺货部分统计 order_goods_manager_count*/
        //             if($ofg_productlist[$j]['ogl_product_state'] == '缺货'){
        //                 $model_param['place_name'] = $ofg_productlist[$j]['place_name'];
        //                 $model_param['brand_name'] = $ofg_productlist[$j]['brand_name'];
        //                 $model_param['model'] = $ofg_productlist[$j]['model'];
        //                 $model_param['order_goods_manager_count'] = $ofg_productlist[$j]['order_goods_manager_count'];
        //                 $lessproductlist[] = $model_param;
        //             }
        //         }

        //         /*统计各产品数量*/
        //         $tableobj[$i]['broadcast_num'] = $broadcast_num;
        //         $tableobj[$i]['meeting_num'] = $meeting_num;
        //         $tableobj[$i]['subway_num'] = $subway_num;
        //         $tableobj[$i]['auxdi_num'] = $auxdi_num;
        //         $tableobj[$i]['record_num'] = $record_num;

        //         /*统计各非常规的数量*/
        //         $tableobj[$i]['unc_develop_num'] = $unc_develop_num;
        //         $tableobj[$i]['unc_special_num'] = $unc_special_num;
        //         $tableobj[$i]['unc_network_num'] = $unc_network_num;
        //         $tableobj[$i]['unc_interlligence_num'] = $unc_interlligence_num;
        //         $tableobj[$i]['unc_address_num'] = $unc_address_num;
        //         $tableobj[$i]['unc_register_num'] = $unc_register_num;
        //         $tableobj[$i]['unc_networksoft_num'] = $unc_networksoft_num;
        //         $tableobj[$i]['unc_interlligencesoft_num'] = $unc_interlligencesoft_num;

        //         $tableobj[$i]['lessproductlist'] = $lessproductlist;

        //         /*发货日期*/
        //         $logistic_date = $tableobj[$i]['logistic_date'];
        //         $delivery_logistic_date = "";
        //         $delivery_logistic_yard = "";
        //         for($l = 0 ; $l < count($logistic_date) ; $l++){
        //             $delivery_logistic_date .= $logistic_date[$l]['delivery_date'].',';
        //             $delivery_logistic_yard .= $logistic_date[$l]['goods_yard_name'].',';
        //         }
        //         $tableobj[$i]['delivery_logistic_date'] = $delivery_logistic_date;
        //         $tableobj[$i]['delivery_logistic_yard'] = $delivery_logistic_yard;
        //     }
        //     return $tableobj;
        // }

        /*查询需要导出的更换/代用/维修/退货/配件/借样确认单*/
        public static function queryexportcsinfo($param){
            $sqlone ="select dsp_logistic.cs_belong.*,dsp_logistic.custom_info.*,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.*,dsp_logistic.return_info.* from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type like '%%'  and dsp_logistic.cs_info.cs_info_state !='0' and dsp_logistic.cs_info.cs_info_state !='3' ";

            if((property_exists($param,'startdate'))&&(property_exists($param,'enddate'))){
               $startdate = $param->startdate;
               $enddate = $param->enddate;
               $sqlone.= " and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
            }else if(property_exists($param,'startdate')){
                $startdate = $param->startdate;
                $sqlone.= " and cs_belong_create_time ='$startdate'";
            }else if(property_exists($param,'enddate')){
                $enddate = $param->enddate;
                $sqlone.= " and cs_belong_create_time ='$enddate'";
            }

            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;

            /*查询确认单明细*/
            for ($i=0; $i < count($tableobj); $i++) {
                $cs_id = $tableobj[$i]['cs_id'];

                $sqltwo = "select dsp_logistic.order_goods_manager.*,dsp_logistic.product_info.*,dsp_logistic.product_brand.brand_name,dsp_logistic.product_type.product_type_name,dsp_logistic.product_place.place_name,dsp_logistic.order_goods_logistics.*,dsp_logistic.unc_product.* from dsp_logistic.order_goods_manager ";
                $sqltwo .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_manager.product_info_id ";
                $sqltwo .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
                $sqltwo .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
                $sqltwo .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
                $sqltwo .= "left join dsp_logistic.order_goods_logistics on dsp_logistic.order_goods_logistics.order_goods_manager_id = dsp_logistic.order_goods_manager.order_goods_manager_id ";
                $sqltwo .= "left join dsp_logistic.unc_product on dsp_logistic.unc_product.unc_product_id = dsp_logistic.order_goods_logistics.unc_product_id ";
                $sqltwo .= "where dsp_logistic.order_goods_manager.cs_id ='$cs_id' ";
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

                $onelistobj = Db::query($sqltwo);
                /*假如$onelistobj == null 为空时 那么该单不显示*/
                if(empty($onelistobj)){
                    $tableobj[$i] = null;
                }else{
                    $tableobj[$i]['ofg_productlist'] = $onelistobj;
                }
            }

            //删除数组中空值
            //$tableobj = array_filter($tableobj);
            $newtableobj = array();
            for($item = 0 ; $item < count($tableobj) ; $item++){
                if($tableobj[$item] != null){
                    $newtableobj[] = $tableobj[$item];
                }
            }
            return $newtableobj;
        }

        /*导出更换确认单 代用确认单 维修确认单  退货确认单 配件确认单 借用确认单*/
        // public static function exportcsinfoconfirmorder($file_name,$file_extend,$template_name,$ret,$type){
        //     $root_url = $_SERVER['DOCUMENT_ROOT'];
        //     $file_name = iconv("utf-8","gb2312",$file_name);
        //     $template_name = iconv("utf-8","gb2312",$template_name);

        //     $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        //     $objPHPExcel = $objReader->load($root_url."/templates/".$template_name);
        //     $objPHPExcel->setActiveSheetIndex(0);
        //     $objPHPExcel->getActiveSheet()->setTitle('sheet0');

        //     $liststart = 0 ;
        //     for($item=3; $item < count($ret)+3; $item++){
        //         $objPHPExcel->getActiveSheet()->setCellValue('A'.($item+$liststart), $ret[$item-3]['cs_belong_create_time']);
        //         $objPHPExcel->getActiveSheet()->setCellValue('B'.($item+$liststart), $ret[$item-3]['delivery_logistic_date']);
        //         $objPHPExcel->getActiveSheet()->setCellValue('C'.($item+$liststart), $ret[$item-3]['build_department_name']);
        //         $objPHPExcel->getActiveSheet()->setCellValue('D'.($item+$liststart), $ret[$item-3]['build_user_name']);
        //         if(($type == 2)||($type == 5)){
        //             $objPHPExcel->getActiveSheet()->setCellValue('E'.($item+$liststart), $ret[$item-3]['delivery_info_receiver_name']);
        //         }else{
        //             $objPHPExcel->getActiveSheet()->setCellValue('E'.($item+$liststart), $ret[$item-3]['return_info_receiver_name']);
        //         }

        //         $objPHPExcel->getActiveSheet()->setCellValue('F'.($item+$liststart), $ret[$item-3]['delivery_logistic_yard']);
        //         /*订单状态*/
        //         if($ret[$item-3]['cs_info_state'] == 1){
        //             $objPHPExcel->getActiveSheet()->setCellValue('G'.($item+$liststart), '处理中');
        //             $objPHPExcel->getActiveSheet()->setCellValue('H'.($item+$liststart), 0);
        //         }else if($ret[$item-3]['cs_info_state'] == 2){
        //             $objPHPExcel->getActiveSheet()->setCellValue('G'.($item+$liststart), '已完成');
        //             $objPHPExcel->getActiveSheet()->setCellValue('H'.($item+$liststart), 1);
        //         }else if($ret[$item-3]['cs_info_state'] == 3){
        //             $objPHPExcel->getActiveSheet()->setCellValue('G'.($item+$liststart), '取消');
        //             $objPHPExcel->getActiveSheet()->setCellValue('H'.($item+$liststart), 0);
        //         }else if($ret[$item-3]['cs_info_state'] == 4){
        //             $objPHPExcel->getActiveSheet()->setCellValue('G'.($item+$liststart), '备货');
        //             $objPHPExcel->getActiveSheet()->setCellValue('H'.($item+$liststart), 0);
        //         }else if($ret[$item-3]['cs_info_state'] == 5){
        //             $objPHPExcel->getActiveSheet()->setCellValue('G'.($item+$liststart), '退回');
        //             $objPHPExcel->getActiveSheet()->setCellValue('H'.($item+$liststart), 0);
        //         }else if($ret[$item-3]['cs_info_state'] == 6){
        //             $objPHPExcel->getActiveSheet()->setCellValue('G'.($item+$liststart), '缺货');
        //             $objPHPExcel->getActiveSheet()->setCellValue('H'.($item+$liststart), 0);
        //         }
        //         $objPHPExcel->getActiveSheet()->setCellValue('I'.($item+$liststart), $ret[$item-3]['product_number']);
        //         if($ret[$item-3]['broadcast_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('N'.($item+$liststart), $ret[$item-3]['broadcast_num']);
        //         if($ret[$item-3]['meeting_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('O'.($item+$liststart), $ret[$item-3]['meeting_num']);
        //         if($ret[$item-3]['subway_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('P'.($item+$liststart), $ret[$item-3]['subway_num']);
        //         if($ret[$item-3]['auxdi_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('Q'.($item+$liststart), $ret[$item-3]['auxdi_num']);
        //         if($ret[$item-3]['record_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('R'.($item+$liststart), $ret[$item-3]['record_num']);

        //         /*非常规部分订单*/
        //         if($ret[$item-3]['unc_develop_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('S'.($item+$liststart), $ret[$item-3]['unc_develop_num']);
        //         if($ret[$item-3]['unc_special_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('T'.($item+$liststart), $ret[$item-3]['unc_special_num']);
        //         if($ret[$item-3]['unc_network_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('U'.($item+$liststart), $ret[$item-3]['unc_network_num']);
        //         if($ret[$item-3]['unc_interlligence_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('V'.($item+$liststart), $ret[$item-3]['unc_interlligence_num']);
        //         if($ret[$item-3]['unc_address_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('W'.($item+$liststart), $ret[$item-3]['unc_address_num']);
        //         if($ret[$item-3]['unc_register_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('X'.($item+$liststart), $ret[$item-3]['unc_register_num']);
        //         if($ret[$item-3]['unc_networksoft_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('Y'.($item+$liststart), $ret[$item-3]['unc_networksoft_num']);
        //         if($ret[$item-3]['unc_interlligencesoft_num'] != 0)
        //             $objPHPExcel->getActiveSheet()->setCellValue('Z'.($item+$liststart), $ret[$item-3]['unc_interlligencesoft_num']);

        //         $lesslist = $ret[$item-3]['lessproductlist'];
        //         if(count($lesslist) != 0 ){
        //             for($i=0;$i<count($lesslist);$i++){
        //                 $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+$item+$liststart), $lesslist[$i]['place_name']);
        //                 $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+$item+$liststart), $lesslist[$i]['brand_name']);
        //                 $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+$item+$liststart), $lesslist[$i]['model']);
        //                 $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+$item+$liststart), $lesslist[$i]['order_goods_manager_count']);
        //             }
        //         }
        //         if(count($lesslist) > 1){
        //             $liststart += (count($lesslist)-1);
        //         }
        //     }

        //     header('Content-Type: application/vnd.ms-excel');
        //     header('Content-Disposition: attachment;filename="'.$file_name.'.'.$file_extend.'"');
        //     header('Cache-Control: max-age=0');
        //     ob_clean();  //关键
        //     flush();     //关键
        //     if($file_extend == 'xlsx'){
        //         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
        //     }else if($file_extend == 'xls'){
        //         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //     }

        //     $objWriter->save("php://output");  /*输出至浏览器*/
        //     exit;        //关键
        // }

        /*导出更换确认单 代用确认单 维修确认单  退货确认单 配件确认单 借用确认单*/
        public static function exportcsinfo($file_name,$file_extend,$template_name,$ret){
            $root_url = $_SERVER['DOCUMENT_ROOT'];
            $file_name = iconv("utf-8","gb2312",$file_name);
            $template_name = iconv("utf-8","gb2312",$template_name);

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($root_url."/templates/".$template_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('sheet0');
            $list_num = 0;

            for($item=0; $item < count($ret); $item++){
                $ofg_productlist = $ret[$item]['ofg_productlist'];
                for($list=0; $list < count($ofg_productlist); $list++){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($list+2+$list_num), $ofg_productlist[$list]['model']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($list+2+$list_num), $ret[$item]['cs_id']);

                    if($ret[$item]['cs_info_state'] == 1){
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.($list+2+$list_num), '处理中');
                    }else if($ret[$item]['cs_info_state'] == 2){
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.($list+2+$list_num), '已完成');
                    }else if($ret[$item]['cs_info_state'] == 3){
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.($list+2+$list_num), '取消');
                    }else if($ret[$item]['cs_info_state'] == 4){
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.($list+2+$list_num), '备货');
                    }else if($ret[$item]['cs_info_state'] == 5){
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.($list+2+$list_num), '退回');
                    }else if($ret[$item]['cs_info_state'] == 6){
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.($list+2+$list_num), '缺货');
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($list+2+$list_num), $ofg_productlist[$list]['place_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($list+2+$list_num), $ofg_productlist[$list]['brand_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($list+2+$list_num), $ofg_productlist[$list]['product_type_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($list+2+$list_num), $ret[$item]['cs_belong_create_time']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($list+2+$list_num), $ret[$item]['build_department_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($list+2+$list_num), $ret[$item]['build_user_name']);

                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($list+2+$list_num), $ret[$item]['company_contact']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.($list+2+$list_num), $ret[$item]['company_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.($list+2+$list_num), $ret[$item]['delivery_info_receiver_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.($list+2+$list_num), $ret[$item]['delivery_info_receiver_address']);
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.($list+2+$list_num), $ret[$item]['delivery_info_goods_yard_name']);

                    if($ret[$item]['cs_info_type'] == 2 ){
                        $objPHPExcel->getActiveSheet()->setCellValue('AF'.($list+2+$list_num), $ofg_productlist[$list]['bar_code']);
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValue('O'.($list+2+$list_num), $ofg_productlist[$list]['bar_code']);
                    }
                    
                    if($ofg_productlist[$list]['ogl_product_state'] == '缺货'){
                        $objPHPExcel->getActiveSheet()->setCellValue('P'.($list+2+$list_num), 1);
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValue('P'.($list+2+$list_num), 0);
                    }
                    if($ret[$item]['cs_info_type'] == 4){
                        $objPHPExcel->getActiveSheet()->setCellValue('Q'.($list+2+$list_num), 1);
                    }else if($ret[$item]['cs_info_type'] == 1){
                        $objPHPExcel->getActiveSheet()->setCellValue('R'.($list+2+$list_num), 1);
                    }else if($ret[$item]['cs_info_type'] == 6){
                        $objPHPExcel->getActiveSheet()->setCellValue('S'.($list+2+$list_num), 1);
                    }else if($ret[$item]['cs_info_type'] == 2){
                        $objPHPExcel->getActiveSheet()->setCellValue('T'.($list+2+$list_num), 1);
                    }else if($ret[$item]['cs_info_type'] == 3){
                        $objPHPExcel->getActiveSheet()->setCellValue('U'.($list+2+$list_num), 1);
                    }else if($ret[$item]['cs_info_type'] == 5){
                        $objPHPExcel->getActiveSheet()->setCellValue('V'.($list+2+$list_num), 1);
                    }

                    if($ret[$item]['cs_info_type'] == 2){
                        $objPHPExcel->getActiveSheet()->setCellValue('AF'.($list+2+$list_num), $ofg_productlist[$list]['order_goods_manager_explain']);
                    }

                    if($ret[$item]['cs_info_type'] == 4){
                        $objPHPExcel->getActiveSheet()->setCellValue('AG'.($list+2+$list_num), $ofg_productlist[$list]['fault_condition']);
                    }

                    if($ret[$item]['cs_info_type'] == 6){
                        $objPHPExcel->getActiveSheet()->setCellValue('AH'.($list+2+$list_num), $ofg_productlist[$list]['replace_reason']);
                    }else if($ret[$item]['cs_info_type'] == 1){
                        $objPHPExcel->getActiveSheet()->setCellValue('AI'.($list+2+$list_num), $ofg_productlist[$list]['replace_reason']);
                    }

                    if($ret[$item]['cs_info_type'] == 3){
                        $objPHPExcel->getActiveSheet()->setCellValue('AJ'.($list+2+$list_num), $ofg_productlist[$list]['order_goods_manager_explain']);
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('AK'.($list+2+$list_num), $ofg_productlist[$list]['ogl_comment']);
                }
                $list_num += count($ofg_productlist);
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

        /*查询需要导出的订货确认单*/
        public static function queryexportgoodsconfirmorder($param){
            $sqlone = "select dsp_logistic.cs_belong.*,dsp_logistic.order_goods_cs_info.*,dsp_logistic.ofg_info.*,dsp_logistic.fee_info.* from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
            $sqlone .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
            /*查询条件*/
            $sqlone .= "where dsp_logistic.order_goods_cs_info.cs_info_state != '3'";
            if((property_exists($param,'startdate'))&&(property_exists($param,'enddate'))){
               $startdate = $param->startdate;
               $enddate = $param->enddate;
               $sqlone.= " and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
            }else if(property_exists($param,'startdate')){
                    $startdate = $param->startdate;
                    $sqlone.= " and cs_belong_create_time ='$startdate'";
            }else if(property_exists($param,'enddate')){
                    $enddate = $param->enddate;
                    $sqlone.= " and cs_belong_create_time ='$enddate'";
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
                if($orderstate == 3){
                    return null;
                }
                $sqlone.= " and cs_info_state ='$orderstate' ";
            }

            if(property_exists($param,'order_id')){
                $order_id = $param->order_id;
                $sqlone.= " and dsp_logistic.order_goods_cs_info.cs_id ='$order_id' ";
            }

            if(property_exists($param,'receiver_name')){
                $receiver_name = $param->receiver_name;
                $sqlone.= " and receiver_name ='$receiver_name' ";
            }

            /*运费付费模式*/
            if(property_exists($param,'freightmode')){
                $freightmode = intval($param->freightmode);
                $sqlone .= "and dsp_logistic.fee_info.transfer_mode = '$freightmode' ";
            }

            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;

            for ($i=0; $i < count($tableobj); $i++) {
                //非常规的产品明细
                $unc_ofg_info_id = intval($tableobj[$i]['unc_ofg_info_id']);
                $sqltwo = "select dsp_logistic.product_info.*,dsp_logistic.product_brand.*,dsp_logistic.product_type.product_type_name,dsp_logistic.product_place.place_name,dsp_logistic.unc_ofg_detail.*,dsp_logistic.unc_product.unc_product_name from dsp_logistic.unc_ofg_detail ";
                $sqltwo .= "left join dsp_logistic.unc_product on dsp_logistic.unc_product.unc_product_id = dsp_logistic.unc_ofg_detail.unc_product_id ";
                $sqltwo .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.unc_ofg_detail.product_info_id ";
                $sqltwo .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
                $sqltwo .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
                $sqltwo .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
                $sqltwo .= "where dsp_logistic.unc_ofg_detail.unc_ofg_info_id ='$unc_ofg_info_id' ";
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

                /*非常规订单*/
                if(property_exists($param,'customproduct')){
                    $customproduct = intval($param->customproduct);
                    $sqltwo .= "and dsp_logistic.unc_product.unc_product_id = '$customproduct' ";
                }

                $listobjone = Db::query($sqltwo);
                if(empty($listobjone)){
                    $tableobj[$i]['unc_productlist'] = [];
                }else{
                    $tableobj[$i]['unc_productlist'] = $listobjone;
                }
                
                /*查询非常规单*/
                if(!property_exists($param,'customproduct')){
                    //常规产品详细
                    $cs_id = $tableobj[$i]['cs_id'];
                    $sqlthree = "select dsp_logistic.product_info.*,dsp_logistic.product_brand.brand_name,dsp_logistic.product_type.product_type_name,dsp_logistic.product_place.place_name,dsp_logistic.order_goods_cs_undeliver_goods_info.* from dsp_logistic.order_goods_cs_undeliver_goods_info ";
                    $sqlthree .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_cs_undeliver_goods_info.product_info_id ";
                    $sqlthree .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
                    $sqlthree .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
                    $sqlthree .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
                    $sqlthree .= "where dsp_logistic.order_goods_cs_undeliver_goods_info.cs_id ='$cs_id' ";
                    if(property_exists($param,'productclass')){
                        $productclass = intval($param->productclass);
                        $sqlthree .= "and dsp_logistic.product_type.product_type_id = '$productclass' ";
                    }

                    /*品牌*/
                    if(property_exists($param,'brand')){
                        $brand = intval($param->brand);
                        $sqlthree .= "and dsp_logistic.product_brand.brand_id = '$brand' ";
                    }

                    /*产品型号*/
                    if(property_exists($param,'producttype')){
                        $producttype = $param->producttype;
                        $sqlthree .= "and dsp_logistic.product_info.model = '$producttype' ";
                    }

                    /*生产地*/
                    if(property_exists($param,'productarea')){
                        $productarea = intval($param->productarea);
                        $sqlthree .= "and dsp_logistic.product_place.place_id = '$productarea' ";
                    }

                    $listobjtwo = Db::query($sqlthree);
                    if(empty($listobjtwo)){
                        $tableobj[$i]['ofg_productlist'] = [];
                    }else{
                        $tableobj[$i]['ofg_productlist'] = $listobjtwo;
                    }

                    if((empty($listobjtwo))&&(empty($listobjone))){
                        if((property_exists($param,'productclass'))||(property_exists($param,'brand'))||(property_exists($param,'producttype'))||(property_exists($param,'productarea'))){
                            $tableobj[$i] = null;
                        }
                    }
                }else{
                    /*只查非常规的*/
                    $tableobj[$i]['ofg_productlist'] = [];
                    if(empty($listobjone)){
                        $tableobj[$i] = null;
                    }
                }
            }

            //删除数组中空值
            $newtableobj = array();
            for($item = 0 ; $item < count($tableobj) ; $item++){
                if($tableobj[$item] != null){
                    $newtableobj[] = $tableobj[$item];
                }
            }

            /*单独查询发货日期*/
            if(count($newtableobj) > 0){
                for($i=0; $i < count($newtableobj); $i++){
                    $cs_id = $newtableobj[$i]['cs_id'];
                    $sqlfour = "select dsp_logistic.logistics_info.* from dsp_logistic.logistics_info where cs_id='$cs_id'";
                    $dateobj = Db::query($sqlfour);
                    $newtableobj[$i]['logistic_date'] = $dateobj;
                }
            }

            /*统计出缺货，产品分类，非常规数据*/
            for($i = 0 ; $i < count($newtableobj);$i++){
                $less_num = 0 ;
                $less_total =  0;
                /*公共广播，会议等产品数量*/
                // $broadcast_num = 0 ;
                // $meeting_num = 0 ;
                // $subway_num = 0 ;
                // $auxdi_num = 0 ;
                // $record_num = 0 ;

                /*非常规产品数量*/
                // $unc_develop_num = 0 ;
                // $unc_special_num = 0;
                // $unc_network_num = 0 ;
                // $unc_interlligence_num = 0;
                // $unc_address_num = 0;
                // $unc_register_num = 0;
                // $unc_networksoft_num = 0;
                // $unc_interlligencesoft_num = 0;

                /*缺货产品列表*/
                //$lessproductlist = array();

                $ofg_productlist = $newtableobj[$i]['ofg_productlist'];
                for($j=0 ; $j <count($ofg_productlist); $j++ ){
                    // if($ofg_productlist[$j]['product_type_name'] == '公共广播')
                    //     $broadcast_num++;
                    // if($ofg_productlist[$j]['product_type_name'] == '会议系统')
                    //     $meeting_num++;
                    // if($ofg_productlist[$j]['product_type_name'] == '地铁事业')
                    //     $subway_num++;
                    // if($ofg_productlist[$j]['product_type_name'] == '澳斯迪')
                    //     $auxdi_num++;
                    // if($ofg_productlist[$j]['product_type_name'] == '录播')
                    //     $record_num++;

                    if($ofg_productlist[$j]['ogcugi_product_state'] == 5){
                        $less_num = 1;
                        $less_total++;
                        // $model_param['place_name'] = $ofg_productlist[$j]['place_name'];
                        // $model_param['brand_name'] = $ofg_productlist[$j]['brand_name'];
                        // $model_param['model'] = $ofg_productlist[$j]['model'];
                        // $model_param['ogcugi_count'] = $ofg_productlist[$j]['ogcugi_count'];
                        // $lessproductlist[] = $model_param;
                    }
                }

                /*统计各常规产品数量*/
                // $tableobj[$i]['broadcast_num'] = $broadcast_num;
                // $tableobj[$i]['meeting_num'] = $meeting_num;
                // $tableobj[$i]['subway_num'] = $subway_num;
                // $tableobj[$i]['auxdi_num'] = $auxdi_num;
                // $tableobj[$i]['record_num'] = $record_num;

                //缺货产品列表
                //$tableobj[$i]['lessproductlist'] = $lessproductlist;
                $newtableobj[$i]['less_num'] = $less_num;
                $newtableobj[$i]['less_total'] = $less_total;
                // $unc_productlist = $tableobj[$i]['unc_productlist'];
                // for($k = 0 ; $k < count($unc_productlist) ; $k++){
                //     if($unc_productlist[$k]['unc_product_name'] == '研发'){
                //         $unc_develop_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                //     if($unc_productlist[$k]['unc_product_name'] == '特殊'){
                //         $unc_special_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                //     if($unc_productlist[$k]['unc_product_name'] == '网络化'){
                //         $unc_network_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                //     if($unc_productlist[$k]['unc_product_name'] == '智能化'){
                //         $unc_interlligence_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                //     if($unc_productlist[$k]['unc_product_name'] == '可寻址'){
                //         $unc_address_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                //     if($unc_productlist[$k]['unc_product_name'] == '注册码'){
                //         $unc_register_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                //     if($unc_productlist[$k]['unc_product_name'] == '网络化软件'){
                //         $unc_networksoft_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                //     if($unc_productlist[$k]['unc_product_name'] == '智能化软件'){
                //         $unc_interlligencesoft_num += intval($unc_productlist[$k]['uod_count']);
                //     }
                // }

                // /*统计各非常规产品数量*/
                // $tableobj[$i]['unc_develop_num'] = $unc_develop_num;
                // $tableobj[$i]['unc_special_num'] = $unc_special_num;
                // $tableobj[$i]['unc_network_num'] = $unc_network_num;
                // $tableobj[$i]['unc_interlligence_num'] = $unc_interlligence_num;
                // $tableobj[$i]['unc_address_num'] = $unc_address_num;
                // $tableobj[$i]['unc_register_num'] = $unc_register_num;
                // $tableobj[$i]['unc_networksoft_num'] = $unc_networksoft_num;
                // $tableobj[$i]['unc_interlligencesoft_num'] = $unc_interlligencesoft_num;

                // /*发货日期*/
                $logistic_date = $newtableobj[$i]['logistic_date'];
                $delivery_logistic_date = "";
                $delivery_logistic_yard = "";
                for($l = 0 ; $l < count($logistic_date) ; $l++){
                    $delivery_logistic_date .= $logistic_date[$l]['delivery_date'].',';
                    $delivery_logistic_yard .= $logistic_date[$l]['goods_yard_name'].',';
                }
                $newtableobj[$i]['delivery_logistic_date'] = $delivery_logistic_date;
                $newtableobj[$i]['delivery_logistic_yard'] = $delivery_logistic_yard;
            }
            return $newtableobj;
        }

        public static function queryexportordergoodsinfo($param){
            $sqlone = "select dsp_logistic.cs_belong.*,dsp_logistic.order_goods_cs_info.*,dsp_logistic.ofg_info.*,dsp_logistic.fee_info.* from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
            $sqlone .= "left join dsp_logistic.fee_info on dsp_logistic.fee_info.fee_info_id = dsp_logistic.order_goods_cs_info.fee_info_id ";
            /*查询条件*/
            $sqlone .= "where dsp_logistic.order_goods_cs_info.cs_info_state != '3'";
            if((property_exists($param,'startdate'))&&(property_exists($param,'enddate'))){
               $startdate = $param->startdate;
               $enddate = $param->enddate;
               $sqlone.= " and cs_belong_create_time >='$startdate' and cs_belong_create_time <='$enddate' ";
            }else if(property_exists($param,'startdate')){
                    $startdate = $param->startdate;
                    $sqlone.= " and cs_belong_create_time ='$startdate'";
            }else if(property_exists($param,'enddate')){
                    $enddate = $param->enddate;
                    $sqlone.= " and cs_belong_create_time ='$enddate'";
            }

            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;
            for ($i=0; $i < count($tableobj); $i++) {
                //常规产品详细
                $cs_id = $tableobj[$i]['cs_id'];
                $sqlthree = "select dsp_logistic.product_info.*,dsp_logistic.product_brand.brand_name,dsp_logistic.product_type.product_type_name,dsp_logistic.product_place.place_name,dsp_logistic.order_goods_cs_undeliver_goods_info.* from dsp_logistic.order_goods_cs_undeliver_goods_info ";
                $sqlthree .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_cs_undeliver_goods_info.product_info_id ";
                $sqlthree .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
                $sqlthree .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
                $sqlthree .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
                $sqlthree .= "where dsp_logistic.order_goods_cs_undeliver_goods_info.cs_id ='$cs_id' and dsp_logistic.order_goods_cs_undeliver_goods_info.ogcugi_product_state='5'";
                /*品牌*/
                if(property_exists($param,'brand')){
                    $brand = intval($param->brand);
                    $sqlthree .= "and dsp_logistic.product_brand.brand_id = '$brand' ";
                }

                /*产品型号*/
                if(property_exists($param,'producttype')){
                    $producttype = $param->producttype;
                    $sqlthree .= "and dsp_logistic.product_info.model = '$producttype' ";
                }

                /*生产地*/
                if(property_exists($param,'productarea')){
                    $productarea = intval($param->productarea);
                    $sqlthree .= "and dsp_logistic.product_place.place_id = '$productarea' ";
                }

                $listobjtwo = Db::query($sqlthree);
                if(empty($listobjtwo)){
                    $tableobj[$i] = null;
                }else{
                    $tableobj[$i]['ofg_productlist'] = $listobjtwo;
                }
            }

            $newtableobj = array();
            for($item = 0 ; $item < count($tableobj) ; $item++){
                if($tableobj[$item] != null){
                    $newtableobj[] = $tableobj[$item];
                }
            }
            return $newtableobj;
        }

        /*导出缺货部分*/
        public static function exportoutofstackinfo($file_name,$file_extend,$template_name,$ret){
            $root_url = $_SERVER['DOCUMENT_ROOT'];
            $file_name = iconv("utf-8","gb2312",$file_name);
            $template_name = iconv("utf-8","gb2312",$template_name);

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($root_url."/templates/".$template_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('sheet0');
            $list_num = 0;

            for($item=0; $item < count($ret); $item++){
                $ofg_productlist = $ret[$item]['ofg_productlist'];
                for($list=0; $list < count($ofg_productlist); $list++){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.($list+2+$list_num), $ofg_productlist[$list]['model']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.($list+2+$list_num), $ofg_productlist[$list]['product_info_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.($list+2+$list_num), $ofg_productlist[$list]['product_type_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.($list+2+$list_num), $ofg_productlist[$list]['brand_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($list+2+$list_num), $ofg_productlist[$list]['place_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.($list+2+$list_num), $ofg_productlist[$list]['ogcugi_unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.($list+2+$list_num), $ofg_productlist[$list]['ogcugi_count']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.($list+2+$list_num), $ret[$item]['cs_belong_create_time']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($list+2+$list_num), $ret[$item]['cs_id']);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($list+2+$list_num), $ret[$item]['build_department_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.($list+2+$list_num), $ret[$item]['build_user_name']);                    
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.($list+2+$list_num), $ret[$item]['company_address']);                    
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.($list+2+$list_num), $ret[$item]['receiver_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.($list+2+$list_num), $ofg_productlist[$list]['ogcugi_comment']);
                }
                $list_num += count($ofg_productlist);
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

        /*导出订货确认单*/
        public static function exportgoodsconfirmorder($file_name,$file_extend,$template_name,$ret){
            $root_url = $_SERVER['DOCUMENT_ROOT'];
            $file_name = iconv("utf-8","gb2312",$file_name);
            $template_name = iconv("utf-8","gb2312",$template_name);

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($root_url."/templates/".$template_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('sheet0');

            $liststart = 0 ;
            for($item=3; $item < count($ret)+3; $item++){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($item+$liststart), $ret[$item-3]['cs_belong_create_time']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($item+$liststart), $ret[$item-3]['delivery_logistic_date']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($item+$liststart), $ret[$item-3]['cs_id']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($item+$liststart), $ret[$item-3]['build_department_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($item+$liststart), $ret[$item-3]['build_user_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($item+$liststart), $ret[$item-3]['company_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.($item+$liststart), $ret[$item-3]['receiver_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.($item+$liststart), $ret[$item-3]['delivery_logistic_yard']);
                /*订单状态*/
                if($ret[$item-3]['cs_info_state'] == 1){
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($item+$liststart), '处理中');
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($item+$liststart), 1);
                }else if($ret[$item-3]['cs_info_state'] == 2){
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($item+$liststart), '已完成');
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($item+$liststart), 1);
                }else if($ret[$item-3]['cs_info_state'] == 3){
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($item+$liststart), '取消');
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($item+$liststart), 1);
                }else if($ret[$item-3]['cs_info_state'] == 4){
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($item+$liststart), '备货');
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($item+$liststart), 1);
                }else if($ret[$item-3]['cs_info_state'] == 5){
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($item+$liststart), '退回');
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($item+$liststart), 1);
                }else if($ret[$item-3]['cs_info_state'] == 6){
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.($item+$liststart), '缺货');
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.($item+$liststart), 1);
                }

                $objPHPExcel->getActiveSheet()->setCellValue('K'.($item+$liststart), $ret[$item-3]['less_num']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.($item+$liststart), $ret[$item-3]['delivered_total']);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($item+$liststart), $ret[$item-3]['less_total']);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($item+$liststart), $ret[$item-3]['delivered_pa']);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($item+$liststart), $ret[$item-3]['delivered_conference']);
                $objPHPExcel->getActiveSheet()->setCellValue('P'.($item+$liststart), $ret[$item-3]['delivered_customization']);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.($item+$liststart), $ret[$item-3]['delivered_record']);
                $objPHPExcel->getActiveSheet()->setCellValue('R'.($item+$liststart), $ret[$item-3]['delivered_metro']);
                $objPHPExcel->getActiveSheet()->setCellValue('S'.($item+$liststart), $ret[$item-3]['delivered_aux']);
                $objPHPExcel->getActiveSheet()->setCellValue('T'.($item+$liststart), $ret[$item-3]['delivered_gift']);
                $objPHPExcel->getActiveSheet()->setCellValue('U'.($item+$liststart), $ret[$item-3]['delivered_album']);

                /*以下部分保留*/
                // /*常规产品统计*/
                // if($ret[$item-3]['broadcast_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('N'.($item+$liststart), $ret[$item-3]['broadcast_num']);
                // if($ret[$item-3]['meeting_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('O'.($item+$liststart), $ret[$item-3]['meeting_num']);
                // if($ret[$item-3]['subway_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('P'.($item+$liststart), $ret[$item-3]['subway_num']);
                // if($ret[$item-3]['auxdi_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('Q'.($item+$liststart), $ret[$item-3]['auxdi_num']);
                // if($ret[$item-3]['record_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('R'.($item+$liststart), $ret[$item-3]['record_num']);

                // /*非常规产品统计*/
                // if($ret[$item-3]['unc_develop_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('S'.($item+$liststart), $ret[$item-3]['unc_develop_num']);
                // if($ret[$item-3]['unc_special_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('T'.($item+$liststart), $ret[$item-3]['unc_special_num']);
                // if($ret[$item-3]['unc_network_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('U'.($item+$liststart), $ret[$item-3]['unc_network_num']);
                // if($ret[$item-3]['unc_interlligence_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('V'.($item+$liststart), $ret[$item-3]['unc_interlligence_num']);
                // if($ret[$item-3]['unc_address_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('W'.($item+$liststart), $ret[$item-3]['unc_address_num']);
                // if($ret[$item-3]['unc_register_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('X'.($item+$liststart), $ret[$item-3]['unc_register_num']);
                // if($ret[$item-3]['unc_networksoft_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('Y'.($item+$liststart), $ret[$item-3]['unc_networksoft_num']);
                // if($ret[$item-3]['unc_interlligencesoft_num'] != 0)
                // $objPHPExcel->getActiveSheet()->setCellValue('Z'.($item+$liststart), $ret[$item-3]['unc_interlligencesoft_num']);

                // $lesslist = $ret[$item-3]['lessproductlist'];
                // if(count($lesslist) != 0 ){
                //     for($i=0;$i<count($lesslist);$i++){
                //         $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+$item+$liststart), $lesslist[$i]['place_name']);
                //         $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+$item+$liststart), $lesslist[$i]['brand_name']);
                //         $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+$item+$liststart), $lesslist[$i]['model']);
                //         $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+$item+$liststart), $lesslist[$i]['ogcugi_count']);
                //     }
                // }
                // if(count($lesslist) > 1){
                //     $liststart += (count($lesslist)-1);
                // }
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

        /*根据流水号和类型查询需打印的更换(0x01)/代用(0x06)/维修(0x04)/退货(0x03)/借样(0x02)确认单*/
        public static function queryprintcsinfoorder($cs_id,$type){
            $sqlone ="select dsp_logistic.cs_belong.*,dsp_logistic.cs_info.*,";
            if($type != 0x03){
                $sqlone .= "dsp_logistic.delivery_info.*,";
            }
            $sqlone .= "dsp_logistic.custom_info.*";
            if($type != 0x02){
                $sqlone .= ",dsp_logistic.return_info.* ";
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

            /*查询其他说明和物流情况说明*/
            $sqlthree = "select dsp_logistic.order_goods_manager.*, dsp_logistic.order_goods_logistics.* from dsp_logistic.order_goods_manager ";
            $sqlthree .= "left join dsp_logistic.order_goods_logistics on dsp_logistic.order_goods_logistics.order_goods_manager_id = dsp_logistic.order_goods_manager.order_goods_manager_id ";
            $sqlthree .= "where dsp_logistic.order_goods_manager.cs_id='$cs_id'";
            $explainobj = Db::query($sqlthree);

            if(!empty($explainobj)){
                $tableobj[0]['order_goods_manager_explain'] = $explainobj[0]['order_goods_manager_explain'];
                $tableobj[0]['ogl_explain'] = $explainobj[0]['ogl_explain'];
            }else{
                $tableobj[0]['order_goods_manager_explain'] = '';
                $tableobj[0]['ogl_explain'] = '';
            }

            return $tableobj;
        }

        /*根据流水号查询需打印的非定型产品订货单*/
        public static function queryprintuncofginfoorder($uoi_id){
            $sqlone = "select dsp_logistic.unc_ofg_info.* from dsp_logistic.unc_ofg_info where dsp_logistic.unc_ofg_info.uoi_id='$uoi_id'";
            $tableobj = Db::query($sqlone);

            $sqltwo = "select dsp_logistic.unc_ofg_info.*,dsp_logistic.unc_ofg_detail.*,dsp_logistic.product_info.* ";
            $sqltwo .= "from dsp_logistic.unc_ofg_info ";
            $sqltwo .= "left join dsp_logistic.unc_ofg_detail on dsp_logistic.unc_ofg_detail.unc_ofg_info_id = dsp_logistic.unc_ofg_info.uoi_id ";
            $sqltwo .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.unc_ofg_detail.product_info_id ";
            $sqltwo .= "where dsp_logistic.unc_ofg_info.uoi_id='$uoi_id' ";
            $listobj = Db::query($sqltwo);

            $tableobj[0]['productlist'] = $listobj;

            return $tableobj;
        }

        /*打印更换,代用，借样，维修，退货,配件确认单*/
        public static function printreplaceconfirmorder($file_name,$file_extend,$template_name,$ret,$type){
            $root_url = $_SERVER['DOCUMENT_ROOT'];
            $file_name = iconv("utf-8","gb2312",$file_name);
            $template_name = iconv("utf-8","gb2312",$template_name);

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($root_url."/templates/".$template_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('sheet0');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', "日期：".$ret[0]['cs_belong_create_time']);
            if($type != 0x02){
                $objPHPExcel->getActiveSheet()->setCellValue('L1', "部门编号：".$ret[0]['build_department_id']);
            }
            else{
                $objPHPExcel->getActiveSheet()->setCellValue('J1', "部门编号：".$ret[0]['build_department_id']);
            }

            $objPHPExcel->getActiveSheet()->setCellValue('C2', $ret[0]['build_department_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G2', $ret[0]['build_user_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('K2', $ret[0]['build_user_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('C3', $ret[0]['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('K3', $ret[0]['company_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('C4', $ret[0]['company_address']);
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $ret[0]['legal_representative']);
            $objPHPExcel->getActiveSheet()->setCellValue('I5', $ret[0]['legal_phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('C6', $ret[0]['company_contact']);
            $objPHPExcel->getActiveSheet()->setCellValue('I6', $ret[0]['company_contact_phone']);

            if($type != 0x03){
                $objPHPExcel->getActiveSheet()->setCellValue('C8', $ret[0]['delivery_info_receiver_name']);
                if($ret[0]['is_insure'] == 1){
                    $objPHPExcel->getActiveSheet()->setCellValue('I8','买');
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue('I8','不买');
                }
                $objPHPExcel->getActiveSheet()->setCellValue('C9', $ret[0]['delivery_info_receiver_phone']);
                $objPHPExcel->getActiveSheet()->setCellValue('I9', $ret[0]['insure_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C10', $ret[0]['delivery_info_goods_yard_name']);
                if($ret[0]['is_sign'] == 1){
                    $objPHPExcel->getActiveSheet()->setCellValue('I10', '签约');
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue('I10', '非签约');
                }

                $objPHPExcel->getActiveSheet()->setCellValue('C11', $ret[0]['delivery_info_goods_yard_phone']);
                if($ret[0]['has_contract'] == 1){
                    $objPHPExcel->getActiveSheet()->setCellValue('I11', '有');
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue('I11', '无');
                }

                $objPHPExcel->getActiveSheet()->setCellValue('C12', $ret[0]['delivery_info_receiver_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('C13', $ret[0]['order_delivery_require']);
            }else{
                $objPHPExcel->getActiveSheet()->setCellValue('C10', $ret[0]['return_info_goods_yard_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('C11', $ret[0]['return_info_goods_yard_phone']);
                $objPHPExcel->getActiveSheet()->setCellValue('C12', $ret[0]['return_info_receiver_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('C13', $ret[0]['return_order_num']);
            }


            if(($type != 0x02)&&($type != 0x03)&&($type != 0x05)){
                $objPHPExcel->getActiveSheet()->setCellValue('C16', $ret[0]['return_info_goods_yard_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('C17', $ret[0]['return_info_goods_yard_phone']);
                $objPHPExcel->getActiveSheet()->setCellValue('C18', $ret[0]['return_info_receiver_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('C19', $ret[0]['return_order_num']);
            }

            if(($type == 0x01)||($type == 0x06)||($type == 0x04)||($type == 0x05)){
                $startitem = 21;
            }

            if(($type == 0x02)||($type == 0x03)){
                $startitem = 15;
            }

            $productlist = $ret[0]['productlist'];
            if( count($productlist) > 0 ){
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$startitem, $productlist[0]['product_type_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$startitem, $productlist[0]['brand_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$startitem, $productlist[0]['product_info_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$startitem, $productlist[0]['model']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$startitem, $productlist[0]['specification']);

                if($type == 0x05){
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$startitem, $productlist[0]['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$startitem, $productlist[0]['order_goods_manager_count']);
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$startitem, $productlist[0]['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$startitem, $productlist[0]['order_goods_manager_count']);
                }

                if(($type == 0x01)||($type == 0x06)){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$startitem, $productlist[0]['bar_code']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$startitem, $productlist[0]['back_date']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$startitem, $productlist[0]['replace_reason']);
                }

                if($type == 0x02){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$startitem, $productlist[0]['back_date']);
                    //$objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-$startitem]['unit_price']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$startitem, "");
                }

                if($type == 0x03){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$startitem, $productlist[0]['bar_code']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$startitem, $productlist[0]['purchase_date']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$startitem, $productlist[0]['unit_price']);
                }

                if($type == 0x04){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$startitem, $productlist[0]['deal_date']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$startitem, $productlist[0]['bar_code']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$startitem, $productlist[0]['fault_condition']);
                }

                if($type == 0x05){
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$startitem, $productlist[0]['unit_price']);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$startitem, $productlist[0]['fault_condition']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$startitem, $productlist[0]['comment']);
                }
            }

            /*其他说明和情况说明暂时都没写*/
            if(($type == 0x01)||($type == 0x06)){
                $objPHPExcel->getActiveSheet()->setCellValue('B22', $ret[0]['order_goods_manager_explain']);
                $objPHPExcel->getActiveSheet()->setCellValue('A23', "情况说明（物流部）: ".$ret[0]['ogl_explain']);
            }else if($type == 0x02){
                $objPHPExcel->getActiveSheet()->setCellValue('B16', $ret[0]['order_goods_manager_explain']);
                $objPHPExcel->getActiveSheet()->setCellValue('A17', "情况说明（物流部）: ".$ret[0]['ogl_explain']);
            }else if($type == 0x03){
                $objPHPExcel->getActiveSheet()->setCellValue('B16', $ret[0]['order_goods_manager_explain']);
                $objPHPExcel->getActiveSheet()->setCellValue('A17', "情况说明（物流部）: ".$ret[0]['ogl_explain']);
            }else if($type == 0x04){
                $objPHPExcel->getActiveSheet()->setCellValue('A22', "情况说明（物流部）: ".$ret[0]['ogl_explain']);
            }

            /*超过默认行数*/
            if(count($productlist) > 1){
                $newrows = count($productlist) - 1;
                if(($type == 0x01)||($type == 0x06)){
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore(22,$newrows);
                    for($i=0; $i<$newrows;$i++){
                        $objPHPExcel->getActiveSheet()->mergeCells('F'.(22+$i).':'.'G'.(22+$i));
                    }
                }else if($type == 0x02){
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore(16,$newrows);
                    for($i=0; $i<$newrows;$i++){
                        $objPHPExcel->getActiveSheet()->mergeCells('F'.(16+$i).':'.'G'.(16+$i));
                    }
                }else if($type == 0x03){
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore(16,$newrows);
                    for($i=0; $i<$newrows;$i++){
                        $objPHPExcel->getActiveSheet()->mergeCells('F'.(16+$i).':'.'G'.(16+$i));
                    }
                }else if($type == 0x04){
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore(22,$newrows);
                    for($i=0; $i<$newrows;$i++){
                        $objPHPExcel->getActiveSheet()->mergeCells('F'.(22+$i).':'.'G'.(22+$i));
                        $objPHPExcel->getActiveSheet()->mergeCells('L'.(22+$i).':'.'M'.(22+$i));
                    }
                }else if($type == 0x05){
                    $objPHPExcel->getActiveSheet()->insertNewRowBefore(22,$newrows);
                    for($i=0; $i<$newrows;$i++){
                        $objPHPExcel->getActiveSheet()->mergeCells('K'.(22+$i).':'.'L'.(22+$i));
                    }
                }
               
                for($item=$startitem+1;$item<(count($productlist)+$startitem);$item++){
                    if(($type == 0x01)||($type == 0x06)||($type == 0x04)||($type == 0x05)){
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$item, $item-20);
                    }else if(($type == 0x02)||($type == 0x03)){
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$item, $item-14);
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$item, $productlist[$item-$startitem]['product_type_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$item, $productlist[$item-$startitem]['brand_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$item, $productlist[$item-$startitem]['product_info_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$item, $productlist[$item-$startitem]['model']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$item, $productlist[$item-$startitem]['specification']);
                    
                    if($type == 0x05){
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$item, $productlist[$item-$startitem]['unit']);
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$item, $productlist[$item-$startitem]['order_goods_manager_count']);
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$item, $productlist[$item-$startitem]['unit']);
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$item, $productlist[$item-$startitem]['order_goods_manager_count']);

                    }
                    if(($type == 0x01)||($type == 0x06)){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$item, $productlist[$item-$startitem]['bar_code']);
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-$startitem]['back_date']);
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$item, $productlist[$item-$startitem]['replace_reason']);
                    }

                    if($type == 0x02){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$item, $productlist[$item-$startitem]['back_date']);
                        //$objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-$startitem]['unit_price']);
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, "");
                    }

                    if($type == 0x03){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$item, $productlist[$item-$startitem]['bar_code']);
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-$startitem]['purchase_date']);
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$item, $productlist[$item-$startitem]['unit_price']);
                    }

                    if($type == 0x04){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$item, $productlist[$item-$startitem]['deal_date']);
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-$startitem]['bar_code']);
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$item, $productlist[$item-$startitem]['fault_condition']);
                    }

                    if($type == 0x05){
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$item, $productlist[$item-$startitem]['unit_price']);
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$item, $productlist[$item-$startitem]['fault_condition']);
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-$startitem]['comment']);
                    }
                }
            }

            $objWriteHTML = PHPExcel_IOFactory::createWriter($objPHPExcel,'HTML');
            $objWriteHTML->save("php://output");  /*输出至浏览器*/
            exit;                                 //关键
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
            $objPHPExcel->getActiveSheet()->setCellValue('A2', "TO:".$ret->uoi_to);
            $objPHPExcel->getActiveSheet()->setCellValue('C3', $ret->uoi_manual_ofg_id);
            $objPHPExcel->getActiveSheet()->setCellValue('H3', $ret->uoi_custom_name);
            $objPHPExcel->getActiveSheet()->setCellValue('C4', $ret->user_name);
            $objPHPExcel->getActiveSheet()->setCellValue('H4', $ret->uoi_date);
            $objPHPExcel->getActiveSheet()->setCellValue('L4', $ret->uoi_to_place);

            $productlist = $ret->productlist;
            if(count($productlist) > 0){
                $objPHPExcel->getActiveSheet()->setCellValue('B7', $productlist[0]->model);
                $objPHPExcel->getActiveSheet()->setCellValue('C7', $productlist[0]->uod_count);
                $objPHPExcel->getActiveSheet()->setCellValue('D7', $productlist[0]->uod_unit);
                $objPHPExcel->getActiveSheet()->setCellValue('E7', $productlist[0]->uod_requirement);
                $objPHPExcel->getActiveSheet()->setCellValue('K7', $productlist[0]->uod_delivery_date);
                $objPHPExcel->getActiveSheet()->setCellValue('N7', $productlist[0]->uod_comment);
            }
            // for($item=7;$item<(count($productlist)+7);$item++){
            //     $objPHPExcel->getActiveSheet()->setCellValue('B'.$item, $productlist[$item-7]->model);
            //     $objPHPExcel->getActiveSheet()->setCellValue('C'.$item, $productlist[$item-7]->uod_count);
            //     $objPHPExcel->getActiveSheet()->setCellValue('D'.$item, $productlist[$item-7]->uod_unit);
            //     $objPHPExcel->getActiveSheet()->setCellValue('E'.$item, $productlist[$item-7]->uod_requirement);
            //     $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-7]->uod_delivery_date);
            //     $objPHPExcel->getActiveSheet()->setCellValue('N'.$item, $productlist[$item-7]->uod_comment);
            // }

            $objPHPExcel->getActiveSheet()->setCellValue('A8', "项目名称：".$ret->uoi_project_name);
            $objPHPExcel->getActiveSheet()->setCellValue('A9', "提供商：".$ret->uoi_provider_name);

            /*超过默认行数*/
            if(count($productlist) > 1){
                /*插入多行数据*/
                $newrows = count($productlist) - 1;
                $objPHPExcel->getActiveSheet()->insertNewRowBefore(8,$newrows);
                for($i=0; $i<$newrows;$i++){
                    $objPHPExcel->getActiveSheet()->mergeCells('E'.(8+$i).':'.'J'.(8+$i));
                    $objPHPExcel->getActiveSheet()->mergeCells('K'.(8+$i).':'.'L'.(8+$i));
                    $objPHPExcel->getActiveSheet()->mergeCells('N'.(8+$i).':'.'Q'.(8+$i));
                }

                for($item=8;$item<(count($productlist)+8-1);$item++){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$item, $item-6);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$item, $productlist[$item-7]->model);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$item, $productlist[$item-7]->uod_count);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$item, $productlist[$item-7]->uod_unit);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$item, $productlist[$item-7]->uod_requirement);
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$item, $productlist[$item-7]->uod_delivery_date);
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$item, $productlist[$item-7]->uod_comment);
                }
            }

            // header('Content-Type: application/vnd.ms-excel');
            // header('Content-Disposition: attachment;filename="'.$file_name.'.'.$file_extend.'"');
            // header('Cache-Control: max-age=0');
            // ob_clean();  //关键
            // flush();     //关键
            // if($file_extend == 'xlsx'){
            //     $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
            // }else if($file_extend == 'xls'){
            //     $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            // }

            $objWriteHTML = PHPExcel_IOFactory::createWriter($objPHPExcel,'HTML');
            $objWriteHTML->save("php://output");  /*输出至浏览器*/
            exit;                                 //关键
        }


        /*获取非常规产品 unc_product*/
        public static function getuncproduct(){
            $sql = "SELECT * FROM dsp_logistic.unc_product";
            return Db::query($sql);
        }


        /*2020-03-23 增*/
        public static function addOrderGoodsCsInfo($info){
            Db::startTrans();
            try{
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
                $cs_info_state = $info['cs_info_state'];
                $cs_comment = $info['cs_comment'];
                $sql_value ="'{$cs_id}','{$ofg_info_id}','{$fee_info_id}','{$delivery_date_reply}','{$unc_ofg_info_id}','{$consult_sheet_file}','{$delivered_total}',";
                $sql_value .= "'{$delivered_pa}','{$delivered_conference}','{$delivered_customization}','{$delivered_record}','{$delivered_metro}','{$delivered_aux}',";
                $sql_value .= "'{$delivered_gift}','{$delivered_album}','{$product_number}','{$order_date}','{$cs_info_state}','{$cs_comment}'";
                $sql = "INSERT INTO dsp_logistic.order_goods_cs_info (cs_id,ofg_info_id,fee_info_id,delivery_date_reply,unc_ofg_info_id,consult_sheet_file,";
                $sql .= "delivered_total,delivered_pa,delivered_conference,delivered_customization,delivered_record,delivered_metro,delivered_aux,";
                $sql .= "delivered_gift,delivered_album,product_number,order_date,cs_info_state,cs_comment) VALUES ({$sql_value})";

                Db::execute($sql);
                Db::commit();
                return $cs_id;
            }
            catch (Exception $ex){
                Db::rollback();
                return false;
            }

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
            $cs_info_state = $info['cs_info_state'];
            $cs_comment = $info['cs_comment'];
            $sql_value ="'{$cs_id}','{$ofg_info_id}','{$fee_info_id}','{$delivery_date_reply}','{$unc_ofg_info_id}','{$consult_sheet_file}','{$delivered_total}',";
            $sql_value .= "'{$delivered_pa}','{$delivered_conference}','{$delivered_customization}','{$delivered_record}','{$delivered_metro}','{$delivered_aux}',";
            $sql_value .= "'{$delivered_gift}','{$delivered_album}','{$product_number}','{$order_date}','{$cs_info_state}','{$cs_comment}'";
            $sql = "INSERT INTO dsp_logistic.order_goods_cs_info (cs_id,ofg_info_id,fee_info_id,delivery_date_reply,unc_ofg_info_id,consult_sheet_file,";
            $sql .= "delivered_total,delivered_pa,delivered_conference,delivered_customization,delivered_record,delivered_metro,delivered_aux,";
            $sql .= "delivered_gift,delivered_album,product_number,order_date,cs_info_state,cs_comment) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE cs_id = '{$cs_id}',ofg_info_id = '{$ofg_info_id}',fee_info_id = '{$fee_info_id}',delivery_date_reply = '{$delivery_date_reply}'";
            $sql .= ",unc_ofg_info_id = '{$unc_ofg_info_id}',consult_sheet_file = '{$consult_sheet_file}',delivered_total = '{$delivered_total}'";
            $sql .=",delivered_pa = '{$delivered_pa}',delivered_conference = '{$delivered_conference}',delivered_customization = '{$delivered_customization}'";
            $sql .= ",delivered_record = '{$delivered_record}',delivered_metro = '{$delivered_metro}',delivered_aux = '{$delivered_aux}'";
            $sql .= ",delivered_gift = '{$delivered_gift}',delivered_album = '{$delivered_album}',product_number = '{$product_number}',order_date = '{$order_date}',cs_info_state = '{$cs_info_state}',cs_comment = '{$cs_comment}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*订单信息 ofg_info*/
        public static function updateofginfo($info){
            $ofg_info_id = $info['ofg_info_id'];
            $receiver_name = $info['receiver_name'];
            $receiver_phone = $info['receiver_phone'];
            $receiver_address = $info['receiver_address'];
            $company_name = $info['company_name'];
            $company_address = $info['company_address'];
            $user_id = $info['user_id'];
            $sql_value ="'{$ofg_info_id}','{$receiver_name}','{$receiver_phone}','{$receiver_address}','{$user_id}','{$company_name}','{$company_address}'";
            $sql = "INSERT INTO dsp_logistic.ofg_info (ofg_info_id,receiver_name,receiver_phone,receiver_address,user_id,company_name,company_address) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE receiver_name = '{$receiver_name}',receiver_phone = '{$receiver_phone}',receiver_address = '{$receiver_address}'";
            $sql .= ",user_id = '{$user_id}',company_name='{$company_name}',company_address='{$company_address}'";
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
            try{
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
            catch (Exception $ex)
            {
                return false;
            }

        }

        /*非常规订单 unc_ofg_info*/
        public static function updateunc_ofg_info($info){
            $uoi_id = $info['uoi_id'];
            $uoi_manual_ofg_id = $info['uoi_manual_ofg_id'];
            $uoi_to = $info['uoi_to'];
            $uoi_custom_name = $info['uoi_custom_name'];
            $uoi_to_place = $info['uoi_to_place'];
            $user_name = $info['user_name'];
            $uoi_date = $info['uoi_date'];
            $uoi_project_name = $info['uoi_project_name'];
            $uoi_provider_name = $info['uoi_provider_name'];
            $sql_value ="'{$uoi_id}','{$uoi_manual_ofg_id}','{$uoi_to}','{$uoi_custom_name}','{$uoi_to_place}','{$user_name}','{$uoi_date}','{$uoi_project_name}','{$uoi_provider_name}'";
            $sql = "INSERT INTO dsp_logistic.unc_ofg_info (uoi_id,uoi_manual_ofg_id,uoi_to,uoi_custom_name,uoi_to_place,user_name,uoi_date,uoi_project_name,uoi_provider_name) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE uoi_manual_ofg_id = '{$uoi_manual_ofg_id}',uoi_to = '{$uoi_to}',uoi_custom_name = '{$uoi_custom_name}'";
            $sql .= ",uoi_to_place = '{$uoi_to_place}',user_name = '{$user_name}',uoi_date = '{$uoi_date}',uoi_project_name = '{$uoi_project_name}',uoi_provider_name = '{$uoi_provider_name}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*非常规订单清单 unc_ofg_detail*/
        public static function updateunc_ofg_detail($info)
        {
            $uod_id = $info['uod_id'];
            $unc_ofg_info_id = $info['unc_ofg_info_id'];
            $product_info_id = $info['product_info_id'];
            $uod_count = $info['uod_count'];
            $uod_unit = $info['uod_unit'];
            $uod_requirement = $info['uod_requirement'];
            $uod_delivery_date = $info['uod_delivery_date'];
            $uod_comment = $info['uod_comment'];
            $unc_product_id = $info['unc_product_id'];
            $sql_value = "'{$uod_id}','{$unc_ofg_info_id}','{$product_info_id}','{$uod_count}','{$uod_unit}','{$uod_requirement}','{$uod_delivery_date}','{$uod_comment}','{$unc_product_id}'";
            $sql = "INSERT INTO dsp_logistic.unc_ofg_detail (uod_id,unc_ofg_info_id,product_info_id,uod_count,uod_unit,uod_requirement,uod_delivery_date,uod_comment,unc_product_id) VALUES ({$sql_value})";
            $sql .= " ON DUPLICATE KEY UPDATE unc_ofg_info_id = '{$unc_ofg_info_id}',product_info_id = '{$product_info_id}',uod_count = '{$uod_count}'";
            $sql .= ",uod_unit = '{$uod_unit}',uod_requirement = '{$uod_requirement}',uod_delivery_date = '{$uod_delivery_date}',uod_comment = '{$uod_comment}',unc_product_id = '{$unc_product_id}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }
        /*根据cs_id 获取走流程确认单的所有信息*/
        public static function getallcsinfobycsid($cs_id)
        {
            if($cs_id == null||$cs_id == "")
                return null;
            $sqlone ="select dsp_logistic.cs_belong.*,dsp_logistic.cs_info.*,dsp_logistic.delivery_info.*,";
            $sqlone .= "dsp_logistic.custom_info.*,dsp_logistic.return_info.*,dsp_logistic.payment_info.* from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.custom_info on dsp_logistic.custom_info.custom_info_id = dsp_logistic.cs_info.custom_info_id ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "left join dsp_logistic.payment_info on dsp_logistic.payment_info.payment_info_id = dsp_logistic.cs_info.payment_info_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_id='$cs_id' ";
            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;
            //获取审批表
            $allcsinfo = $tableobj[0];
            $cs_examine_info = Array();
            //$examine_ids = Array();
            $examine_ids = explode(',',$allcsinfo['cs_examine_ids'],-1);
            if(count($examine_ids)>0)
            {
                foreach ($examine_ids as $id )
                {
                    $exmine = self::getclassinfobyproperty('cs_examine','cs_examine_id',$id);
                    if(!empty($exmine))
                        $cs_examine_info[]=$exmine[0];
                }
            }

            $logistric_info = self::getclassinfobyproperty('logistics_info','cs_id',$cs_id);
            if(empty($logistric_info))
                $allcsinfo['logistic_info'] = null;
            else
                $allcsinfo['logistic_info'] = $logistric_info;
            $allcsinfo['cs_examine_info'] = $cs_examine_info;
            $allcsinfo['order_goods'] =self::getordergoodsbyid($cs_id);


            $allcsinfo['unc_ofg_info'] = "";
            $allcsinfo['unc_ofg_detail'] = "";
            $unc_ofg_info_id = $allcsinfo['unc_ofg_info_id'];
            if ($unc_ofg_info_id >= 1){
                $unc_ofg_info = self::getclassinfobyproperty('dsp_logistic.unc_ofg_info','uoi_id',$unc_ofg_info_id);
                if (!empty($unc_ofg_info)){
                    $allcsinfo['unc_ofg_info'] = $unc_ofg_info[0];
                    $unc_ofg_detail = self::getuncofgdetailbyid($unc_ofg_info_id);
                    if (!empty($unc_ofg_detail)){
                        $allcsinfo['unc_ofg_detail'] = $unc_ofg_detail;
                    }
                }
            }
             return $allcsinfo;
        }

        //根据cs_id获取确认单购买清单
        public static function getordergoodsbyid($cs_id)
        {
            $sqlone ="select dsp_logistic.order_goods_manager.*,dsp_logistic.order_goods_logistics.*,dsp_logistic.product_info.*,dsp_logistic.unc_product.unc_product_name,";
            $sqlone .= "dsp_logistic.product_brand.*,dsp_logistic.product_place.* ,dsp_logistic.product_type.* from dsp_logistic.order_goods_manager ";
            $sqlone .= "left join dsp_logistic.order_goods_logistics on dsp_logistic.order_goods_logistics.order_goods_manager_id = dsp_logistic.order_goods_manager.order_goods_manager_id ";
            $sqlone .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_manager.product_info_id ";
            $sqlone .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
            $sqlone .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
            $sqlone .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
            $sqlone .= "left join dsp_logistic.unc_product on dsp_logistic.unc_product.unc_product_id = dsp_logistic.order_goods_logistics.unc_product_id ";
            $sqlone .= "where dsp_logistic.order_goods_manager.cs_id = '$cs_id' ";
            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;
            return $tableobj;
        }

        //根据cs_id 获取订货确认单清单
        public static function getorderogcugibyid($cs_id)
        {
            $sqlone ="select dsp_logistic.order_goods_cs_undeliver_goods_info.*,dsp_logistic.product_info.*,";
            $sqlone .= "dsp_logistic.product_brand.*,dsp_logistic.product_place.* ,dsp_logistic.product_type.* from dsp_logistic.order_goods_cs_undeliver_goods_info ";
            //$sqlone .= "left join dsp_logistic.order_goods_logistics on dsp_logistic.order_goods_logistics.order_goods_manager_id = dsp_logistic.order_goods_cs_undeliver_goods_info.order_goods_manager_id ";
            $sqlone .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.order_goods_cs_undeliver_goods_info.product_info_id ";
            $sqlone .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
            $sqlone .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
            $sqlone .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";
            $sqlone .= "where dsp_logistic.order_goods_cs_undeliver_goods_info.cs_id = '$cs_id' ";
            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;
            return $tableobj;
        }

        //根据cs_id 获取非常规订单清单
        public static function getuncofgdetailbyid($unc_ofg_info_id)
        {
            $sqlone ="select dsp_logistic.unc_ofg_detail.*,dsp_logistic.product_info.*,dsp_logistic.unc_product.*,";
            $sqlone .= "dsp_logistic.product_brand.*,dsp_logistic.product_place.* ,dsp_logistic.product_type.* from dsp_logistic.unc_ofg_detail ";
            //$sqlone .= "left join dsp_logistic.order_goods_logistics on dsp_logistic.order_goods_logistics.order_goods_manager_id = dsp_logistic.order_goods_cs_undeliver_goods_info.order_goods_manager_id ";
            $sqlone .= "left join dsp_logistic.product_info on dsp_logistic.product_info.product_info_id = dsp_logistic.unc_ofg_detail.product_info_id ";

            $sqlone .= "left join dsp_logistic.unc_product on dsp_logistic.unc_product.unc_product_id = dsp_logistic.unc_ofg_detail.unc_product_id ";

            $sqlone .= "left join dsp_logistic.product_brand on dsp_logistic.product_brand.brand_id = dsp_logistic.product_info.brand_id ";
            $sqlone .= "left join dsp_logistic.product_place on dsp_logistic.product_place.place_id = dsp_logistic.product_info.place_id ";
            $sqlone .= "left join dsp_logistic.product_type on dsp_logistic.product_type.product_type_id = dsp_logistic.product_info.product_type_id ";

            $sqlone .= "where dsp_logistic.unc_ofg_detail.unc_ofg_info_id = '$unc_ofg_info_id' ";
            $tableobj = Db::query($sqlone);
            if(empty($tableobj))
                return null;
            return $tableobj;
        }

        //根据cs_id修改订单状态
        public static function cancelcsinfobyid($cs_id){
            $cs_info_state = 3;
            $sql = "update dsp_logistic.cs_info SET  cs_info_state = '{$cs_info_state}' where cs_id = '{$cs_id}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }

        public static function cancelordergoodscsinfobyid($cs_id){
            $cs_info_state = 3;
            $sql = "update dsp_logistic.order_goods_cs_info SET  cs_info_state = '{$cs_info_state}' where cs_id = '{$cs_id}'";
            $sqlret = Db::execute($sql);
            return $sqlret;
        }

        public  static  function getreceiverbycsid($cs_id){
            //经理部分的确认单
            $sqlone = "select dsp_logistic.delivery_info.*,dsp_logistic.return_info.*,dsp_logistic.cs_info.cs_info_type,dsp_logistic.logistics_info.*from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.delivery_info on dsp_logistic.delivery_info.delivery_info_id = dsp_logistic.cs_info.delivery_info_id ";
            $sqlone .= "left join dsp_logistic.return_info on dsp_logistic.return_info.return_info_id = dsp_logistic.cs_info.return_info_id ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.cs_info.cs_id = dsp_logistic.logistics_info.cs_id ";
            $sqlone.= "where dsp_logistic.cs_info.cs_id = '$cs_id'";
            $tableobj = Db::query($sqlone);
            if(!empty($tableobj))
            {
//                if($tableobj[0]['cs_info_type'] == 2|| $tableobj[0]['cs_info_type'] == 5)
//                {
                    $info = Array();
                    $info['receiver_name'] = self::parse($tableobj[0]['delivery_info_receiver_name']);
                    $info['receiver_phone'] = self::parse($tableobj[0]['delivery_info_receiver_phone']);
                    $info['receiver_address'] = self::parse($tableobj[0]['delivery_info_receiver_address']);
					$info['goods_yard_name'] = self::parse($tableobj[0]['goods_yard_name']);
					$info['cs_id'] = self::parse($tableobj[0]['cs_id']);
					$info['count'] = self::parse($tableobj[0]['count']);
					$info['transfer_order_num'] = self::parse($tableobj[0]['transfer_order_num']);
					$info['delivery_date'] = self::parse($tableobj[0]['delivery_date']);
                    return $info;
//                }
//                else
//                {
//                    $info = Array();
//                    $info['receiver_name'] = $tableobj[0]['return_info_receiver_name'];
//                    $info['receiver_phone'] = $tableobj[0]['return_info_receiver_phone'];
//                    $info['receiver_address'] = $tableobj[0]['return_info_receiver_address'];
//					$info['goods_yard_name'] = $tableobj[0]['goods_yard_name'];
//					$info['cs_id'] = $tableobj[0]['cs_id'];
//					$info['count'] = $tableobj[0]['count'];
//					$info['transfer_order_num'] = $tableobj[0]['transfer_order_num'];
//					$info['delivery_date'] = $tableobj[0]['delivery_date'];
//                    return $info;
//                }
            }
            //物流单
            $sqlone = "select dsp_logistic.ofg_info.*,dsp_logistic.logistics_info.* from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.ofg_info on dsp_logistic.ofg_info.ofg_info_id = dsp_logistic.order_goods_cs_info.ofg_info_id ";
            $sqlone .= "left join dsp_logistic.logistics_info on dsp_logistic.logistics_info.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqlone.= "where dsp_logistic.order_goods_cs_info.cs_id = '$cs_id'";
            $tableobj = Db::query($sqlone);
            if(!empty($tableobj))
            {
                $info = Array();

                $info['receiver_name'] =self:: parse($tableobj[0]['receiver_name']);


                $info['receiver_phone'] =self::parse( $tableobj[0]['receiver_phone']);

                $info['receiver_address'] = self::parse($tableobj[0]['receiver_address']);
				$info['goods_yard_name'] = self::parse($tableobj[0]['goods_yard_name']);
				$info['cs_id'] = self::parse($tableobj[0]['cs_id']);
				$info['count'] = self::parse($tableobj[0]['count']);
				$info['transfer_order_num'] = self::parse($tableobj[0]['transfer_order_num']);
				$info['delivery_date'] = self::parse($tableobj[0]['delivery_date']);
                return $info;
            }
            return null;
        }     

        public  static  function parse($prarameter)
        {
            if($prarameter == null || $prarameter == "")
                return "";
            else
                return $prarameter;
        }
		
        /*cs_product*/
        public static function getcsproduct(){
            $sql = "select * from dsp_logistic.cs_product";
            $sql.= ' order by number';
            $tableobj = Db::query($sql);
            if(!empty($tableobj)){
                return $tableobj;
            }
        }

        /*模糊搜索用户  */
        public  static function serachuser($param)
        {
            if(empty($param))
                return null;
            $count = count($param);
            if($count == 0)
                return null;

            $sql = "SELECT dsp_logistic.user.fullname , dsp_logistic.user.user_id,dsp_logistic.user.phone ,dsp_logistic.organize.organize_name,dsp_logistic.role.role_name FROM dsp_logistic.user ";
            $sql .= "left join dsp_logistic.role on dsp_logistic.role.role_id = dsp_logistic.user.role_id ";
            $sql .= "left join dsp_logistic.organize on dsp_logistic.organize.organize_id = dsp_logistic.user.organize_id ";
            if(array_key_exists('serachText',$param))
            {
                $serachText = $param['serachText'];
                $serachText = addslashes($serachText);
                $sql .= "WHERE dsp_logistic.user.fullname LIKE '%{$serachText}%' ";
            }

            if(array_key_exists('organize_id',$param))
            {
                $departId = $param['organize_id'];
                $sql.= "and dsp_logistic.organize.organize_id = '$departId' ";
            }
            if(array_key_exists('role_name',$param))
            {
                $role_name = $param['role_name'];
                $sql.= "and dsp_logistic.role.role_name = '$role_name' ";
            }
            if(array_key_exists('organize_name',$param))
            {
                $organize_name = $param['organize_name'];
                $sql.= "and dsp_logistic.organize.organize_name = '$organize_name' ";
            }
            $sql.= ' limit 0 , 5;';
            $retsql = Db::query($sql);
            return $retsql;
        }

        public static function serachuserlike($param){
            if(empty($param))
                return null;
            $count = count($param);
            if($count == 0)
                return null;

            $sql = "SELECT dsp_logistic.user.* FROM dsp_logistic.user ";
            if(array_key_exists('serachText',$param))
            {
                $serachText = $param['serachText'];
                $sql .= "WHERE dsp_logistic.user.fullname LIKE '%{$serachText}%' or dsp_logistic.user.username LIKE '%{$serachText}%' ";
            }
            $sql.= ' limit 0 , 5;';
            $retsql = Db::query($sql);
            return $retsql;
        }

        public static function updatepersonalinfo($param){
            $username= $param["username"];
            $password = $param["oldpassword"];
            $newpassword = $param["newpassword"];

            $sqlone = "SELECT *  FROM dsp_logistic.user  where username = '$username' and password = '$password'";
            $retsql = Db::query($sqlone);
            if(empty($retsql))
                return "";
            $user_id = intval($retsql[0]['user_id']);
            $sqltwo = " UPDATE dsp_logistic.user SET password = '{$newpassword}' where user_id = '{$user_id}'";
            $result = Db::execute($sqltwo);
            return $result;
        }

        public  static  function serachusername($name)
        {
            $sql = "SELECT dsp_logistic.user.* FROM dsp_logistic.user WHERE username = '$name' ";
            $retsql = Db::query($sql);
            if(empty($retsql))
                return "";
            return $retsql;
        }
        /*模糊搜索部门  */
        public  static function serachdepartment($param)
        {
            if(empty($param))
                return null;
            $count = count($param);
            if($count == 0)
                return null;
            $sql = "SELECT * FROM dsp_logistic.organize ";
            if(array_key_exists('name',$param))
            {
                $name = $param['name'];
                $sql .= "WHERE organize_name LIKE '%{$name}%' ";
            }
            $sql.= ' limit 0 , 5;';
            $retsql = Db::query($sql);
            return $retsql;
        }
        /*判断经理存不存在*/
        public static function judgemanagerexist($name,$depart_id)
        {
            $sql = "SELECT *  FROM dsp_logistic.user  where fullname = '$name' and organize_id = '$depart_id'";
            $retsql = Db::query($sql);
            if(!empty($retsql))
                return $retsql;
            return '';
        }

        /*检测产品型号是否存在*/
        public static function detectmodelisexist($model){
            $sql = "SELECT *  FROM dsp_logistic.product_info  where model = '$model' ";
            $ret = Db::query($sql);
            if(!empty($ret))
                return $ret;
            return '';
        }

        /*product_type 产品类别*/
        public static function producttype($product_type_name){
            switch($product_type_name){
                case '公共广播':
                return 1;

                case '会议系统':
                return 2;

                case '地铁事业':
                return 3;

                case '智能音响':
                return 4;

                case '录播系统':
                return 5;

                case '应急广播':
                return 6;

                case '音视频一线通':
                return 7;

                case '视频会议':
                return 8;

                case '视频监控':
                return 9;

                case '专业音响':
                return 10;

                case '数字班牌':
                return 11;

                default:
                return 12;
            }
        }

        /*product_brand*/
        public static function productbrand($brand_name){
            switch ($brand_name){
                case 'DSPPA':
                    return 1;
                case 'ZABKZ':
                    return 2;
                case 'OTEWA':
                    return 3;
                case 'AUXDIO':
                    return 4;
                case '推荐产品':
                    return 5;
                default:
                    return 6;
            }
        }

        /*product_place*/
        public static function productplace($place_name){
            switch ($place_name){
                case '总厂':
                    return 1;
                case '盛葆':
                    return 2;
                case '乐坊':
                    return 3;
                case '总厂外购':
                    return 4;
                case '澳斯迪':
                    return 5;
                case '智慧科技':
                    return 6;
                case '信息科技':
                    return 7;
                case '盛葆外购':
                    return 8;
                case '智慧平台':
                    return 9;
                case '外购':
                    return 10;
                default:
                    return 11;
            }
        }

        /*insertproductinfo*/
        public static function insertproductinfo($mysql){
            $sql = "INSERT INTO dsp_logistic.product_info (model,product_info_name,product_type_id,brand_id,place_id) VALUES ".$mysql;
            $sqlret = Db::execute($sql);
            return $sqlret;
        }

        /*Parsefreightmode*/
        public static function parsefreightmode($freightmode)
        {
            if ($freightmode == 0||$freightmode == '0')
                return "到付";
            elseif ($freightmode == 1||$freightmode == '1')
                return "现金";
            elseif ($freightmode == 2||$freightmode == '2')
                return "代付";
            elseif ($freightmode == 3||$freightmode == '3')
                return "公司付";
            elseif ($freightmode == 4||$freightmode == '4')
                return "送货";
            else
                return "";
        }

        public  static function parseorderstate($state)
        {
            if($state == 0 || $state == -1)
                return "";
            elseif ($state == 1)
            {
                return "处理中";
            }
            elseif ($state == 2)
            {
                return "已完成";
            }
            elseif ($state == 3)
            {
                return "取消";
            }
            elseif ($state == 4)
            {
                return "备货";
            }
            elseif ($state == 5)
            {
                return "退回";
            }
            elseif ($state == 6)
            {
                return "缺货";
            }
        }
        //转手订单页面用
        public static function querysalesedconfirmorder(...$args){
            $totalargs = count($args);
            $type = $args[0];
            $pagenum = intval($args[1]?$args[1]:1);
            $length = intval($args[2]);

            $sqlone = "select count(*) from dsp_logistic.cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.cs_info.cs_info_type='$type' ";

            if($totalargs == 4){
                if($args[3]['company_name'] != "" ){
                    $company = $args[3]['company_name'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_organize_name ='$company'";
                }
                if($args[3]['people_name'] != "" ){
                    $areamanger1 = $args[3]['people_name'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1'";
                }
                if($args[3]['department_name'] ){
                    $departmentname1 = $args[3]['department_name'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_department_name ='$departmentname1' ";
                }
                if(array_key_exists('order_state',$args[3]))
                {
                    if($args[3]['order_state'] != "")
                    {
                        $cs_info_state = $args[3]['order_state'];
                        $sqlone.= " and dsp_logistic.cs_info.cs_info_state ='$cs_info_state' ";
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
            $sqltwo ="select  dsp_logistic.cs_belong.*, dsp_logistic.cs_info.* from dsp_logistic.cs_info ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.cs_info.cs_id ";
            $sqltwo .= "where dsp_logistic.cs_info.cs_info_type='$type' ";
            //不能查看未提交的单
            $sqltwo .= "and dsp_logistic.cs_info.cs_info_state != 0 ";
            if($totalargs == 4){
                if($args[3]['company_name'] != "" ){
                    $company = $args[3]['company_name'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_organize_name ='$company'";
                }
                if($args[3]['people_name'] != "" ){
                    $areamanger1 = $args[3]['people_name'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1'";
                }
                if($args[3]['department_name'] ){
                    $departmentname1 = $args[3]['department_name'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_department_name ='$departmentname1' ";
                }
                if(array_key_exists('order_state',$args[3]))
                {
                    if($args[3]['order_state'] != "")
                    {
                        $cs_info_state = $args[3]['order_state'];
                        $sqltwo.= " and dsp_logistic.cs_info.cs_info_state ='$cs_info_state' ";
                    }
                }
            }
            $sqltwo .= "order By dsp_logistic.cs_info.write_date DESC limit {$offset},{$length}";

            $tableobj = Db::query($sqltwo);
            if(!empty($tableobj)){
                for ($i = 0;$i < count($tableobj);$i++)
                {
                    if($type == 1)
                        $tableobj[$i]["order_type"] =  '更换确认单';
                    elseif($type == 1)
                    {
                        $tableobj[$i]["order_type"] =  '更换确认单';
                    }
                    elseif($type == 2)
                    {
                        $tableobj[$i]["order_type"] =  '借样确认单';
                    }
                    elseif($type == 3)
                    {
                        $tableobj[$i]["order_type"] =  '退回确认单';
                    }
                    elseif($type == 4)
                    {
                        $tableobj[$i]["order_type"] =  '维修确认单';
                    }
                    elseif($type == 5)
                    {
                        $tableobj[$i]["order_type"] =  '配件确认单';
                    }
                    elseif($type == 6)
                    {
                        $tableobj[$i]["order_type"] =  '代用确认单';
                    }

                    $state = $tableobj[$i]["cs_info_state"];
                    if($state == 0||$state == -1)
                        $tableobj[$i]["cs_info_state"] = "";
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
                    elseif ($state == 5)
                    {
                        $tableobj[$i]["cs_info_state"] = "退回";
                    }
                    elseif ($state == 6)
                    {
                        $tableobj[$i]["cs_info_state"] = "缺货";
                    }
                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
        }
        //转手订单页面用
        public static function querygoodsorder(...$args){
            $totalargs = count($args);
            $pagenum = intval($args[0]?$args[0]:1);
            $length = intval($args[1]);

            $sqlone = "select count(*) from dsp_logistic.order_goods_cs_info ";
            $sqlone .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqlone .= "where dsp_logistic.order_goods_cs_info.cs_id like '%%' ";

            if($totalargs == 3){
                if($args[2]['company_name'] != "" ){
                    $company = $args[2]['company_name'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_organize_name ='$company'";
                }
                if($args[2]['people_name'] != "" ){
                    $areamanger1 = $args[2]['people_name'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1'";
                }
                if($args[2]['department_name'] ){
                    $departmentname1 = $args[2]['department_name'];
                    $sqlone.= " and dsp_logistic.cs_belong.build_department_name ='$departmentname1' ";
                }
                if(array_key_exists('order_state',$args[2]))
                {
                    if($args[2]['order_state'] != "")
                    {
                        $cs_info_state = $args[2]['order_state'];
                        $sqlone.= " and dsp_logistic.cs_info.cs_info_state ='$cs_info_state' ";
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
            $sqltwo ="select  dsp_logistic.cs_belong.*,dsp_logistic.order_goods_cs_info.* from dsp_logistic.order_goods_cs_info ";
            $sqltwo .= "left join dsp_logistic.cs_belong on dsp_logistic.cs_belong.cs_id = dsp_logistic.order_goods_cs_info.cs_id ";
            $sqltwo .= "where dsp_logistic.order_goods_cs_info.cs_id like '%%' ";
            if($totalargs == 3){
                if($args[2]['company_name'] != "" ){
                    $company = $args[2]['company_name'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_organize_name ='$company'";
                }
                if($args[2]['people_name'] != "" ){
                    $areamanger1 = $args[2]['people_name'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_user_name ='$areamanger1'";
                }
                if($args[2]['department_name'] ){
                    $departmentname1 = $args[2]['department_name'];
                    $sqltwo.= " and dsp_logistic.cs_belong.build_department_name ='$departmentname1' ";
                }
                if(array_key_exists('order_state',$args[2]))
                {
                    if($args[2]['order_state'] != "")
                    {
                        $cs_info_state = $args[2]['order_state'];
                        $sqltwo.= " and dsp_logistic.order_goods_cs_info.cs_info_state ='$cs_info_state' ";
                    }
                }
            }
            $sqltwo .= "order By dsp_logistic.order_goods_cs_info.order_date DESC limit {$offset},{$length}";

            $tableobj = Db::query($sqltwo);
            if(!empty($tableobj)){
                for ($i = 0;$i < count($tableobj);$i++)
                {
                    $tableobj[$i]["cs_info_type"] = 0;
                    $tableobj[$i]["write_date"] =  $tableobj[$i]["order_date"];
                    $tableobj[$i]["order_type"] =  '订货单';
                    $state = $tableobj[$i]["cs_info_state"];
                    if($state == 0||$state == -1)
                        $tableobj[$i]["cs_info_state"] = "";
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
                    elseif ($state == 5)
                    {
                        $tableobj[$i]["cs_info_state"] = "退回";
                    }
                    elseif ($state == 6)
                    {
                        $tableobj[$i]["cs_info_state"] = "缺货";
                    }
                }
                return (array('code'=>0,'msg'=>'','count'=>$count,'data'=>$tableobj));
            }
        }

	}
?>
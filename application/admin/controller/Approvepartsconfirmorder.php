<?php
namespace app\admin\controller;
use think\Controller;

class Approvepartsconfirmorder extends Controller
{
	/*新增订单渲染方法*/
    public function approvepartsconfirmorder(){
        $queryuserinfo = session("user_querypower");
        $rolename = $queryuserinfo['role_name'];
        if( $rolename == "管理人员" || $rolename == "部长/主管"||$rolename == "物流部人员")
        {
            $this->assign('current_user_type',5);
        }
        elseif ($rolename == "财务人员")
        {
            $this->assign('current_user_type',4);
        }

        elseif($rolename == "总经理")
        {
            $this->assign('current_user_type',3);
        }
        elseif($rolename == "部门总监")
        {
            $this->assign('current_user_type',2);
        }
        elseif($rolename == "部门助理")
        {
            $this->assign('current_user_type',6);
        }
        return $this->fetch();
    }

    public function getexamineorder(){
        $page = $_GET['page'];
        $limit = $_GET['limit'];
        $user = session("user_session");
        $user_id = $user["user_id"];
        $orderType = 5;
        $queryuserinfo = session("user_querypower");
        $rolename = $queryuserinfo['role_name'];
        if( $rolename == "管理人员" || $rolename == "部长/主管"||$rolename == "物流部人员")
        {
            if(isset($_GET['queryInfo'])){
                $queryInfo = $_GET['queryInfo'];
                $tablelist = \app\index\model\Admin::logisticQueryApproveConfirmOrder($orderType,$page,$limit,$queryInfo);
            }else{
                $tablelist = \app\index\model\Admin::logisticQueryApproveConfirmOrder($orderType,$page,$limit);
            }
        }
        else
        {
            if(isset($_GET['queryInfo'])){
                $queryInfo = $_GET['queryInfo'];
                $tablelist = \app\index\model\Admin::queryApproveConfirmOrder($user_id,$orderType,$page,$limit,$queryInfo);
            }else{
                $tablelist = \app\index\model\Admin::queryApproveConfirmOrder($user_id,$orderType,$page,$limit);
            }
        }
        
    	return $tablelist;
    }

    public function getareamanager(){
        $departmentId = $_POST;
        $organizeID = \app\index\model\Admin::getuserinfobydepid($departmentId["param"]);
        return $organizeID;
    }
}
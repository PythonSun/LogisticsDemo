<?php
namespace app\index\controller;
use think\Controller;

class Adminindex extends Controller
{	
	/*首页渲染方法*/
    public function index(){
        $userinfo = \app\index\model\Admin::getsessioninfo();
        $user_id = intval($userinfo['user_id']);
        
        $this->assign('loginusername',$userinfo["fullname"]);
        
        /*更换确认单*/
        $type = 0x01;
        $replaceorder = \app\index\model\Admin::querycsinfonums($user_id,$type);
    	$this->assign('replaceorder',$replaceorder);

    	/*借样确认单*/
        $type = 0x02;
        $borroworder = \app\index\model\Admin::querycsinfonums($user_id,$type);
    	$this->assign('borroworder',$borroworder);

    	/*退货确认单*/
        $type = 0x03;
        $returnorder = \app\index\model\Admin::querycsinfonums($user_id,$type);
    	$this->assign('returnorder',$returnorder);

    	/*维修确认单*/
        $type = 0x04;
        $repairorder = \app\index\model\Admin::querycsinfonums($user_id,$type);
    	$this->assign('repairorder',$repairorder);

    	/*配件确认单*/
        $type = 0x05;
        $partsorder = \app\index\model\Admin::querycsinfonums($user_id,$type);
    	$this->assign('partsorder',$partsorder);

    	/*代用确认单*/
        $type = 0x06;
        $alternativeorder = \app\index\model\Admin::querycsinfonums($user_id,$type);
    	$this->assign('alternativeorder',$alternativeorder);

        $userinfo = \app\index\model\Admin::getsessioninfo();
        $role_id = intval($userinfo["role_id"]);
        $role_info = \app\index\model\Admin::queryroleinfo($role_id);

        /*用户管理权限*/
        $usermanagepower = $role_info[0]['user_manage_permission'];
        $this->assign('usermanagepower',$usermanagepower);

        /*新增订货确认单权限*/
        $addgoodsorderpower = ($role_info[0]['order_goods_permission'])&0x01;
        $this->assign('addgoodsorderpower',$addgoodsorderpower);

        /*新增订单权限*/
        if(((($role_info[0]['replace_permission'])&0x01) == 0x01)||((($role_info[0]['borrow_sample_permission'])&0x01) == 0x01)||((($role_info[0]['return_goods_permission'])&0x01) == 0x01)||((($role_info[0]['fixing_permission'])&0x01) == 0x01)||((($role_info[0]['maintain_permission'])&0x01) == 0x01)||((($role_info[0]['substitute_permission'])&0x01) == 0x01)){
            $addorderpower = 0x01;
            $this->assign('addorderpower',$addorderpower);
        }else{
            $addorderpower = 0x00;
            $this->assign('addorderpower',$addorderpower);
        }
        
        //$addreplaceorderpower = 0x01;
        $addreplaceorderpower = ($role_info[0]['replace_permission'])&0x01;
        $this->assign('addreplaceorderpower',$addreplaceorderpower);

        //$addborroworderpower = 0x02;
        $addborroworderpower = ($role_info[0]['borrow_sample_permission'])&0x01;
        $this->assign('addborroworderpower',$addborroworderpower);

        //$addreturnorderpower = 0x03;
        $addreturnorderpower = ($role_info[0]['return_goods_permission'])&0x01;
        $this->assign('addreturnorderpower',$addreturnorderpower);

        //$addrepairorderpower = 0x04;
        $addrepairorderpower = ($role_info[0]['fixing_permission'])&0x01;
        $this->assign('addrepairorderpower',$addrepairorderpower);

        //$addpartsorderpower = 0x05;
        $addpartsorderpower = ($role_info[0]['maintain_permission'])&0x01;
        $this->assign('addpartsorderpower',$addpartsorderpower);

        //$addalternativeorderpower = 0x06;
        $addalternativeorderpower = ($role_info[0]['substitute_permission'])&0x01;
        $this->assign('addalternativeorderpower',$addalternativeorderpower);

        /*物流单号输入权限*/
        $addlogisticorder = $role_info[0]['logistic_input_permission'];
        $this->assign('addlogisticorder',$addlogisticorder);

        /*订单审批权限权限*/
        if(((($role_info[0]['replace_permission'])&0x20) == 0x20)||((($role_info[0]['borrow_sample_permission'])&0x20) == 0x20)||((($role_info[0]['return_goods_permission'])&0x20) == 0x20)||((($role_info[0]['fixing_permission'])&0x20) == 0x20)||((($role_info[0]['maintain_permission'])&0x20) == 0x20)||((($role_info[0]['substitute_permission'])&0x20) == 0x20)){
            $approvepower = 0x20;
            $this->assign('approvepower',$approvepower);
        }else{
            $approvepower = 0x00;
            $this->assign('approvepower',$approvepower);
        }
        
        $approvereplacepower = ($role_info[0]['replace_permission'])&0x20;
        $this->assign('approvereplacepower',$approvereplacepower);

        $approveborrowpower = ($role_info[0]['borrow_sample_permission'])&0x20;
        $this->assign('approveborrowpower',$approveborrowpower);

        $approvereturnpower = ($role_info[0]['return_goods_permission'])&0x20;
        $this->assign('approvereturnpower',$approvereturnpower);

        $approverepairpower = ($role_info[0]['fixing_permission'])&0x20;
        $this->assign('approverepairpower',$approverepairpower);

        $approvepartspower = ($role_info[0]['maintain_permission'])&0x20;
        $this->assign('approvepartspower',$approvepartspower);

        $approvealternativepower = ($role_info[0]['substitute_permission'])&0x20;
        $this->assign('approvealternativepower',$approvealternativepower);

        /*确认单查询权限*/
        if(((($role_info[0]['order_goods_permission'])&0x40) == 0x40)||((($role_info[0]['replace_permission'])&0x40) == 0x40)||((($role_info[0]['borrow_sample_permission'])&0x40) == 0x40)||((($role_info[0]['return_goods_permission'])&0x40) == 0x40)||((($role_info[0]['fixing_permission'])&0x40) == 0x40)||((($role_info[0]['maintain_permission'])&0x40) == 0x40)||((($role_info[0]['substitute_permission'])&0x40) == 0x40)){
            $querypower = 0x40;
            $this->assign('querypower',$querypower);
        }else{
            $querypower = 0x00;
            $this->assign('querypower',$querypower);
        }

        $querygoodspower = ($role_info[0]['order_goods_permission'])&0x40;
        $this->assign('querygoodspower',$querygoodspower);

        $queryreplacepower = ($role_info[0]['replace_permission'])&0x40;
        $this->assign('queryreplacepower',$queryreplacepower);

        $queryborrowpower = ($role_info[0]['borrow_sample_permission'])&0x40;
        $this->assign('queryborrowpower',$queryborrowpower);

        $queryreturnpower = ($role_info[0]['return_goods_permission'])&0x40;
        $this->assign('queryreturnpower',$queryreturnpower);

        $queryrepairpower = ($role_info[0]['fixing_permission'])&0x40;
        $this->assign('queryrepairpower',$queryrepairpower);

        $querypartspower = ($role_info[0]['maintain_permission'])&0x40;
        $this->assign('querypartspower',$querypartspower);

        $queryalternativepower = ($role_info[0]['substitute_permission'])&0x40;
        $this->assign('queryalternativepower',$queryalternativepower);
	   	return $this->fetch();
    }

    /*退出登录*/
    public function logout(){
        $ret = \app\index\model\Admin::logout();
        return $ret;
    }
}



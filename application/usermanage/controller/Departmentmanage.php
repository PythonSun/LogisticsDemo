<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 17:18
 */

namespace app\usermanage\controller;

use think\Controller;

class Departmentmanage extends Controller
{
    public function departmentmanage()
    {
        return $this->fetch();
    }

    public function getorganizeinfo(){
    	$organizelist = \app\index\model\Admin::querydepartmentinfo();
    	$data = array();
    	for ($i=0; $i <count($organizelist) ; $i++) {
    		$data[$i]["name"] = $organizelist[$i]['organize_name'];
    		$data[$i]["id"] = $organizelist[$i]['organize_id'];
    		$data[$i]["pId"] = $organizelist[$i]['parent_id'];
            $data[$i]["department_number"] = $organizelist[$i]['department_number'];
    	}
    	return json_encode($data);
    }

    public function getmaxorganizeID(){
    	$organizeID = \app\index\model\Admin::getmaxorganizeID();
    	return $organizeID;
    }
}
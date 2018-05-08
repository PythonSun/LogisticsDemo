<?php
namespace app\admin\controller;
use think\Controller;

class Setexportfileparam extends Controller
{
	/*设置导出文件参数*/
    public function setexportfileparam(){
    	return $this->fetch();
    }
}
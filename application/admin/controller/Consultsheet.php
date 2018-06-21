<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/06/20
 * Time: 13:51
 */
namespace app\admin\controller;
use think\Controller;

class Consultsheet extends Controller
{
    public function consultsheet(){
        $canDelPic = $_GET['canDelPic'];
        $this->assign('canDelPic',$canDelPic);

        $picNames = $_GET['picNames'];
        $strJson = str_replace('/$/@','"',$picNames);
        $picNames = json_decode($strJson);
        $this->assign('picNames',json_encode($picNames));

        //delPic
        $delPic = $_GET['delPic'];
        $strDelPicJson = str_replace('/$/@','"',$delPic);
        $delPic = json_decode($strDelPicJson);
        $this->assign('delPic',json_encode($delPic));
        return $this->fetch();
    }

    public function delcachefile(){
        $delFileName = '';
        if (array_key_exists('delFileName',$_POST)){
            $delFileName = $_POST['delFileName'];
        }else{
        }
        $delFileNameMsg = '';
        try
        {
            if (!empty($delFileName)){
                $filePath = ROOT_PATH . 'public' . DS . 'cachefile'.DS.$delFileName;
                if (file_exists($filePath)){
                    if (unlink($filePath)){
                        $delFileNameMsg = '文件删除成功！';
                    }else{
                        $delFileNameMsg = '文件删除失败！';
                    }
                }
            }
            $res=[
                'code'=>'1',
                'msg'=>$delFileNameMsg,
            ];
            return json($res);
        }catch (Exception $e){
            $res=[
                'code'=>'0',
                'msg'=>$e->getMessage()
            ];
            return json($res);
        }
    }
}
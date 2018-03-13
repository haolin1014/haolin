<?php
namespace app\admin\controller;
use think\Controller;
class Common extends Controller{
	public function __construct(){
		if(!session('uid') || !session('username') ){
			$this->redirect('Admin/Login/index');
		}
	}
	// 上传图片
	public function upload($filename,$path='/uploads'){
		// 上传图片
		$file = request()->file($filename);
		// 移动到框架应用根目录/public/static/ 目录下
		if($file){
		    $info = $file->move($path);
		    if($info){
		        return str_replace(ROOT_PATH . 'public' . DS .'static'. DS, '', $path). DS .$info->getSaveName();
		    }else{
		        // 上传失败获取错误信息
		        $this->error($file->getError());
		    }
		}
	}
}


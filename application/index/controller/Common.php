<?php
namespace app\index\controller;
use think\Db;
use think\Controller;
use app\index\service\Wx;

class Common extends Controller
{
	public function __construct(){
		session(null);
		if(Config('WX_CHECK')){
			Wx::userCheck();
		}else{
			session('uid',1);
		}
	}
}
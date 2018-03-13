<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\controller\Common;

/**
 * 管理员控制器
 */
class Manage extends Common
{
	public function index(){
		$admin = Db::table('admin')->order('id')->select();
		foreach ($admin as $key => $value) {
		    if($value['auth']==0){//如果是超级管理员
		         $where = null;
		    }else{
		         $where['id'] = array('in',$value['auth']);
		    }
		    $res = Db::table('shop')->field('name')->where($where)->select();
		    $admin[$key]['auth'] = $res;
		}
		$shop = Db::table('shop')->select();
		return view('index',['admin'=>$admin,'shop'=>$shop]);
	}
	
	// 添加管理员
	public function addAdmin(){
		$data = [
			'name'=>input('name'),
			'account'=>input('account'),
			'password'=>md5(input('password')),
			'auth'=>trim(input('auth'),','),
			'logintime'=>time()
		];
		Db::table('admin')->insert($data);
	    echo json_encode(array('state'=>1));
	}
	// 删除管理员
	public function delAdmin(){
	    $res = Db::table('admin')->delete(input('id'));
	    if($res){
	        echo json_encode(array('state'=>1));
	    }  
	}

} 
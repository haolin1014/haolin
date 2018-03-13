<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;

class Login extends Controller
{
	public function index(){
		if(!request()->isPost()) return view();
		$captcha = input('verify');
		if(!captcha_check($captcha)){
		 $this->error('验证码不正确');
		};
		$username = input('name');
		$password = md5(input('pwd'));
		$user = Db::table('admin')->where('account',$username)->find();
		if($user && $user['password']==$password){
			$this->makeAuth($user);//写入权限
			$this->redirect('admin/index/index');
		}else{
			$this->error('账号或密码错误');
		}
	}

	// 退出登录
	public function logout(){
		session(null);
        $this->redirect('Admin/Login/index');
	}

	// 写入权限
	private function makeAuth($user){
		//写入session
		session('uid',$user['id']);
		session('username',$user['name']);
		// 写入权限标识
		session('auth',$user['auth']);
		// 默认店铺
		if($user['auth']==0){//如果是超级管理员，则默认为南湖店
			session('shop_id',1);
			$shops = Db::table('shop')->field('id,name')->select();
		}else{
			$shop_id = substr($user['auth'], 0,1);
			session('shop_id',$shop_id);
			$shops = Db::table('shop')->field('id,name')->where('id','in',$user['auth'])->select();
		}
		session('shops',$shops);
	}

} 
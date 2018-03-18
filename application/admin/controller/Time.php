<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\controller\Common;
use app\admin\model\OrderTime;

/**
 * 预约时间控制器
 */
class Time extends Common
{
	public function index()
	{
		$shop_id = input('shop_id',session('shop_id'));//获取店铺id，默认为1
		// 获取预约时间段
		$result = OrderTime::getAllTime($shop_id);

		return view('index',$result);
	}
	
	// ajax修改时间状态
	public function updateOrderTimeState(){
		foreach (input('post.') as $k => $v) {
			$data = [
				'id' => $k,
				'state'=>$v
				];
			OrderTime::update($data);
		}
		echo json_encode(array('state'=>1));
	}
} 
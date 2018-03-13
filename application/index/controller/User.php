<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Common;

class User extends Common
{
	public function order(){
		$uid = session('uid');
		$order = Db::table('order')->join('shop','order.shop_id=shop.id')->where(array('uid'=>$uid))->select();
		foreach ($order as $k => $v) {
		  $order[$k]['order_time'] = explode(',',$v['order_time']);
		  $y = substr($v['order_day'],0,4);
		  $m = substr($v['order_day'],4,2);
		  $d = substr($v['order_day'],6,2);
		  $order[$k]['order_day'] = $y.'-'.$m.'-'.$d;
		  $order[$k]['order_num'] = count($order[$k]['order_time']);//预约时间点的个数
		}

		return view('order',['order'=>$order]);
	}
}
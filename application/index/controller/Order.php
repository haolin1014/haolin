<?php
namespace app\index\controller;
use think\Db;
use think\Controller;
use think\Cache;
use app\index\controller\Common;

class Order extends Common
{
	// 订单提交页面
	public function index(){
		$uid = session('uid');
		$carts = Db::table('cart')->where(array('uid'=>$uid))->field('time_id')->select();
		$ids = '';
		foreach ($carts as $v) {
		  $ids .= $v['time_id'].',';
		}
		$ids = trim($ids,',');
		$where['id'] = array('in',$ids);
		$order_time = Db::table('order_time')->where($where)->select();
		foreach ($order_time as $k => $v) {
		  $y = substr($v['day'],0,4);
		  $m = substr($v['day'],4,2);
		  $d = substr($v['day'],6,2);
		  $order_time[$k]['day'] = $y.'-'.$m.'-'.$d;
		}
		return view('index',['order_time'=>$order_time]);
	}

	// 提交订单
	public function buy(){
		$uid = session('uid');
		$shopid = session('shopid');
		//获取选择时间段的所有id
		$time_ids = Db::table('cart')->where(array('uid'=>$uid,'shop_id'=>$shopid))->select();
		// 检查是否有选择的时间段，如果没有，跳回选择时间页面
		if(!$time_ids){
			header("Content-type:text/html;charset=utf-8");
			$this->redirect('index/Index/chooseTime', '', 3, '<p style="font-size:50px;text-align:center;margin:30px auto;">请选择正确的时间段</p>');
		}

		$ids = '';
		foreach ($time_ids as $k => $v) {
			$ids .= $v['time_id'].',';
		}
		$ids = trim($ids,',');
		// 清除该用户所有购物车中的id
		Db::table('cart')->where(array('uid'=>$uid))->delete();
		// 将选择的时间段设定为已预订
		$where['id'] = array('in',$ids);
		Db::table('order_time')->where($where)->update(['state'=>1]);
		// 获取所有时间字段拼接成字符串，组成入库数据
		$order_times = Db::table('order_time')->where($where)->select();
		$order_time_data = '';
		$order_day = '';
		foreach ($order_times as $k => $v) {
			$order_time_data .= $v['time'].',';
			$order_day = $v['day'];
		}
		$order_time_data = trim($order_time_data,',');
		// 生成订单
		$data = array(
			'uid'=>session('uid'),
			'username'=>input('username'),
			'sex'=>input('sex'),
			'phone'=>input('phone'),
			'shop_id'=>session('shopid'),
			'add_time'=>time(),
			'order_time'=>$order_time_data,
			'order_day'=>$order_day,
			);
		$order_id = Db::table('order')->insert($data);
		if($order_id){
			return view('buy',['order_id'=>$order_id]);
		}else{
			$this->error('下单失败，请重新下单！');
		}

	}
}
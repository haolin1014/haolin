<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\controller\Common;

/**
 * 预约时间控制器
 */
class Time extends Common
{
	public function index()
	{
		$shop_id = input('shop_id',session('shop_id'));//获取店铺id，默认为1
		$result['shop_id'] = $shop_id;
		// 获取时间分组值并排序
		$groups = Db::table('order_time')->field('day') ->group('day')->where(array('shop_id'=>$shop_id))->select();
		$dayGroup = array();
		foreach ($groups as $v) {
		    array_push($dayGroup, $v['day']);
		}
		sort($dayGroup);
		// 先查是否有今天的记录，如果没有则清掉时间状态，并将时间设为今天。
		$todayTime = date("Ymd");
		$today = Db::table('order_time')->where(array('day'=>$todayTime,'shop_id'=>$shop_id))->select();
		if($today){
		    $result['today'] = $today;
		}else{
		    $data = array(
		            'day'=>$todayTime,
		            'state'=>0
		        );
		    Db::table('order_time')->where(array('day'=>$dayGroup[2],'shop_id'=>$shop_id))->update($data);
		    $result['today'] = Db::table('order_time')->where(array('day'=>$todayTime,'shop_id'=>$shop_id))->select();
		}
		// 明天预订时间
		$tomorrowTime = date("Ymd",strtotime("+1 day"));
		$tomorrow = Db::table('order_time')->where(array('day'=>$tomorrowTime,'shop_id'=>$shop_id))->select();
		if($tomorrow){
		    $result['tomorrow'] = $tomorrow;
		}else{
		    $data = array(
		            'day'=>$tomorrowTime,
		            'state'=>0
		        );
		    Db::table('order_time')->where(array('day'=>$dayGroup[1],'shop_id'=>$shop_id))->update($data);
		    $result['tomorrow'] = Db::table('order_time')->where(array('day'=>$tomorrowTime,'shop_id'=>$shop_id))->select();
		}
		 // 后天预订时间
		$nextDayTime = date("Ymd",strtotime("+2 day"));
		$nextDay = Db::table('order_time')->where(array('day'=>$nextDayTime,'shop_id'=>$shop_id))->select();
		if($nextDay){
		    $result['nextDay'] = $nextDay;
		}else{
		    $data = array(
		            'day'=>$nextDayTime,
		            'state'=>0
		        );
		    $res = Db::table('order_time')->where(array('day'=>$dayGroup[0],'shop_id'=>$shop_id))->update($data);
		    $result['nextDay'] = Db::table('order_time')->where(array('day'=>$nextDayTime,'shop_id'=>$shop_id))->select();
		}

		return view('index',$result);
	}
	
	// ajax修改时间状态
	public function updateOrderTimeState(){
		foreach ($_POST as $k => $v) {
			$data = [
				'id' => $k,
				'state'=>$v
				];
			Db::table('order_time') ->update($data);
		}
		echo json_encode(array('state'=>1));
	}
} 
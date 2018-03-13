<?php
namespace app\admin\service;
use think\Db;

class TimeService
{
	// 初始化时间表,其实只需要运行一次
	public static function createTime($shopId){
		if(empty($shopId)){
			$this->error('创建店铺时间段失败！');
		}
		set_time_limit(0);
		// 生成今天的时间表
		$day = date('Ymd');
		$start = 9;
		for($i=$start;$i<21;$i++){
			for($j=0;$j<6;$j++){
				if($i<10){
					$time = '0'.$i.':'.$j.'0';
				}else{
					$time = $i.':'.$j.'0';	
				}
				$data = array(
					'day'=>$day,
					'time'=>$time,
					'shop_id'=>$shopId
					);
				Db::table('order_time')->data($data)->insert();
			}
		}
		// 生成明天的时间表
		$tomorrow = date("Ymd",strtotime("+1 day"));
		$start = 9;
		for($i=$start;$i<21;$i++){
			for($j=0;$j<6;$j++){
				if($i<10){
					$time = '0'.$i.':'.$j.'0';
				}else{
					$time = $i.':'.$j.'0';	
				}
				$data = array(
					'day'=>$tomorrow,
					'time'=>$time,
					'shop_id'=>$shopId
					);
				Db::table('order_time')->data($data)->insert();
			}
		}
		// 生成后天的时间表
		$nextDay = date("Ymd",strtotime("+2 day"));
		$start = 9;
		for($i=$start;$i<21;$i++){
			for($j=0;$j<6;$j++){
				if($i<10){
					$time = '0'.$i.':'.$j.'0';
				}else{
					$time = $i.':'.$j.'0';	
				}
				$data = array(
					'day'=>$nextDay,
					'time'=>$time,
					'shop_id'=>$shopId
					);
				Db::table('order_time')->data($data)->insert();
			}
		}
	}

	public static function delShopTime($shopId){
		// 删除该店铺所有时间段
		if(!empty($shopId)){
			Db::table('order_time')->where(array('shop_id'=>$shopId))->delete();
		}
	}
	
}
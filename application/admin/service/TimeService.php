<?php
namespace app\admin\service;
use think\Db;

class TimeService
{
	/**
	 * 初始化时间表,其实只需要运行一次
	 * @param  [type] $shopId [description]
	 * @param  [type] $start  [description]
	 * @param  [type] $end    [description]
	 * @param  [type] $space  [description]
	 * @return [type]         [description]
	 */
	public static function createTime($shopId,$start,$end,$space){
		if(empty($shopId)){
			$this->error('创建店铺时间段失败！');
		}
		set_time_limit(0);
		// 生成今天的时间表
		self::_createOneTime(date('Ymd'),$shopId,$start,$end,$space);

		// 生成明天的时间表
		self::_createOneTime(date("Ymd",strtotime("+1 day")),$shopId,$start,$end,$space);

		// 生成后天的时间表
		self::_createOneTime(date("Ymd",strtotime("+2 day")),$shopId,$start,$end,$space);
		
	}
	/**
	 * 初始化单天时间段
	 * @param  [type] $day    [description]
	 * @param  [type] $shopId [description]
	 * @param  [type] $start  [description]
	 * @param  [type] $end    [description]
	 * @param  [type] $space  [description]
	 * @return [type]         [description]
	 */
	private static function _createOneTime($day,$shopId,$start,$end,$space){
		// 这个开始时间和结束时间,必须是整数时间最小间隔为5分钟,间隔必须为5的倍数。先选择间隔，然后开始和结束时间就可以选了。
		$k = 60/$space;

		$dataAll = [];
		for($i=$start; $i<$end; $i++){
			for($j=0;$j<$k;$j++){
				if($i<10){
					$b = $j*$space >= 10 ? $j*$space : '0'.$j*$space;
					$time = '0'.$i.':'.$b;
				}else{
					$b = $j*$space >= 10 ? $j*$space : '0'.$j*$space;
					$time = $i.':'.$b;	
				}
				$data = array(
					'day'=>$day,
					'time'=>$time,
					'shop_id'=>$shopId
					);
				array_push($dataAll,$data);
			}
		}
		Db::table('order_time')->insertAll($dataAll);
	}
	/**
	 * [删除该店铺所有时间段]
	 * @param  [type] $shopId [description]
	 * @return [type]         [description]
	 */
	public static function delShopTime($shopId){
		if(!empty($shopId)){
			Db::table('order_time')->where(array('shop_id'=>$shopId))->delete();
		}
	}
	
}
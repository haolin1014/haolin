<?php
namespace app\admin\model;
use think\Model;

class OrderTime extends Model
{
	protected $resultSetType = 'collection';
	// 获取当前店铺所有今明后三天所有时间段
	public static function getAllTime($shop_id)
	{
		$result['shop_id'] = $shop_id;
		// 获取时间分组值并排序
		$dayGroup = self::_getDayGroup($shop_id);
		// 今天预订时间
		$result['today'] = self::_getTime($shop_id, date("Ymd"), $dayGroup );
		// 明天预订时间
		$result['tomorrow'] = self::_getTime($shop_id, date("Ymd",strtotime("+1 day")), $dayGroup );
		// 后天预订时间
		$result['nextDay'] = self::_getTime($shop_id, date("Ymd",strtotime("+2 day")), $dayGroup );

		return $result;
	}
	/**
	 * 获取当前天的时间组
	 */
	private static function _getTime($shop_id,$day,$dayGroup)
	{
		$result = self::where(array('day'=>$day,'shop_id'=>$shop_id))->select()->toArray();
		if(!$result){
		    $data = array(
		            'day'=>$day,
		            'state'=>0
		        );
		    self::where(array('day'=>$dayGroup[2],'shop_id'=>$shop_id))->update($data);
		    $result = self::where(array('day'=>$day,'shop_id'=>$shop_id))->select()->toArray();
		}
		return $result;
	}
	/**
	 * 获取时间分组值并排序
	 */
	private static function _getDayGroup($shop_id)
	{
		$groups = self::field('day') ->group('day')->where(array('shop_id'=>$shop_id))->select()->toArray();
		$dayGroup = array();
		foreach ($groups as $v) {
		    array_push($dayGroup, $v['day']);
		}
		sort($dayGroup);
		return $dayGroup;
	}
}
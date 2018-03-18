<?php
namespace app\admin\model;
use think\Model;
use app\admin\model\Shop;

class Order extends Model
{
	// 设置返回数据集的对象名
	protected $resultSetType = 'collection';

	/**
	 * 获取历史订单数据
	 * @param  [type] $shop_id [description]
	 * @param  [type] $Ym      [description]
	 * @return [type]          [description]
	 */
	public static function getHistoryOrders($shop_id,$Ym){
		$order_month = self::where('order_day','like',$Ym."%")->where('shop_id',$shop_id)->order('add_time desc')->select()->toArray();
		$data['shopName'] = Shop::where('id',$shop_id)->value('name');//店铺名称
		$data['order_month_sum'] = self::where('order_day','like',$Ym."%")->where('shop_id',$shop_id)->count();//本月订单数

		foreach ($order_month as $k => $v) {
		    $order_month[$k]['order_time'] = explode(',', $v['order_time']);
		}
		$day_groups = self::where('order_day','like',$Ym."%")->where('shop_id',$shop_id)->field('order_day') ->group('order_day')->order('order_day desc')->select()->toArray();
		// 将订单按对应日期分组重新组织数组
		foreach ($day_groups as $k => $v) {
		   foreach ($order_month as $k1 => $v1) {
		       if($v['order_day']==$v1['order_day']){
		        $day_groups[$k]['orders'][] = $v1;
		       }
		   }
		}
		// 统计当天订单数，压入数组
		foreach ($day_groups as $k => $v) {
		    $day_groups[$k]['count'] = count($v['orders']);
		}
		// 格式化时间字段
		foreach ($day_groups as $k => $v) {
		    $y = substr($v['order_day'],0,4);
		    $m = substr($v['order_day'],4,2);
		    $d = substr($v['order_day'],6,2);
		    $day_groups[$k]['order_day'] = $y.'/'.$m.'/'.$d;
		}
		$data['day_groups'] = $day_groups;
		return $data;
	}
	/**
	 * 获取预约时间段
	 */
	public static function getOrderTime($shop_id,$day){
		$order_today = self::where('order_day',$day)->where('shop_id',$shop_id)->order('order_time')->select();
		if($order_today){
			$order_today = $order_today->toArray();
			// 将时间段格式化为数组
			foreach ($order_today as $k => $v) {
			    $order_today[$k]['order_time'] = explode(',', $v['order_time']);
			}
		}
		return $order_today;
	}
} 
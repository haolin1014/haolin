<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\controller\Common;
use think\Db;
/**
 * 订单控制器
 */
class Index extends Common
{
	// 预约订单
	public function index(){
		return view();
	}
	// 历史订单
	public function history(){
		$data = [];

		$shop_id = input('shop_id',session('shop_id'));//店铺
		$Ym = input('Ym') ? '20'.input('Ym') : date('Ym');//月份
		$order_month = Db::table('order')->where('order_day','like',$Ym."%")->where('shop_id',$shop_id)->order('add_time desc')->select();
		// 当月数据统计
		$data['shop_id'] = $shop_id;
		$data['shopName'] = Db::table('shop')->where('id',$shop_id)->value('name');//店铺名称
		$data['Ym'] = substr($Ym, 4,2);//当前月份
		$data['order_month_sum'] = Db::table('order')->where('order_day','like',$Ym."%")->where('shop_id',$shop_id)->count();//本月订单数

		foreach ($order_month as $k => $v) {
		    $order_month[$k]['order_time'] = explode(',', $v['order_time']);
		}
		$day_groups = Db::table('order')->where('order_day','like',$Ym."%")->where('shop_id',$shop_id)->field('order_day') ->group('order_day')->order('order_day desc')->select();
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
		$data['Yms'] = $this->getYm();
		return view('history',$data);
	}

	// 预约页面ajax刷新
	public function ajaxYuyue(){
	    $shop_id = input('shop_id',1);
	    $order = Db::table('order');
	    $order_today = Db::table('order')->where('order_day',date("Ymd"))->where('shop_id',$shop_id)->order('order_time')->select();
	    $order_tomorrow = Db::table('order')->where('order_day',date("Ymd",strtotime("+1 day") ) )->where('shop_id',$shop_id)->order('order_time')->select();
	    // 将时间段格式化为数组
	    foreach ($order_today as $k => $v) {
	        $order_today[$k]['order_time'] = explode(',', $v['order_time']);
	    }
	     foreach ($order_tomorrow as $k => $v) {
	        $order_tomorrow[$k]['order_time'] = explode(',', $v['order_time']);
	    }
	    $data = array(
	        'today'=>$order_today,
	        'tomorrow'=>$order_tomorrow
	        );
	    echo json_encode($data);
	}

	// 获取当前月份以及前11个月的年月份
	private function getYm(){
		$data = [];
		$Y = substr(date('Y'),2,2);
		$m = date('m');
		$data[$Y.$m] = $Y.'年'.$m.'月';
		for($i = 1; $i<12; $i++){
			$m = $m-1;
			if($m==0){
				$m = 12;
				$Y = $Y-1;
			}
			$m = $m<10 ? '0'.$m :$m;
			$data[$Y.$m] = $Y.'年'.$m.'月';
		}
		return $data;
	}

} 
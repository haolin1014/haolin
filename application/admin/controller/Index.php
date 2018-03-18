<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\controller\Common;
use think\Db;
use app\admin\model\Order;
/**
 * 首页控制器
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

		// 当月数据统计
		$data['shop_id'] = $shop_id;
		$data['Ym'] = substr($Ym, 4,2);//当前月份
		$data['Yms'] = $this->getYm();
		// 获取店铺，该月份的订单信息，并格式化。
		$result = Order::getHistoryOrders($shop_id,$Ym);
		$data = array_merge($data,$result);

		return view('history',$data);
	}

	// 预约页面ajax刷新
	public function ajaxYuyue(){
	    $shop_id = input('shop_id',1);

	    $data = array(
	        'today'=>Order::getOrderTime($shop_id,date("Ymd")),
	        'tomorrow'=>Order::getOrderTime($shop_id,date("Ymd",strtotime("+1 day")) )
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
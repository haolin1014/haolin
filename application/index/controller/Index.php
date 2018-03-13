<?php
namespace app\index\controller;
use think\Db;
use think\Cache;
use app\index\controller\Common;

class Index extends Common
{
	// 首页
    public function index()
    {	
    	$shop = Db::table('shop')->select();
        return view('index',['shop'=>$shop]);
    }
    //预约说明
     public function des(){
		$shopid = input('shopid');
		$shopName = input('shopName');
		session('shopid',$shopid);
		session('shopName',$shopName);
		$desc = Cache::get('desc');
        return view('des',['desc'=>$desc]);
     }
     // 选择时间
     public function chooseTime(){
             $shopid = session('shopid');//获取店铺id
            // 获取时间分组值并排序
            $groups = Db::table('order_time')->field('day') ->group('day')->where(array('shop_id'=>$shopid))->select();
            $dayGroup = array();
            foreach ($groups as $v) {
                array_push($dayGroup, $v['day']);
            }
            sort($dayGroup);
            // 先查是否有今天的记录，如果没有则清掉时间状态，并将时间设为今天。
            $todayTime = date("Ymd");
            $today = Db::table('order_time')->where(array('day'=>$todayTime,'shop_id'=>$shopid))->select();
            if($today){
                $result['today'] = $today;
            }else{
                $data = array(
                        'day'=>$todayTime,
                        'state'=>0
                    );
                Db::table('order_time')->where(array('day'=>$dayGroup[2],'shop_id'=>$shopid))->update($data);
                $result['today'] = Db::table('order_time')->where(array('day'=>$todayTime,'shop_id'=>$shopid))->select();
            }
            // 明天预订时间
            $tomorrowTime = date("Ymd",strtotime("+1 day"));
            $tomorrow = Db::table('order_time')->where(array('day'=>$tomorrowTime,'shop_id'=>$shopid))->select();
            if($tomorrow){
                $result['tomorrow'] = $tomorrow;
            }else{
                $data = array(
                        'day'=>$tomorrowTime,
                        'state'=>0
                    );
                Db::table('order_time')->where(array('day'=>$dayGroup[1],'shop_id'=>$shopid))->update($data);
                $result['tomorrow'] = Db::table('order_time')->where(array('day'=>$tomorrowTime,'shop_id'=>$shopid))->select();
             }
             // 已经选择的时间段
             $carts = Db::table('cart')->where(array('uid'=>session('uid'),'shop_id'=>session('shopid')))->select();
             $result['cart'] = json_encode($carts);

             return view('chooseTime',$result);
     }
}

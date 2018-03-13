<?php
namespace app\index\controller;
use think\Db;
use think\Cache;
use app\index\controller\Common;

class Cart extends Common 
{
	// 添加时间段
	public function add(){
		$id = input('id');
		$data = array(
			'uid'=>session('uid'),
			'time_id'=>$id,
			'shop_id'=>session('shopid'),
			);
		$res = Db::table('cart')->data($data)->insert();
		if($res){
			echo json_encode(array('state'=>1));
		}
	}
	// 删除时间段
	public function delete(){
		$time_id = input('id');
		$where['time_id'] = $time_id;
		$res = Db::table('cart')->where($where)->delete();
		if($res){
			echo json_encode(array('state'=>1));
		}
	}
}
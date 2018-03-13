<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\admin\controller\Common;
use app\admin\Service\TimeService;

/**
 * 店铺控制器
 */
class Shop extends Common
{
	public function index(){
		$shop = Db::table('shop')->select();
		$desc = Cache::get('desc');
		if(!$desc){
			Cache::set('desc','*拍摄最美证件照，每人只需要选取一个时间点进行预约');
			$desc = Cache::get('desc');
		}
		return view('index',['shop'=>$shop,'desc'=>$desc]);
	}

	// 添加店铺
	public function add(){
		if(request()->isPost()){
			$data = [
				'name'=>input('name'),
				'address'=>input('address'),
				'start_time'=>input('start_time'),
				'end_time'=>input('end_time'),
			];

			$data['pic1'] = $this->upload('pic1',ROOT_PATH . 'public' . DS .'static'. DS .'Public'. DS .'shop');
			$data['pic2'] = $this->upload('pic2',ROOT_PATH . 'public' . DS .'static'. DS .'Public'. DS .'shop');

			$shop_id = Db::table('shop')->insertGetId($data);
			if($shop_id){
				set_time_limit(180);
				TimeService::createTime($shop_id);//初始化店铺时间段
				$this->success('创建店铺成功！',url('admin/shop/index'));
			}else{
				$this->error('创建店铺失败！');
			}

		}else{
			return view();
		}
	}
	// 修改店铺
	public function update(){
		$shop_id = input('id');
		if(request()->isPost()){
			$data = [
				'name'=>input('name'),
				'address'=>input('address'),
				'start_time'=>input('start_time'),
				'end_time'=>input('end_time'),
			];
			if(request()->file('pic1')) $data['pic1'] = $this->upload('pic1',ROOT_PATH . 'public' . DS .'static'. DS .'Public'. DS .'shop');
			if(request()->file('pic2')) $data['pic2'] = $this->upload('pic2',ROOT_PATH . 'public' . DS .'static'. DS .'Public'. DS .'shop');

			if(Db::table('shop')->where('id',$shop_id)->update($data)){
				$this->success('修改店铺成功！',url('admin/shop/index'));
			}else{
				$this->error('修改店铺失败！');
			}

		}else{
			$shop = Db::table('shop')->find($shop_id);
			return view('update',['shop'=>$shop]);
		}
	}
	// 删除店铺
	public function del(){
		$id = input('id');
		$shop = Db::table('shop')->find($id);//查找并删除店铺图片
		@unlink('./static/'.$shop['pic1']);
		@unlink('./static/'.$shop['pic2']);
		$res = Db::table('shop')->delete($id);
		if($res){
			// 删除店铺时间段
			TimeService::delShopTime($id);
			$this->success('删除成功',url('Admin/shop/index'));
		}else{
			$this->error('删除失败');
		}
	}
	// ajax修改地址
	public function updateAddress(){
		$data = array(
			'id' => input('id'),
			'address' => input('address')
			);
		$res = Db::table('shop')->update($data);
		if($res){
			$data = array(
				'state'=>1
				);
		}else{
			$data = array(
				'state'=>0
				);
		}
		echo json_encode($data);
	}
	// ajax更新店铺说明
	public function updateSet(){
		if(Cache::set('desc',input('shopDec'))){
			echo json_encode(array('state'=>1));
		}
	}
	
} 
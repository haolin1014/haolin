<?php
namespace app\admin\validate;
use app\admin\validate\BaseValidate;

class ShopValidate extends BaseValidate
{
	protected $rule = [
		['name', 'require|chsAlphaNum', '店铺名不能为空|店铺名格式不正确'],
		['address', 'require|chsAlphaNum', '地址不能为空|地址格式不正确'],
		['start_time', 'max:2', '开始时间格式不正确'],
		['end_time', 'max:2', '结束时间格式不正确'],
		['id', 'number', 'id格式不正确'],
	];

}
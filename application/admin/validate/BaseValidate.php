<?php
namespace app\admin\validate;
use think\Validate;
use think\Request;

class BaseValidate extends Validate
{
	public function goCheck()
	{
	    $request = Request::instance();
	    $params = $request->param();

	    if (!$this->check($params)) {
	    	$url = $request->module().'/'.$request->controller().'/'.$request->action();
	        return redirect($url, [], 302, ['msg' => $this->error]);
	    }
	    return true;
	}
}

